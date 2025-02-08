<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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


    // Créer nouvella équipe par l'admin
    #[Route('/new', name: 'admin_team_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
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

    // Modifier les infos d'une équipe par l'admin
    #[Route('/{id}/edit', name: 'admin_team_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
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

    // Route pour afficher la liste des joueurs d'une équipe 
    #[Route('/{teamId}/players', name: 'team_players_list')]
    public function playersList(int $teamId, TeamRepository $teamRepository, PlayerRepository $playerRepository): Response
    {
        // Récupérer l'équipe par son ID
        $team = $teamRepository->find($teamId);

        if (!$team) {
            // Si l'équipe n'existe pas, on redirige ou on affiche une erreur
            $this->addFlash('error', 'Équipe non trouvée');
            return $this->redirectToRoute('user_show_teams');
        }

        // Récupérer les joueurs de l'équipe, groupés par poste
        $joueurs = $playerRepository->findBy(['team' => $team], ['position' => 'ASC']);

        // Séparer les joueurs par poste (par exemple, gardiens, défenseurs, attaquants)
        $gardiens = [];
        $defenseurs = [];
        $milieux = [];
        $attaquants = [];

        foreach ($joueurs as $joueur) {
            switch ($joueur->getPosition()) {
                case 'Gardien':
                    $gardiens[] = $joueur;
                    break;
                case 'Défenseur':
                    $defenseurs[] = $joueur;
                    break;
                case 'Milieu':
                    $milieux[] = $joueur;
                    break;
                case 'Attaquant':
                    $attaquants[] = $joueur;
                    break;
            }
        }

        return $this->render('user/team_details.html.twig', [
            'equipe' => $team,
            'gardiens' => $gardiens,
            'defenseurs' => $defenseurs,
            'milieux' => $milieux,
            'attaquants' => $attaquants
        ]);
    }


    // Supprimer l'équipe par l'admine
    #[Route('/{id}', name: 'app_team_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
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
