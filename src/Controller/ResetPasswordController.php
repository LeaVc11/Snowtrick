<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Service\AlertServiceInterface;
use App\Service\MailService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/reset/password', name: 'app_reset_password')]
    public function index(Request $request, MailService $mailService, AlertServiceInterface $alertService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            if ($user) {

                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new DateTime());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                $url = $this->generateUrl('app_update_password',
                    [
                        'token' => $reset_password->getToken()
                    ]);
                $content = "Bonjour, " . $user->getUsername() .
                    "<br/> Vous avez demandé à réinitialiser votre mot de passe sur le site Snowtrick.<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href=" . $url . "> mettre à jour votre mot de passe.</a>";

                $mailService->send($user->getEmail(), $user->getUsername(), 'Réinitialiser votre mot de passe', $content);

                $alertService->warning('Vous allez recevoir dans quelques secondes un mail avec la procédure pour réinitialiser votre mot de passe.');
            } else {
                $alertService->warning('Cette adresse email est inconnue.');
            }
        }
        return $this->render('reset_password/index.html.twig');
    }

    #[Route('/edit/password/{token}', name: 'app_update_password')]
    public function update(Request $request, string $token, UserPasswordHasherInterface $encoder): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$reset_password) {
            return $this->redirectToRoute('app_reset_password');
        }
        $now = new DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {

            $this->addFlash('notice', "Votre demande de mot de passe a expiré. Merci de la renouveler.");
            return $this->redirectToRoute('app_reset_password');
        }
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new_pwd = $form->get('new_password')->getData();
            $password = $encoder->hashPassword($reset_password->getUser(), $new_pwd);

            $reset_password->getUser()->setPassword($password);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a bien été mis à jour.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}






