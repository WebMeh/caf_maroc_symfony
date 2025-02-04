<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/teams', name: 'admin_teams')]
    public function getTeams(TeamRepository $teamRepository): Response
    {
        return $this->render('admin/admin_equipes.html.twig', [
            'teams' => $teamRepository->findAll()
        ]);
    }

    #[Route('/teams/{id}', name: 'admin_teams_details')]
    public function getTeamsDetails($id): Response
    {
        return $this->render('admin/admin_equipe_details.html.twig', [
            'id' => $id,
        ]);
    }

}
