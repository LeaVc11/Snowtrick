<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/trick')]
class TrickController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_trick', methods: ['GET'])]
    public function index(): Response
    {
        $tricks = $this->entityManager->getRepository(Trick::class)->findAll();

        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    #[Route('/new', name: 'app_trick_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('medias')->getData();
            foreach ($imageFiles as $imageFile) {
                if ($file = $imageFile->getFile()) {
                    $imageFileName = $fileUploader->upload($file);
                    $imageFile->setUrl($imageFileName);
                }
            }
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($trick->getTitle());
            $trick->setSlug($slug);

            $trick->setUser($this->getUser());

            $this->entityManager->persist($trick);
            $this->entityManager->flush();

            $this->addFlash('success', 'Nouveau trick ajouté avec succès!');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/show', name: 'app_trick_show', methods: ['GET'])]
    #[ParamConverter('trick', Trick::class, ['mapping' => ['slug' => 'slug']])]
    public function show(Trick $trick): Response
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    #[ParamConverter('trick', Trick::class, ['mapping' => ['slug' => 'slug']])]
    public function edit(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trickRepository->save($trick, true);

            return $this->redirectToRoute('app_trick', [], Response::HTTP_SEE_OTHER);
        }
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($trick->getTitle());
        $trick->setSlug($slug);


        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}', name: 'app_trick_delete', methods: ['POST'])]
    #[ParamConverter('trick', Trick::class, ['mapping' => ['slug' => 'slug']])]
    public function delete(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $trickRepository->remove($trick, true);
        }

        return $this->redirectToRoute('app_trick', [], Response::HTTP_SEE_OTHER);
    }
}
