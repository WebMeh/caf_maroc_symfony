<?php

namespace App\Controller;

use App\Entity\Billet;
use App\Entity\Matche;
use App\Form\BilletType;
use App\Repository\BilletRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/billet')]
final class BilletController extends AbstractController
{

    // Voir la liste des billets en attente par l'admin
    #[Route('/en-attente', name: 'admin_billets_en_attente')]
    public function billetsEnAttente(BilletRepository $billetRepository): Response
    {
        $billets = $billetRepository->findBilletsEnAttente();

        return $this->render('admin/billets_en_attente.html.twig', [
            'billets' => $billets,
        ]);
    }

    // Voir la liste des billets vendus
    #[Route('/vendus', name: 'admin_billets_vendus')]
    public function billetsVendus(BilletRepository $billetRepository): Response
    {
        $billetsParMatch = $billetRepository->countBilletsVendusParMatch();

        return $this->render('admin/billets_vendus.html.twig', [
            'billetsParMatch' => $billetsParMatch,
        ]);
    }

    // Approuver une demande de billet par l'admin
    #[Route('/admin/billet/{id}/approuver', name: 'admin_billet_approuver')]
    public function approuverBillet(Billet $billet, EntityManagerInterface $entityManager): Response
    {
        // Générer un tracking number unique
        $trackingNumber = strtoupper(bin2hex(random_bytes(5))); // Ex: "A7F9D3B21E"
        $billet->setTrackingNumber($trackingNumber);
        $billet->setStatut('approuvé');
        $entityManager->flush();

        $this->addFlash('success', "Billet approuvé avec le numéro de suivi : $trackingNumber");

        return $this->redirectToRoute('admin_billets_en_attente');
    }

    //Refuser une demande de billet par l'admine
    #[Route('/admin/billet/{id}/refuser', name: 'admin_billet_refuser')]
    public function refuserBillet(Billet $billet, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($billet);
        $entityManager->flush();

        $this->addFlash('danger', 'Le billet a été refusé et supprimé.');
        return $this->redirectToRoute('admin_billets_en_attente');
    }

    // Réserver un billet pour un match connu par son id
    #[IsGranted('ROLE_USER')]
    #[Route("/matche/{id}/reserver", name: 'billet_reserver', methods: ['POST', 'GET'])]
    public function reserver(Request $request, Matche $matche, EntityManagerInterface $entityManager)
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Créer un billet pour cet utilisateur
        $billet = new Billet();
        $billet->setMatche($matche);
        $billet->setAcheteur($this->getUser()); // L'utilisateur connecté est l'acheteur
        $billet->setPrix($matche->getPrixBillet()); // Le prix est pris depuis le match

        // Le billet n'est pas encore validé, il est en attente d'approbation par l'admin
        $billet->setValide(false);

        // Sauvegarder le billet
        $entityManager->persist($billet);
        $entityManager->flush();

        // Rediriger besr liste des billets de user
        $this->addFlash('success', 'Votre réservation a été enregistrée, en attente de validation par l\'admin.');
        return $this->redirectToRoute('user_billets_liste');
    }


    // Mes billets (User tickets)
    #[Route('/mes-billets', name: 'user_billets_liste')]
    #[IsGranted('ROLE_USER')]
    public function userBilletsListe(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté

        // Récupérer les billets de l'utilisateur
        $billets = $entityManager->getRepository(Billet::class)->findBy(['acheteur' => $user]);

        return $this->render('user/user_billets_liste.html.twig', [
            'billets' => $billets,
        ]);
    }

    // télécharger ke billet
    #[Route('/{id}/download', name: 'billet_download')]
    public function downloadBillet(int $id, BilletRepository $billetRepository): Response
    {
        $billet = $billetRepository->find($id);

        if (!$billet || $billet->getStatut() !== 'approuvé') {
            throw $this->createNotFoundException("Billet non trouvé ou non approuvé.");
        }

        // Récupérer l'acheteur
        $acheteur = $billet->getAcheteur();

        // Options pour DOMPDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Initialiser Dompdf
        $dompdf = new Dompdf($pdfOptions);


        // HTML du billet
        $html = $this->renderView('billet/billet_pdf.html.twig', [
            'billet' => $billet,
            'acheteur' => $acheteur
        ]);




        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le fichier PDF directement en mémoire
        $pdfContent = $dompdf->output();

        // Créer la réponse pour le téléchargement du fichier
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="Billet_' . $billet->getId() . '.pdf"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    #[Route(name: 'app_billet_index', methods: ['GET'])]
    public function index(BilletRepository $billetRepository): Response
    {
        return $this->render('admin/admin_billets.html.twig', [
            'billets' => $billetRepository->findAll()
        ]);
    }

    #[Route('/new', name: 'app_billet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $billet = new Billet();
        $form = $this->createForm(BilletType::class, $billet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($billet);
            $entityManager->flush();

            return $this->redirectToRoute('app_billet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('billet/new.html.twig', [
            'billet' => $billet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_billet_show', methods: ['GET'])]
    public function show(Billet $billet): Response
    {
        return $this->render('billet/show.html.twig', [
            'billet' => $billet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_billet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Billet $billet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BilletType::class, $billet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_billet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('billet/edit.html.twig', [
            'billet' => $billet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_billet_delete', methods: ['POST'])]
    public function delete(Request $request, Billet $billet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $billet->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($billet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_billet_index', [], Response::HTTP_SEE_OTHER);
    }
}
