<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Service\AlertServiceInterface;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function index(Request $request, UserPasswordHasherInterface $encoder, MailService $mailService, AlertServiceInterface $alertService): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $password = $encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
            //upload des images
//            dd($imageData);
            $imageFile = $form->get('image')->getData();
            $imageName = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('image_directory'),
                $imageName
                );
            $user->setImage($imageName);
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $content = "Bonjour " . $user->getUsername() . "<br/>Bienvenue sur .";
            $mailService->send($user->getEmail(), $user->getUsername(), 'Bienvenue ', $content);

            $alertService->success('Votre inscription s\'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte.');

            return $this->redirectToRoute('app_home');
        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}