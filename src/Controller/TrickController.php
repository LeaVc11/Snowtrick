<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    #[Route('/trick/new', name: 'app_trick_new')]
    public function index(Request $request): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);

        return $this->render('trick/index.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
