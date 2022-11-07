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
//    #[Route('/account_profile', name: 'account_profile')]
//    public function myProfile(Request $request,UserRepository $userRepository,): Response
//    {
//        $user = $this->getUser();
//        $imageData = new Image();
//        $form = $this->createForm(ImageType::class, $imageData);
//
//        $form->handleRequest($request);
//        return $this->render('account/index.html.twig');
//        'user' => $user,
//        'form' => $form->createView(),
//    }

}
