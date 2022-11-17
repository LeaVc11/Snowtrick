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
        $this->addFlash('success', 'Vous êtes maintenant connecté sur votre espace compte!');
        return $this->render('account/index.html.twig');
    }
}
