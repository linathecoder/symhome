<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\MeubleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        MeubleRepository $meubleRepository,
        CategorieRepository $categorieRepository
    ): Response {
        $featured = $meubleRepository->findBy([], ['id' => 'DESC'], 6);
        $categories = $categorieRepository->findAll();

        return $this->render('home/index.html.twig', [
            'featured' => $featured,
            'categories' => $categories,
        ]);
    }
}
