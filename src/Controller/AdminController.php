<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\MeubleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    // ================= DASHBOARD =================

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        CommandeRepository $commandeRepository,
        MeubleRepository $meubleRepository,
        UserRepository $userRepository
    ): Response {

        $commandes = $commandeRepository->findAll();

        // revenu total
        $revenu = 0;

        foreach ($commandes as $commande) {
            $revenu += $commande->getTotal();
        }

        return $this->render('admin/dashboard.html.twig', [
            'revenu' => $revenu,
            'nombreCommandes' => count($commandes),
            'nombreProduits' => count($meubleRepository->findAll()),
            'nombreClients' => count($userRepository->findAll()),
        ]);
    }

    // ================= USERS =================

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/toggle-admin', name: 'admin_user_toggle')]
    public function toggleAdmin(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {

        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {

            $user->setRoles(['ROLE_USER']);
        } else {

            $user->setRoles(['ROLE_ADMIN']);
        }

        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete')]
    public function deleteUser(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {

        $user = $userRepository->find($id);

        if ($user) {

            $em->remove($user);

            $em->flush();
        }

        return $this->redirectToRoute('admin_users');
    }

    // ================= CATALOGUE =================

    #[Route('/catalogue', name: 'admin_catalogue')]
    public function catalogue(
        MeubleRepository $meubleRepository
    ): Response {

        return $this->render('admin/catalogue.html.twig', [
            'meubles' => $meubleRepository->findAll(),
        ]);
    }
}
