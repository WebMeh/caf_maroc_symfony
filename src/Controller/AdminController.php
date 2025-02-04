<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_index')]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }

    #[Route('/teams', name: 'admin_teams')]
    public function getTeams(Request $request, TeamRepository $teamRepository): Response
    {
        // Récupérer la page courante (par défaut page 1)
        $page = $request->query->getInt('page', 1);
        $teamsPerPage = 6; // 6 équipes par page

        // Calculer l'offset (début de la page)
        $offset = ($page - 1) * $teamsPerPage;

        // Récupérer les équipes avec la pagination
        $teams = $teamRepository->findBy([], null, $teamsPerPage, $offset);

        // Compter le total des équipes pour la pagination
        $totalTeams = $teamRepository->count([]);

        // Calculer le nombre total de pages
        $totalPages = ceil($totalTeams / $teamsPerPage);
        return $this->render('admin/admin_equipes.html.twig', [
            'teams' => $teams,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
    }

    #[Route('/teams/{id}', name: 'admin_teams_details')]
    public function getTeamsDetails($id): Response
    {
        return $this->render('admin/admin_equipe_details.html.twig', [
            'id' => $id,
        ]);
    }

    // Route pour afficher la liste des joueurs d'une équipe
    #[Route('/teams/{teamId}/players', name: 'admin_players_list')]
    public function playersList(int $teamId, TeamRepository $teamRepository, PlayerRepository $playerRepository): Response
    {
        // Récupérer l'équipe par son ID
        $team = $teamRepository->find($teamId);

        if (!$team) {
            // Si l'équipe n'existe pas, on redirige ou on affiche une erreur
            $this->addFlash('error', 'Équipe non trouvée');
            return $this->redirectToRoute('admin_teams');
        }

        // Récupérer tous les joueurs de l'équipe
        $players = $playerRepository->findBy(['team' => $team]);

        return $this->render('admin/admin_players_list.html.twig', [
            'team' => $team,
            'players' => $players,
        ]);
    }
}
