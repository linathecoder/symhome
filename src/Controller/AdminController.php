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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    private function denyUnlessAdmin(): ?Response
    {
        $roles = $this->requestStack->getSession()->get('user_roles', []);

        if (!in_array('ROLE_ADMIN', $roles, true)) {
            $this->addFlash('danger', 'Acces reserve a l administrateur.');
            return $this->redirectToRoute('app_login');
        }

        return null;
    }

    // ================= DASHBOARD =================

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        CommandeRepository $commandeRepository,
        MeubleRepository $meubleRepository,
        UserRepository $userRepository
    ): Response {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        $commandes = $commandeRepository->findAll();

        // revenu total
        $revenu = 0;

        foreach ($commandes as $commande) {
            $revenu += $commande->getTotal();
        }

        return $this->render('admin/dashboard.html.twig', [
            'ca' => $revenu,
            'nbCommandes' => count($commandes),
            'nbMeubles' => count($meubleRepository->findAll()),
            'nbUsers' => count($userRepository->findAll()),
        ]);
    }

    #[Route('/commandes', name: 'admin_commandes')]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        return $this->render('admin/commandes.html.twig', [
            'commandes' => $commandeRepository->findBy([], ['date' => 'DESC']),
        ]);
    }

    #[Route('/commandes/{id}/status', name: 'admin_commande_status', methods: ['POST'])]
    public function updateCommandeStatus(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        $status = $request->request->get('status');
        $allowedStatuses = [
            'en_attente' => 'En attente',
            'payee' => 'Payee',
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
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/new', name: 'admin_user_new')]
    public function newUser(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        $user = new \App\Entity\User();
        $form = $this->createForm(AdminUserType::class, $user, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('plainPassword')->getData();
            if ($password) {
                $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            }
            $user->setIsVerified(true);
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
        EntityManagerInterface $em
    ): Response {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(AdminUserType::class, $user, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('plainPassword')->getData();
            if ($password) {
                $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            }
            $em->flush();
            $this->addFlash('success', 'Profil utilisateur mis à jour.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/toggle-admin', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggleAdmin(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        if (!$this->isCsrfTokenValid('toggle-user' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_users');
        }

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

        if ($this->requestStack->getSession()->get('user_id') === $user->getId()) {
            $this->requestStack->getSession()->set('user_roles', $user->getRoles());
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        if (!$this->isCsrfTokenValid('delete-user' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_users');
        }

        if ($this->requestStack->getSession()->get('user_id') === $id) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte admin.');
            return $this->redirectToRoute('admin_users');
        }

        $user = $userRepository->find($id);

        if ($user) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprime.');
        }

        return $this->redirectToRoute('admin_users');
    }

    // ================= CATALOGUE =================

    #[Route('/catalogue', name: 'admin_catalogue')]
    public function catalogue(
        MeubleRepository $meubleRepository
    ): Response {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        return $this->render('admin/catalogue.html.twig', [
            'meubles' => $meubleRepository->findAll(),
        ]);
    }
}
