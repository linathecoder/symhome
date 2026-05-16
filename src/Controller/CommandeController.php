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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CommandeController extends AbstractController
{
    // ── USER: My orders history ──────────────────────────────────────────────
    #[IsGranted('ROLE_USER')]
    #[Route('/commande', name: 'app_commande')]
    public function index(): Response
    {
        $user = $this->getUser();
        $commandes = $user->getCommandes();

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    // ── USER: Order detail ───────────────────────────────────────────────────
    #[IsGranted('ROLE_USER')]
    #[Route('/commande/{id}', name: 'commande_show', requirements: ['id' => '\d+'])]
    public function show(Commande $commande): Response
    {
        // Ensure user can only see their own orders
        if ($commande->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    // ── CHECKOUT: Validate cart → create order ───────────────────────────────
    #[IsGranted('ROLE_USER')]
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(
        RequestStack $requestStack,
        MeubleRepository $meubleRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        $commande = new Commande();
        $commande->setUser($this->getUser());
        $commande->setDate(new \DateTime());
        $commande->setStatus('en_attente');

        $total = 0;
        foreach ($cart as $id => $quantity) {
            $meuble = $meubleRepository->find($id);
            if (!$meuble) continue;

            $ligne = new LigneCommande();
            $ligne->setMeuble($meuble);
            $ligne->setQuantity($quantity);
            $ligne->setPrice($meuble->getPrix());
            $ligne->setCommande($commande);
            $em->persist($ligne);

            $total += $meuble->getPrix() * $quantity;
        }

        $commande->setTotal($total);
        $em->persist($commande);
        $em->flush();

        // Clear cart
        $session->set('cart', []);

        // Send confirmation email
        try {
            $email = (new Email())
                ->from('noreply@symhome.tn')
                ->to($this->getUser()->getEmail())
                ->subject('Confirmation de votre commande #' . $commande->getId())
                ->html($this->renderView('emails/confirmation_commande.html.twig', [
                    'commande' => $commande,
                    'user'     => $this->getUser(),
                ]));
            $mailer->send($email);
        } catch (\Exception $e) {
            // Email sending failed, but order was placed
        }

        $this->addFlash('success', 'Commande #' . $commande->getId() . ' passée avec succès ! Un email de confirmation vous a été envoyé.');
        return $this->redirectToRoute('commande_show', ['id' => $commande->getId()]);
    }

    // ── ADMIN: All orders list ───────────────────────────────────────────────
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/commandes', name: 'admin_commandes')]
    public function adminIndex(CommandeRepository $commandeRepository): Response
    {
        return $this->render('admin/commandes.html.twig', [
            'commandes' => $commandeRepository->findBy([], ['date' => 'DESC']),
        ]);
    }

    // ── ADMIN: Update order status ───────────────────────────────────────────
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/commande/{id}/status', name: 'admin_commande_status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em
    ): Response {
        $status = $request->request->get('status');
        $allowed = ['en_attente', 'en_cours', 'completee', 'annulee'];
        if (in_array($status, $allowed)) {
            $commande->setStatus($status);
            $em->flush();
            $this->addFlash('success', 'Statut mis à jour.');
        }
        return $this->redirectToRoute('admin_commandes');
    }
}
