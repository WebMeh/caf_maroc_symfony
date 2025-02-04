<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/teams', name: 'admin_teams')]
    public function getTeams(): Response
    {
        return $this->render('admin/admin_equipes.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/teams/{id}', name: 'admin_teams_details')]
    public function getTeamsDetails($id): Response
    {
        return $this->render('admin/admin_equipe_details.html.twig', [
            'id' => $id,
        ]);
    }
}
