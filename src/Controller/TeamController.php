<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/team')]
final class TeamController extends AbstractController
{
    #[Route(name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('team/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_team_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logo')->getData();

            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('logos_directory'),
                        $newFilename
                    );
                    $team->setLogo($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier.');
                }
            }

            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('admin_teams', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Récupérer le fichier téléchargé pour le logo
            $logoFile = $form->get('logo')->getData();

            if ($logoFile) {
                // Générez un nom unique pour le fichier
                $newFilename = uniqid() . '.' . $logoFile->guessExtension();

                // Déplace le fichier dans le dossier de stockage
                try {
                    $logoFile->move(
                        $this->getParameter('logos_directory'), // Le répertoire où stocker les logos
                        $newFilename
                    );
                    $team->setLogo($newFilename); // Mettre à jour le champ logo de l'équipe
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du logo.');
                }
            }

            // Sauvegarde des modifications de l'équipe
            $entityManager->flush();

            return $this->redirectToRoute('admin_teams', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team/edit.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $team->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer tous les joueurs de l'équipe
            $players = $team->getPlayers();
            foreach ($players as $player) {
                $entityManager->remove($player);
            }
            // Supprimer l'équipe
            $entityManager->remove($team);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_teams', [], Response::HTTP_SEE_OTHER);
    }
}
