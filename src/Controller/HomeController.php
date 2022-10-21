<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{


    #[Route('/', name: 'app_home')]
    public function index(MailService $mailService,TrickRepository $repository ): Response
    {
        $tricks = $repository->findAll();
        $mailService->send('snowtrick42@gmail.com', "johndoe", "Mon premier mail", "Bonjour");
        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks
        ]);
    }
}
