<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private TrickRepository $trickRepository;
    private PaginatorInterface $paginator;
    private RequestStack $request;


    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository, TrickRepository $trickRepository, PaginatorInterface $paginator, RequestStack $request )
    {
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->trickRepository = $trickRepository;
        $this->paginator = $paginator;
        $this->request = $request;
    }

    public function getCommentsByTrick( Trick $trick, Request $request ): Response
    {
        $queryComments = $this->commentRepository->getQueryByTrick($trick);
        $pagination = $this->paginator->paginate(
            $queryComments,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('comment/index.html.twig', [
            'pagination' => $pagination,

        ]);

    }

    #[Route('/new/trick/{slug}', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, string $slug, CommentRepository $commentRepository, TrickRepository $trickRepository): Response
    {

        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {

            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Impossible de trouver ce commentaire');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_comment_new', [
                'slug' => $trick->getSlug(),
                'trick' => $trick->getId()])]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment = $form->getData();
            $now = new DateTimeImmutable();

            $comment->setTrick($trick);
            $comment->setUser($this->getUser());
            $comment->setCreatedAt($now);
            $comment->setUpdatedAt($now);

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès!');
            return $this->redirectToRoute('app_trick_show',
                ['slug' => $trick->getSlug()]);
        }
        return $this->renderForm('comment/new.html.twig', [
            'comment' => $comment,
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
            return $this->redirectToRoute('app_trick_show', [
                'slug' => $trick->getSlug()]);
        }

        return $this->renderForm('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_comment_delete', methods: ['GET'])]
    public function delete(Comment $comment): Response
    {

        $this->entityManager->remove($comment);
        $this->entityManager->flush();
        $this->addFlash('success', 'Votre commentaire a été supprimé avec succès!');

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
