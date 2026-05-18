<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, UserRepository $userRepository): Response
    {
        if ($request->getSession()->get('user_id')) {
            return $this->redirectToRoute('app_meuble_index');
        }

        $error = null;
        $lastUsername = '';

        if ($request->isMethod('POST')) {
            $lastUsername = (string) $request->request->get('_username', '');
            $password = (string) $request->request->get('_password', '');
            $user = $userRepository->findOneBy(['email' => $lastUsername]);

            if ($user && password_verify($password, $user->getPassword())) {
                $session = $request->getSession();
                $session->set('user_id', $user->getId());
                $session->set('user_email', $user->getEmail());
                $session->set('user_name', $user->getPrenom() ?: $user->getEmail());
                $session->set('user_roles', $user->getRoles());

                if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                    return $this->redirectToRoute('admin_dashboard');
                }

                return $this->redirectToRoute('app_meuble_index');
            }

            $error = 'Email ou mot de passe incorrect.';
        }

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->clear();

        return $this->redirectToRoute('app_meuble_index');
    }
}
