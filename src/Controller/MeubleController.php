<?php

namespace App\Controller;

use App\Repository\MeubleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MeubleController extends AbstractController
{
    #[Route('/meubles', name: 'app_meuble_index')]
    public function index(MeubleRepository $meubleRepository): Response
    {
        return $this->render('meuble/index.html.twig', [
            'meubles' => $meubleRepository->findAll(),
        ]);
    }
}