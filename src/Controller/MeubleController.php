<?php

namespace App\Controller;

use App\Entity\Meuble;
use App\Form\MeubleType;
use App\Repository\MeubleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/meuble')]
class MeubleController extends AbstractController
{
    #[Route('/', name: 'app_meuble_index')]
    public function index(MeubleRepository $meubleRepository): Response
    {
        return $this->render('meuble/index.html.twig', [
            'meubles' => $meubleRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_meuble_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $meuble = new Meuble();
        $form = $this->createForm(MeubleType::class, $meuble);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($meuble);
            $em->flush();

            return $this->redirectToRoute('app_meuble_index');
        }

        return $this->render('meuble/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_meuble_edit')]
    public function edit(Meuble $meuble, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MeubleType::class, $meuble);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_meuble_index');
        }

        return $this->render('meuble/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/delete', name: 'app_meuble_delete')]
    public function delete(Meuble $meuble, EntityManagerInterface $em): Response
    {
        $em->remove($meuble);
        $em->flush();

        return $this->redirectToRoute('app_meuble_index');
    }
    #[Route('/meuble/{id}', name: 'app_meuble_show')]
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
}
