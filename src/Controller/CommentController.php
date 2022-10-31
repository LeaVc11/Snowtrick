<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/', name: 'app_comment', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/new/{trick}', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CommentRepository $commentRepository, Trick $trick): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment,
            ['action' => $this->generateUrl('app_comment_new', ['trick' => $trick->getId()])]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new DateTimeImmutable();
            $comment->setTrick($trick)
                ->setCreatedAt($now)
                ->setAuthor($this->getUser())
                ->setUpdatedAt($now);
            $commentRepository->save($comment, true);
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès!');

            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
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
                ->setUpdatedAt(new \DateTimeImmutable('now'))
            ;
            $commentRepository->save($comment, true);
            $this->addFlash('success', 'Votre commentaire a été modifié avec succès!');

            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        $trick = $comment->getTrick();
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $commentRepository->remove($comment, true);
            $this->addFlash('success', 'Votre commentaire a été supprimé avec succès!');
        }

        return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()], Response::HTTP_SEE_OTHER);
    }
}
