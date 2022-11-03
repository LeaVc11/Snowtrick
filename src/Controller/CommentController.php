<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private TrickRepository $trickRepository;


    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository, TrickRepository $trickRepository)
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->trickRepository = $trickRepository;
    }

    #[Route('/', name: 'app_comment', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/new/trick/{slug}', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, string $slug, CommentRepository $commentRepository, TrickRepository $trickRepository): Response
    {
        // On récupère le slug tu trick et on vérifie qu'il existe
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {
            //S'il n'existe pas, erreur 404
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Impossible de trouver ce commentaire');
        }
        // On crée le formulaire
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_comment_new', [
                'slug'=> $trick->getSlug(),
                'trick' => $trick->getId()])]);
        // On associe la requête
        $form->handleRequest($request);
        // Si le formulaire est soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère notre commentaire
            $comment = $form->getData();
            $now = new DateTimeImmutable();
            // On associe le Trick
            $comment->setTrick($trick);
            $comment->setUser($this->getUser());
            $comment->setCreatedAt($now);
            $comment->setUpdatedAt($now);
            // On sauvegarde en BDD
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            // On renvoie l'utilisateur avec le message flash
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès!');
            return $this->redirectToRoute('app_trick_show',
                ['slug' => $trick->getSlug()]);
        }
//        dd($comment);
        // Sinon on affiche la page et le formulaire
        return $this->renderForm('comment/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $trick = $comment->getTrick();

        if ($form->isSubmitted() && $form->isValid()) {
            $comment
                ->setUpdatedAt(new \DateTimeImmutable('now'));
            $commentRepository->save($comment, true);
            $this->addFlash('success', 'Votre commentaire a été modifié avec succès!');
            return $this->redirectToRoute('app_comment', [
                'slug' => $trick->getSlug()]);
        }

        return $this->renderForm('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        $trick = $comment->getTrick();
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $commentRepository->remove($comment, true);
            $this->addFlash('success', 'Votre commentaire a été supprimé avec succès!');
        }

        return $this->redirectToRoute('app_trick_show',
            ['slug' => $trick->getSlug()], Response::HTTP_SEE_OTHER);
    }
}
