<?php

namespace App\Controller;

use App\Repository\BilletRepository;
use App\Repository\MatcheRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/can-2025', name: 'home')]
    public function index(
        MatcheRepository $matcheRepository,
        TeamRepository $teamRepository,
        BilletRepository $billetRepository
    ): Response {
        // 1️⃣ Récupérer les 3 prochains matchs les plus proches
        $prochainsMatchs = $matcheRepository->createQueryBuilder('m')
            ->where('m.date > :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('m.date', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        // 2️ Récupérer les derniers matchs joués
        $derniersMatchs = $matcheRepository->createQueryBuilder('m')
            ->where('m.date < :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        // 3️ Récupérer le classement des équipes (ordre par points)
        // Récupérer tous les matchs
        $matches = $matcheRepository->findAll();
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

        //  Nombre total de billets vendus
        $totalBilletsVendus = $billetRepository->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.statut = :statut') // On suppose qu'il y a un champ "status" pour validé/non-validé
            ->setParameter('statut', 'approuvé')
            ->getQuery()
            ->getSingleScalarResult();

        //  Statistiques générales
        $totalButs = $matcheRepository->createQueryBuilder('m')
            ->select('SUM(m.score1 + m.score2)')
            ->getQuery()
            ->getSingleScalarResult();



        //  Meilleur buteur (exemple)
        $meilleurButeur = $teamRepository->createQueryBuilder('t')
            ->orderBy('t.goalsFor', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();


        return $this->render('welcome.html.twig', [
            'prochainsMatchs' => $prochainsMatchs,
            'derniersMatchs' => $derniersMatchs,
            'teams' => $teams,
            'totalBilletsVendus' => $totalBilletsVendus,
            'totalButs' => $totalButs,
            'meilleurButeur' => $meilleurButeur
        ]);
    }
}
