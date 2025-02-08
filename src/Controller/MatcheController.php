<?php

namespace App\Controller;

use App\Entity\Matche;
use App\Form\MatcheType;
use App\Repository\MatcheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/matche')]
final class MatcheController extends AbstractController
{
    #[Route(name: 'app_matche_index', methods: ['GET'])]
    public function index(MatcheRepository $matcheRepository): Response
    {
        return $this->render('matche/index.html.twig', [
            'matches' => $matcheRepository->findAll(),
        ]);
    }

    // ALl matches
    #[Route('/all', name: 'all_matches')]
    public function getAll(MatcheRepository $matcheRepository): Response
    {
        $matches = $matcheRepository->findBy([], ['date' => 'ASC']);

        return $this->render('matche/all_matches.html.twig', [
            'matches' => $matches,
        ]);
    }

    #[Route('/new', name: 'admin_match_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $matche = new Matche();
        $form = $this->createForm(MatcheType::class, $matche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($matche);
            $entityManager->flush();

            return $this->redirectToRoute('admin_matches_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('matche/new.html.twig', [
            'matche' => $matche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_matche_show', methods: ['GET'])]
    public function show(Matche $matche): Response
    {
        return $this->render('matche/show.html.twig', [
            'matche' => $matche,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_match_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Matche $matche, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MatcheType::class, $matche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_matches_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('matche/edit.html.twig', [
            'match' => $matche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_match_delete', methods: ['POST'])]
    public function delete(Request $request, Matche $matche, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $matche->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($matche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_matches_list', [], Response::HTTP_SEE_OTHER);
    }
}
