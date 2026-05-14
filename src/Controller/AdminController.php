<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\MeubleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        CommandeRepository $commandeRepository,
        MeubleRepository $meubleRepository,
        UserRepository $userRepository
    ): Response {

        $commandes = $commandeRepository->findAll();

        // 💰 revenu total
        $revenu = 0;
        foreach ($commandes as $commande) {
            $revenu += $commande->getTotal();
        }

        // 📊 stats simples
        $nombreCommandes = count($commandes);
        $nombreProduits = count($meubleRepository->findAll());
        $nombreClients = count($userRepository->findAll());

        return $this->render('admin/dashboard.html.twig', [
            'revenu' => $revenu,
            'nombreCommandes' => $nombreCommandes,
            'nombreProduits' => $nombreProduits,
            'nombreClients' => $nombreClients,
        ]);
    }
}
