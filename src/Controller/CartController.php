<?php

namespace App\Controller;

use App\Repository\MeubleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
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
                    'product' => $meuble,
                    'quantity' => $quantity
                ];

                $total += $meuble->getPrix() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(
        int $id,
        RequestStack $requestStack
    ): Response {

        $session = $requestStack->getSession();

        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {

            $cart[$id]++;

        } else {

            $cart[$id] = 1;
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(
        int $id,
        RequestStack $requestStack
    ): Response {

        $session = $requestStack->getSession();

        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {

            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }
}
