<?php

namespace App\Controller;



use App\Entity\Image;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }
    #[Route('/account_profile', name: 'account_profile')]
    public function myProfile(Request $request,UserRepository $userRepository,): Response
    {
        // On prépare le formulaire
        $imageData = new Image();
        $form = $this->createForm(RegisterType::class, $imageData);
        $form->handleRequest($request);
        // Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
//            dd($imageData);
            $imageFile = $form->get('image')->getData();
            //Ici il faut copier l'image dans le bon répertoire
//            dd($imageFile);

            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('image_directory'),
                $newFilename
            );
            // * Un setFilename pour mettre le bon chemin local)
            $imageData->setFilename($newFilename);

            // * Sauvegarder l'image en BDD
//            dd($imageData,$imageFile);
            $this->imageRepository->save($imageData, true);
            // L'image est ajoutéee et on retourne à la page home
            return $this->redirectToRoute('app_account');
        }
    }}



