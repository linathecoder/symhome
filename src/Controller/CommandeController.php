<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\CommandeRepository;
use App\Repository\MeubleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

final class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/commande/show/{id}', name: 'commande_show')]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commande/delete/{id}', name: 'commande_delete')]
    public function delete(
        Commande $commande,
        EntityManagerInterface $entityManager
    ): Response {

        $entityManager->remove($commande);
        $entityManager->flush();

        return $this->redirectToRoute('app_commande');
    }

    #[Route('/commande/checkout', name: 'commande_checkout')]
    public function checkout(
        RequestStack $requestStack,
        MeubleRepository $meubleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('app_cart');
        }

        $commande = new Commande();
        $commande->setDate(new \DateTimeImmutable());
        $commande->setStatus('En attente');
        $commande->setUser($this->getUser());

        $total = 0;

        foreach ($cart as $id => $qty) {

            $meuble = $meubleRepository->find($id);

            if (!$meuble) continue;

            $ligne = new LigneCommande();
            $ligne->setCommande($commande);
            $ligne->setMeuble($meuble);
            $ligne->setQuantity($qty);
            $ligne->setPrice($meuble->getPrix());

            $entityManager->persist($ligne);

            $total += $meuble->getPrix() * $qty;
        }

        $commande->setTotal($total);

        $entityManager->persist($commande);
        $entityManager->flush();

        $session->remove('cart');

        return $this->redirectToRoute('commande_show', [
            'id' => $commande->getId()
        ]);
    }
}
