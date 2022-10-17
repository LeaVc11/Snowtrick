<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Repository\ResetPasswordRepository;
use App\Service\Mail;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/reset/password', name: 'app_reset_password')]
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
//     dd($user);
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
                $content = "Bonjour, ".$user->getUsername().
                    "<br/> Vous avez demandé à réinitialiser votre mot de passe sur le site Snowtrick.<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href=".$url."> mettre à jour votre mot de passe.</a>";
                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getUsername(),'Réinitialiser votre mot de passe', $content);


            }
        }
        return $this->render('reset_password/index.html.twig');
    }

    #[NoReturn] #[Route('/edit/password/{token}', name: 'app_update_password')]
    public function update($token): Response
    {
//        dd($token);
        $reset_password = $this->entityManager->getRepository(ResetPasswordRepository::class)->findOneByToken($token);
        if (!$reset_password){
            return $this->redirectToRoute('app_reset_password')
        }
    }

}
