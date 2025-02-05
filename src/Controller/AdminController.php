<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\MatcheRepository;
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
    public function getTeamsDetails($id, Team $team): Response
    {
        return $this->render('admin/admin_equipe_details.html.twig', [
            'id' => $id,
            'team' => $team
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

    #[Route('/matches', name: 'admin_matches_list')]
    public function list(MatcheRepository $matcheRepository, Request $request)
    {
        $page = max(1, $request->query->getInt('page', 1)); // Récupérer la page actuelle (par défaut 1)
        $limit = 3; // Nombre de matchs par page
        $offset = ($page - 1) * $limit; // Calcul de l'offset

        // Récupérer les matchs paginés
        $matches = $matcheRepository->findBy([], ['date' => 'DESC'], $limit, $offset);

        // Nombre total de matchs
        $totalMatches = $matcheRepository->count([]);

        // Nombre total de pages
        $totalPages = ceil($totalMatches / $limit);

        return $this->render('admin/matches.html.twig', [
            'matches' => $matches,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    // Classement des équipes selon les points
    #[Route('/classement', name: 'admin_classement')]
    public function classement(TeamRepository $teamRepository, MatcheRepository $matchRepository): Response
    {
        // Récupérer tous les matchs
        $matches = $matchRepository->findAll();
        $teams = $teamRepository->findAll();

        // Initialiser les équipes avec les données de base
        foreach ($teams as $team) {
            $team->setPoints(0); // Points initiaux
            $team->setGoalsFor(0); // Buts marqués
            $team->setGoalsAgainst(0); // Buts encaissés
            $team->setGoalDifference(0); // Différence de buts
        }

        // Mettre à jour les données des équipes à partir des matchs
        foreach ($matches as $match) {
            $team1 = $match->getTeam1();
            $team2 = $match->getTeam2();

            // Mettre à jour les buts pour chaque équipe
            $team1->setGoalsFor($team1->getGoalsFor() + $match->getScore1());
            $team2->setGoalsFor($team2->getGoalsFor() + $match->getScore2());
            $team1->setGoalsAgainst($team1->getGoalsAgainst() + $match->getScore2());
            $team2->setGoalsAgainst($team2->getGoalsAgainst() + $match->getScore1());

            // Calculer la différence de buts
            $team1->setGoalDifference($team1->getGoalsFor() - $team1->getGoalsAgainst());
            $team2->setGoalDifference($team2->getGoalsFor() - $team2->getGoalsAgainst());

            // Mettre à jour les points
            if ($match->getScore1() > $match->getScore2()) {
                $team1->setPoints($team1->getPoints() + 3); // Victoire pour l'équipe 1
            } elseif ($match->getScore1() < $match->getScore2()) {
                $team2->setPoints($team2->getPoints() + 3); // Victoire pour l'équipe 2
            } else {
                $team1->setPoints($team1->getPoints() + 1); // Match nul
                $team2->setPoints($team2->getPoints() + 1);
            }
        }

        // Trier les équipes par points, puis différence de buts
        usort($teams, function ($a, $b) {
            if ($a->getPoints() !== $b->getPoints()) {
                return $b->getPoints() <=> $a->getPoints();
            }
            return $b->getGoalDifference() <=> $a->getGoalDifference();
        });

        return $this->render('admin/classement.html.twig', [
            'teams' => $teams
        ]);
    }
}
