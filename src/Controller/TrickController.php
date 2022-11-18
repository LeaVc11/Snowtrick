<?php

namespace App\Controller;


use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;


#[Route('/trick')]
class TrickController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TrickRepository $trickRepository;
    private PaginatorInterface $paginator;
    private RequestStack $request;


    public function __construct(EntityManagerInterface $entityManager, TrickRepository $trickRepository, PaginatorInterface $paginator, RequestStack $request )
    {
        $this->entityManager = $entityManager;
        $this->trickRepository = $trickRepository;
        $this->paginator = $paginator;
        $this->request = $request;
    }
    public function getTricksByTrick( Trick $trick, Request $request ): Response
    {
        $queryTricks = $this->trickRepository->getQueryByTrick($trick);
        $pagination = $this->paginator->paginate(
            $queryTricks, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('trick/index.html.twig', [
            'pagination' => $pagination,
        ]);

    }
    #[Route('/{page<\d+>?1}', name: 'app_trick', methods: ['GET'])]
    public function index(): Response
    {
        $tricks = $this->entityManager->getRepository(Trick::class)->findAll();
//        dd($tricks);

        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    #[Route('/new', name: 'app_trick_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->getData();

            $this->entityManager->persist($trick);

            $this->entityManager->flush();

            $this->addFlash('success', 'Nouveau trick a été ajouté avec succès!');

        return $this->redirectToRoute('app_add_image_to_trick', [
            'slug' => $trick->getSlug()]);
    }
        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/show', name: 'app_trick_show', methods: ['GET'])]
    public function show(Trick $trick, Request $request): Response
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'request' =>$request
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_trick_edit')]
    public function edit(Request $request, string $slug): Response
    {

        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {

            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Impossible de trouver ce trick');
        }

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $trick = $form->getData();

            $slugger = new AsciiSlugger();

            $slug = $slugger->slug($trick->getSlug());

            $trick->setSlug($slug);

            $this->entityManager->persist($trick);
            $this->entityManager->flush();

            $this->addFlash('success', "Modifications enregistrées avec succès!");
            return $this->redirectToRoute('app_edit_image_to_trick', ['slug' => $trick->getSlug()]);
        }

        return $this->renderForm('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}/delete', name: 'app_trick_delete')]
    public function delete(Trick $trick): Response
    {

        $this->entityManager->remove($trick);
        $this->entityManager->flush();

        $this->addFlash('success', "Le Trick a été supprimé avec succès!");
        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

}
