<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig');
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(int $id): Response
    {
        return $this->redirectToRoute('app_cart');
    }
}