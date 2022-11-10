<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(Request $request, CommentRepository $commentRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $total = $commentRepository->countComments();
        $comments = $commentRepository->getCommentsForPage($page, CommentRepository::PAGINATOR_PER_PAGE);

        return $this->render('comment/index.html.twig', [
            'comments' => $comments,
            'page' => $page,
            'total' => $total
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
                'slug' => $trick->getSlug(),
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
//        dd($comment);
        $this->entityManager->remove($comment);
        $this->entityManager->flush();
        $this->addFlash('success', 'Votre commentaire a été supprimé avec succès!');


        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
