<?php

namespace App\Controller;

use App\Classe\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
$mail= new Mail();
$mail->send('snowtrick42@gmail.com', "johndoe", "Mon premier mail", "Bonjour");
        return $this->render('home/index.html.twig');
    }
}
