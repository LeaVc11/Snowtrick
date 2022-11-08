<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }
    #[Route('/account/profil', name: 'app_account_profile')]
    public function myProfile(): Response
    {
        $user = $this->getUser();
        return $this->render('account/index.html.twig');
//        'user' => $user;
    }
}

