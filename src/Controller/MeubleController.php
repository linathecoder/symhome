<?php

namespace App\Controller;

use App\Entity\Meuble;
use App\Form\MeubleType;
use App\Repository\CategorieRepository;
use App\Repository\MeubleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/meuble')]
class MeubleController extends AbstractController
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

    // ================= LIST + SEARCH + FILTER =================
    #[Route('/', name: 'app_meuble_index')]
    public function index(
        Request $request,
        MeubleRepository $meubleRepository,
        CategorieRepository $categorieRepository
    ): Response {
        $search = $request->query->get('search', '');
        $categorieId = $request->query->get('categorie', '');

        $qb = $meubleRepository->createQueryBuilder('m')
            ->leftJoin('m.categorie', 'c');

        if ($search) {
            $qb->andWhere('m.nom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($categorieId) {
            $qb->andWhere('c.id = :cat')
               ->setParameter('cat', $categorieId);
        }

        $meubles = $qb->getQuery()->getResult();
        $categories = $categorieRepository->findAll();

        return $this->render('meuble/index.html.twig', [
            'meubles'    => $meubles,
            'categories' => $categories,
            'search'     => $search,
            'categorieId'=> $categorieId,
        ]);
    }

    #[Route('/{id}', name: 'app_meuble_show', requirements: ['id' => '\d+'])]
    public function show(int $id, MeubleRepository $meubleRepository): Response
    {
        $meuble = $meubleRepository->find($id);
        if (!$meuble) {
            throw $this->createNotFoundException('Meuble introuvable');
        }

        return $this->render('meuble/show.html.twig', [
            'meuble' => $meuble,
        ]);
    }

    // ================= CREATE (ADMIN) =================
    #[Route('/new', name: 'app_meuble_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        $meuble = new Meuble();
        $form = $this->createForm(MeubleType::class, $meuble);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                $meuble->setImage($newFilename);
            } else {
                $meuble->setImage('default.jpg');
            }

            $em->persist($meuble);
            $em->flush();
            $this->addFlash('success', 'Meuble ajouté avec succès !');
            return $this->redirectToRoute('admin_catalogue');
        }

        return $this->render('meuble/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ================= EDIT (ADMIN) =================
    #[Route('/{id}/edit', name: 'app_meuble_edit', requirements: ['id' => '\d+'])]
    public function edit(
        Meuble $meuble,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        $form = $this->createForm(MeubleType::class, $meuble);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                $meuble->setImage($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Meuble modifié avec succès !');
            return $this->redirectToRoute('admin_catalogue');
        }

        return $this->render('meuble/edit.html.twig', [
            'form'   => $form->createView(),
            'meuble' => $meuble,
        ]);
    }

    // ================= DELETE (ADMIN) =================
    #[Route('/{id}/delete', name: 'app_meuble_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Meuble $meuble, EntityManagerInterface $em): Response
    {
        if ($response = $this->denyUnlessAdmin()) {
            return $response;
        }

        if ($this->isCsrfTokenValid('delete' . $meuble->getId(), $request->request->get('_token'))) {
            $em->remove($meuble);
            $em->flush();
            $this->addFlash('success', 'Meuble supprimé.');
        }
        return $this->redirectToRoute('admin_catalogue');
    }
}
