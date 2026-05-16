<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\CommandeRepository;
use App\Repository\MeubleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommandeController extends AbstractController
{
    // ── USER: My orders ─────────────────────────────
    #[IsGranted('ROLE_USER')]
    #[Route('/commande', name: 'app_commande')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findBy(
                ['user' => $this->getUser()],
                ['id' => 'DESC']
            ),
        ]);
    }

    // ── USER: Order detail ──────────────────────────
    #[IsGranted('ROLE_USER')]
    #[Route('/commande/{id}', name: 'commande_show')]
    public function show(Commande $commande): Response
    {
        if ($commande->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/commande/{id}/delete', name: 'commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($commande->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete-commande' . $commande->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide. La commande n’a pas été supprimée.');
            return $this->redirectToRoute('app_commande');
        }

        $em->remove($commande);
        $em->flush();

        $this->addFlash('success', 'Commande supprimée avec succès.');

        return $this->redirectToRoute('app_commande');
    }

    // ── CHECKOUT (UNIQUE VERSION) ───────────────────
    #[IsGranted('ROLE_USER')]
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(
        RequestStack $requestStack,
        MeubleRepository $meubleRepository,
        EntityManagerInterface $em
    ): Response {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (!$cart) {
            return $this->redirectToRoute('app_cart');
        }

        $commande = new Commande();
        $commande->setUser($this->getUser());
        $commande->setDate(new \DateTime());
        $commande->setStatus('en_attente');

        $total = 0;
        $validItemCount = 0;

        foreach ($cart as $id => $qty) {
            $meuble = $meubleRepository->find($id);
            if (!$meuble || $qty <= 0) {
                continue;
            }

            $ligne = new LigneCommande();
            $ligne->setCommande($commande);
            $ligne->setMeuble($meuble);
            $ligne->setQuantity($qty);
            $ligne->setPrice($meuble->getPrix());

            $em->persist($ligne);

            $total += $meuble->getPrix() * $qty;
            $validItemCount++;
        }

        if ($validItemCount === 0 || $total <= 0) {
            $this->addFlash('danger', 'Impossible de valider la commande : votre panier est vide ou contient des articles invalides.');
            return $this->redirectToRoute('app_cart');
        }

        $commande->setTotal($total);

        $em->persist($commande);
        $em->flush();

        $session->remove('cart');

        return $this->redirectToRoute('commande_show', [
            'id' => $commande->getId()
        ]);
    }
}
