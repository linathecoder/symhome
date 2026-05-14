<?php

namespace App\Controller\Admin;

use App\Repository\CommandeRepository;
use App\Repository\MeubleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(
        CommandeRepository $commandeRepository,
        MeubleRepository $meubleRepository,
        UserRepository $userRepository
    ): Response {

        // 📊 statistiques simples
        $nbCommandes = count($commandeRepository->findAll());
        $nbMeubles = count($meubleRepository->findAll());
        $nbUsers = count($userRepository->findAll());

        // 💰 chiffre d'affaires
        $commandes = $commandeRepository->findAll();
        $ca = 0;

        foreach ($commandes as $cmd) {
            $ca += $cmd->getTotal();
        }

        return $this->render('admin/dashboard.html.twig', [
            'nbCommandes' => $nbCommandes,
            'nbMeubles' => $nbMeubles,
            'nbUsers' => $nbUsers,
            'ca' => $ca,
        ]);
    }
}
