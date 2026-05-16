<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\CommandeRepository;
use App\Repository\MeubleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    // ── Dashboard ────────────────────────────────────────────────────────────
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(
        CommandeRepository $commandeRepository,
        UserRepository $userRepository,
        MeubleRepository $meubleRepository
    ): Response {
        // Revenue
        $allCommandes = $commandeRepository->findAll();
        $chiffreAffaires = array_sum(array_map(fn($c) => $c->getTotal(), $allCommandes));
        $totalClients = count($userRepository->findAll());
        $totalCommandes = count($allCommandes);

        // Best selling meuble
        $meubleSales = [];
        foreach ($allCommandes as $commande) {
            foreach ($commande->getLigneCommandes() as $ligne) {
                $meubleId = $ligne->getMeuble()->getId();
                $meubleSales[$meubleId] = ($meubleSales[$meubleId] ?? 0) + $ligne->getQuantity();
            }
        }
        arsort($meubleSales);
        $bestMeuble = null;
        if (!empty($meubleSales)) {
            $bestMeuble = $meubleRepository->find(array_key_first($meubleSales));
        }

        // Monthly revenue for chart (last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i months");
            $monthLabel = $date->format('M Y');
            $monthlyRevenue[$monthLabel] = 0;
        }
        foreach ($allCommandes as $commande) {
            $label = $commande->getDate()->format('M Y');
            if (isset($monthlyRevenue[$label])) {
                $monthlyRevenue[$label] += $commande->getTotal();
            }
        }

        // Orders by status
        $statusCounts = [
            'en_attente' => 0,
            'en_cours'   => 0,
            'completee'  => 0,
            'annulee'    => 0,
        ];
        foreach ($allCommandes as $c) {
            if (isset($statusCounts[$c->getStatus()])) {
                $statusCounts[$c->getStatus()]++;
            }
        }

        return $this->render('admin/dashboard.html.twig', [
            'chiffreAffaires' => $chiffreAffaires,
            'totalClients'    => $totalClients,
            'totalCommandes'  => $totalCommandes,
            'bestMeuble'      => $bestMeuble,
            'monthlyRevenue'  => $monthlyRevenue,
            'statusCounts'    => $statusCounts,
        ]);
    }

    // ── Users management ─────────────────────────────────────────────────────
    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/toggle-admin', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggleAdmin(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) throw $this->createNotFoundException();

        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) {
            $user->setRoles(['ROLE_USER']);
        } else {
            $user->setRoles(['ROLE_ADMIN']);
        }
        $em->flush();
        $this->addFlash('success', 'Rôle mis à jour.');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepository->find($id);
        if ($user) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('admin_users');
    }

    // ── Catalogue ─────────────────────────────────────────────────────────────
    #[Route('/catalogue', name: 'admin_catalogue')]
    public function catalogue(MeubleRepository $meubleRepository): Response
    {
        return $this->render('admin/catalogue.html.twig', [
            'meubles' => $meubleRepository->findAll(),
        ]);
    }
}
