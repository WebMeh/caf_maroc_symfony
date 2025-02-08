<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est connecté, on redirige selon son rôle
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles(); // Récupérer les rôles

            // Vérifier précisément si ROLE_ADMIN est dans la liste des rôles
            if (in_array('ROLE_ADMIN', $roles, true)) {
                return $this->redirectToRoute('admin_index'); // Redirection admin
            } else {
                return $this->redirectToRoute('home'); // Redirection utilisateur
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
