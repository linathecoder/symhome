<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\MeubleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    // ================= CART PAGE =================
    #[Route('/cart', name: 'app_cart')]
    public function index(
        RequestStack $requestStack,
        MeubleRepository $meubleRepository
    ): Response {

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        $items = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {

            $meuble = $meubleRepository->find($id);

            if ($meuble) {
                $items[] = [
                    'meuble' => $meuble,
                    'qty' => $quantity
                ];

                $total += $meuble->getPrix() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total
        ]);
    }

    // ================= ADD TO CART =================
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(
        int $id,
        RequestStack $requestStack
    ): Response {

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        $cart[$id] = ($cart[$id] ?? 0) + 1;

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    // ================= REMOVE ITEM =================
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(
        int $id,
        RequestStack $requestStack
    ): Response {

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        unset($cart[$id]);

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(
        RequestStack $requestStack,
        MeubleRepository $meubleRepository,
        EntityManagerInterface $em
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('app_cart');
        }

        // 🧾 création commande
        $commande = new Commande();
        $commande->setDate(new \DateTimeImmutable());
        $commande->setStatus('payée'); // paiement SIMPLIFIÉ
        $commande->setUser($this->getUser());

        $total = 0;

        foreach ($cart as $id => $quantity) {

            $meuble = $meubleRepository->find($id);

            if (!$meuble) continue;

            $ligne = new LigneCommande();
            $ligne->setMeuble($meuble);
            $ligne->setQuantity($quantity);
            $ligne->setPrice($meuble->getPrix());
            $ligne->setCommande($commande);

            $em->persist($ligne);

            $total += $meuble->getPrix() * $quantity;
        }

        $commande->setTotal($total);

        $em->persist($commande);
        $em->flush();

        // 🧹 vider panier
        $session->remove('cart');

        // 🎉 redirection confirmation
        return $this->redirectToRoute('app_commande');
    }
}
