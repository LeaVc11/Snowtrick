<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private $entityManager;

    #[Route('/register', name: 'app_register')]
    public function index(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findByEmail($user->getEmail());

            if (!$search_email){
                $password = $encoder->hashPassword($user, $user->getPassword());

                $user->setPassword($password);

                $entityManager->persist($user);
                $entityManager->flush();
                $notification="Votre inscription a été enregistré. Vous pouvez vous connecter dès à présent ";
            }else{
                $notification="Votre email exite déjà ";
            }



        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}