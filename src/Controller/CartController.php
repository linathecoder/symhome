<?php

namespace App\Controller;

use App\Repository\MeubleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    // ── View cart ────────────────────────────────────────────────────────────
    #[Route('/cart', name: 'app_cart')]
    public function index(
        RequestStack $requestStack,
        MeubleRepository $meubleRepository
    ): Response {
        $session = $requestStack->getSession();
        $cart    = $session->get('cart', []);

        $items = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $meuble = $meubleRepository->find($id);
            if ($meuble) {
                $sousTotal = $meuble->getPrix() * $quantity;
                $items[]   = [
                    'meuble'   => $meuble,
                    'quantity' => $quantity,
                    'sousTotal'=> $sousTotal,
                ];
                $total += $sousTotal;
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    // ── Add to cart ──────────────────────────────────────────────────────────
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(int $id, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $cart    = $session->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $session->set('cart', $cart);
        $this->addFlash('success', 'Produit ajouté au panier !');
        return $this->redirectToRoute('app_cart');
    }

    // ── Update quantity ──────────────────────────────────────────────────────
    #[Route('/cart/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, RequestStack $requestStack): Response
    {
        $session  = $requestStack->getSession();
        $cart     = $session->get('cart', []);
        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $quantity;
        }

        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
    }

    // ── Remove from cart ─────────────────────────────────────────────────────
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(int $id, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $cart    = $session->get('cart', []);
        unset($cart[$id]);
        $session->set('cart', $cart);
        $this->addFlash('info', 'Produit retiré du panier.');
        return $this->redirectToRoute('app_cart');
    }

    // ── Clear cart ───────────────────────────────────────────────────────────
    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(RequestStack $requestStack): Response
    {
        $requestStack->getSession()->set('cart', []);
        return $this->redirectToRoute('app_cart');
    }
}
