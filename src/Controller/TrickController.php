<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/trick/new', name: 'app_trick_new')]
    public function index(Request $request): Response
    {

        $notification = null;
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $trick = $form->getData();

            $trick->setAuthor($this->getUser());

            $this->entityManager->persist($trick);
            $this->entityManager->flush();
            $notification = "Votre mot de passe a bien été mis à jour.";
        }

        return $this->render('trick/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
//
//    #[Route('/trick/edit/{slug}', name: 'app_trick_edit')]
//    public function index(Request $request): Response
//    {
//        $form = $this->createForm(TrickType::class, $trick);
//    }
}
