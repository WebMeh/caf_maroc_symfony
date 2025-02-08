<?php

namespace App\Controller;

use App\Repository\MatcheRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    // Les équipes partie user
    #[Route('/teams', name: 'user_show_teams')]
    public function showTeams(TeamRepository $teamRepository): Response
    {
        return $this->render('user/equipes.html.twig', [
            'equipes' => $teamRepository->findAll()
        ]);
    }

    #[Route('/matche/{id}', name: 'user_show_match')]
    public function showMatche(int $id, MatcheRepository $matcheRepository): Response
    {
        return $this->render('user/matche_details.html.twig', [
            'matche' => $matcheRepository->find($id)
        ]);
    }
}
