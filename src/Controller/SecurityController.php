<?php

namespace App\Controller;

use App\Form\AccountType;
use App\Form\ChangePasswordType;
use App\Form\RegisterType;
use App\Service\AvatarFileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

         if ($this->getUser()) {
             return $this->redirectToRoute('app_account');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error ],

        );
    }
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route('/account/password', name: 'app_account_password')]
    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $notification = null;
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $old_pwd = $form->get('old_password')->getData();
            /*    dd($old_pwd);*/
            if ($encoder->isPasswordValid($user, $old_pwd)) {
                /*  die('ok)');*/
                $new_pwd = $form->get('new_password')->getData();
                /*dd($new_pwd);*/
                $password=$encoder->hashPassword($user,$new_pwd);

                $user->setPassword($password);
                $this->entityManager->flush();
                $notification="Votre mot de passe a bien été mis à jour.";
            }else{
                $notification = "Votre mot de passe actuel n'est pas le bon";
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }

    #[Route('/account/profil', name: 'app_account_profil')]
    public function MyProfile(Request $request, AvatarFileUploader $fileUploader): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $imgFile = $form->get('avatar')->getData();
            if ($file = $form->get('avatar')->get('file')->getData()) {
                if($user->getAvatar()->getUrl()) {
                    $fileUploader->deleteAvatarFile($user->getAvatar());
                }
                $imgFileName = $fileUploader->upload($file);
                $imgFile->setUrl($imgFileName);
            }
            $this->entityManager->flush();

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/profil.html.twig',[
            'accountForm' => $form->createView(),
        ]);
//        return $this->render('account/password.html.twig', [
//            'form' => $form->createView(),
//            'notification' => $notification
//        ]);
    }
}
