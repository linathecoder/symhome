<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\AdminUserType;
use App\Repository\CommandeRepository;
use App\Repository\MeubleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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

    #[Route('/commandes', name: 'admin_commandes')]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        return $this->render('admin/commandes.html.twig', [
            'commandes' => $commandeRepository->findBy([], ['date' => 'DESC']),
        ]);
    }

    #[Route('/commandes/{id}/status', name: 'admin_commande_status', methods: ['POST'])]
    public function updateCommandeStatus(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $status = $request->request->get('status');
        $allowedStatuses = [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'completee' => 'Complétée',
            'annulee' => 'Annulée',
        ];

        if (!array_key_exists($status, $allowedStatuses)) {
            $this->addFlash('danger', 'Statut de commande invalide.');
            return $this->redirectToRoute('admin_commandes');
        }

        $commande->setStatus($status);
        $em->flush();

        $this->addFlash('success', 'Statut de commande mis à jour.');
        return $this->redirectToRoute('admin_commandes');
    }

    // ================= USERS =================

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/new', name: 'admin_user_new')]
    public function newUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        $user = new \App\Entity\User();
        $form = $this->createForm(AdminUserType::class, $user, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('plainPassword')->getData();
            if ($password) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'admin_user_edit')]
    public function editUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(AdminUserType::class, $user, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('plainPassword')->getData();
            if ($password) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }
            $em->flush();
            $this->addFlash('success', 'Profil utilisateur mis à jour.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
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
