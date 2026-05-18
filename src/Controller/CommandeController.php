<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Repository\MeubleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommandeController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private UserRepository $userRepository
    ) {
    }

    private function currentUser(): ?User
    {
        $userId = $this->requestStack->getSession()->get('user_id');

        return $userId ? $this->userRepository->find($userId) : null;
    }

    private function isAdmin(): bool
    {
        $roles = $this->requestStack->getSession()->get('user_roles', []);

        return in_array('ROLE_ADMIN', $roles, true);
    }

    private function denyUnlessLoggedIn(): ?Response
    {
        if (!$this->currentUser()) {
            $this->addFlash('danger', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return null;
    }

    #[Route('/commande', name: 'app_commande')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        if ($response = $this->denyUnlessLoggedIn()) {
            return $response;
        }

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findBy(
                ['user' => $this->currentUser()],
                ['id' => 'DESC']
            ),
        ]);
    }

    #[Route('/commande/{id}', name: 'commande_show')]
    public function show(Commande $commande): Response
    {
        if ($response = $this->denyUnlessLoggedIn()) {
            return $response;
        }

        if ($commande->getUser()?->getId() !== $this->currentUser()?->getId() && !$this->isAdmin()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commande/{id}/delete', name: 'commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($response = $this->denyUnlessLoggedIn()) {
            return $response;
        }

        if ($commande->getUser()?->getId() !== $this->currentUser()?->getId() && !$this->isAdmin()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete-commande' . $commande->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide. La commande n a pas ete supprimee.');
            return $this->redirectToRoute('app_commande');
        }

        $em->remove($commande);
        $em->flush();

        $this->addFlash('success', 'Commande supprimee avec succes.');

        return $this->redirectToRoute('app_commande');
    }

    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(
        Request $request,
        RequestStack $requestStack,
        MeubleRepository $meubleRepository,
        EntityManagerInterface $em
    ): Response {
        if ($response = $this->denyUnlessLoggedIn()) {
            return $response;
        }

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (!$cart) {
            return $this->redirectToRoute('app_cart');
        }

        $items = [];
        $total = 0;

        foreach ($cart as $id => $qty) {
            $meuble = $meubleRepository->find($id);
            if (!$meuble || $qty <= 0) {
                continue;
            }

            if ($qty > $meuble->getStock()) {
                $this->addFlash('danger', sprintf(
                    'Stock insuffisant pour %s. Stock disponible : %d.',
                    $meuble->getNom(),
                    $meuble->getStock()
                ));

                return $this->redirectToRoute('app_cart');
            }

            $sousTotal = $meuble->getPrix() * $qty;
            $items[] = [
                'meuble' => $meuble,
                'quantity' => $qty,
                'sousTotal' => $sousTotal,
            ];
            $total += $sousTotal;
        }

        if (!$items || $total <= 0) {
            $this->addFlash('danger', 'Impossible de valider la commande : votre panier est vide ou contient des articles invalides.');
            return $this->redirectToRoute('app_cart');
        }

        if (!$request->isMethod('POST')) {
            return $this->render('payment/checkout.html.twig', [
                'items' => $items,
                'total' => $total,
            ]);
        }

        if (!$this->isCsrfTokenValid('payment-checkout', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide. Veuillez recommencer le paiement.');
            return $this->redirectToRoute('app_checkout');
        }

        $paymentMethod = $request->request->get('payment_method', 'card');
        $allowedPaymentMethods = ['card', 'cash'];

        if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
            $this->addFlash('danger', 'Mode de paiement invalide.');
            return $this->redirectToRoute('app_checkout');
        }

        $commande = new Commande();
        $commande->setUser($this->currentUser());
        $commande->setDate(new \DateTime());
        $commande->setStatus($paymentMethod === 'card' ? 'payee' : 'en_attente');

        foreach ($items as $item) {
            $ligne = new LigneCommande();
            $ligne->setCommande($commande);
            $ligne->setMeuble($item['meuble']);
            $ligne->setQuantity($item['quantity']);
            $ligne->setPrice($item['meuble']->getPrix());

            $em->persist($ligne);

            $item['meuble']->setStock($item['meuble']->getStock() - $item['quantity']);
        }

        $commande->setTotal($total);

        $em->persist($commande);
        $em->flush();

        $session->remove('cart');
        $this->addFlash('success', $paymentMethod === 'card' ? 'Paiement valide. Votre commande est confirmee.' : 'Commande enregistree. Paiement a la livraison.');

        return $this->redirectToRoute('commande_show', [
            'id' => $commande->getId(),
        ]);
    }
}
