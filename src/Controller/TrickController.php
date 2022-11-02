<?php

namespace App\Controller;


use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;


#[Route('/trick')]
class TrickController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TrickRepository $trickRepository;


    public function __construct(EntityManagerInterface $entityManager, TrickRepository $trickRepository)
    {
        $this->entityManager = $entityManager;
        $this->trickRepository = $trickRepository;
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
//        dd($form->isValid());
        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->getData();
//            dd($trick);
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
//metre slug = id
//    #[Route('/{id}/{slug}/show', name: 'app_trick_show', methods: ['GET'])]
    #[Route('/{slug}/show', name: 'app_trick_show', methods: ['GET'])]
    public function show(Trick $trick): Response
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_trick_edit')]
    public function edit(Request $request, string $slug): Response
    {
        // On récupère le slug tu trick et on vérifie qu'il existe
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {
            //S'il n'existe pas, erreur 404
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Impossible de trouver ce trick');
        }
        // On crée le formulaire
        $form = $this->createForm(TrickType::class, $trick);
        // On associe la requête
        $form->handleRequest($request);
        // Si le formulaire est soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère notre objet modifié
            $trick = $form->getData();
            // On instancie un slugger ascii
            $slugger = new AsciiSlugger();
            // On récupère le slug saisit dans le formulaire et on le reconvertit
            $slug = $slugger->slug($trick->getSlug());
            // On remet le slug altéré si nécessaire
            $trick->setSlug($slug);
//            foreach ($images as $image){
//                $images->getImages($image);
//            }
            // On sauvegarde en BDD
            $this->entityManager->persist($trick);
            $this->entityManager->flush();
            // On renvoie l'utilisateur avec le message flash
            $this->addFlash('success', "Modifications enregistrées avec succès!");
            return $this->redirectToRoute('app_edit_image_to_trick', ['slug' => $trick->getSlug()]);
        }
        // Sinon on affiche la page et le formulaire
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
