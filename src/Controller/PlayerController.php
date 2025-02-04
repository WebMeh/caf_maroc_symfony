<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/player')]
final class PlayerController extends AbstractController
{
    #[Route(name: 'app_player_index', methods: ['GET'])]
    public function index(PlayerRepository $playerRepository): Response
    {
        return $this->render('player/index.html.twig', [
            'players' => $playerRepository->findAll(),
        ]);
    }

    #[Route('/new/{teamId}', name: 'admin_player_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TeamRepository $teamRepository, int $teamId): Response
    {
        $team = $teamRepository->find($teamId);
        if (!$team) {
            throw $this->createNotFoundException("Équipe non trouvée.");
        }

        $player = new Player();
        $player->setTeam($team);

        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($player);
            $entityManager->flush();

            return $this->redirectToRoute('admin_players_list', [
                'teamId'=>$teamId
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('player/new.html.twig', [
            'player' => $player,
            'form' => $form,
            'team' => $team
        ]);
    }

    #[Route('/{id}', name: 'app_player_show', methods: ['GET'])]
    public function show(Player $player): Response
    {
        return $this->render('player/show.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_player_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Player $player, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_players_list', [
                'teamId'=> $player->getTeam()->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('player/edit.html.twig', [
            'team' =>$player->getTeam(),
            'player' => $player,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_player_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, PlayerRepository $playerRepository, EntityManagerInterface $entityManager): Response
    {

        $player = $playerRepository->find($id);
        if ($this->isCsrfTokenValid('delete' . $player->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($player);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_players_list', [
            'teamId' => $player->getTeam()->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
