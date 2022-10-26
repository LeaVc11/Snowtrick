<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Video;
use App\Form\ImageType;
use App\Form\VideoType;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AddMediaToTrickController extends AbstractController
{
    private TrickRepository $trickRepository;
    private ImageRepository $imageRepository;
    private VideoRepository $videoRepository;

    public function __construct( TrickRepository $trickRepository, ImageRepository $imageRepository, VideoRepository $videoRepository) {
        $this->trickRepository = $trickRepository;
        $this->imageRepository = $imageRepository;
        $this->videoRepository = $videoRepository;


    }
    #[Route('/add/image/to/{slug}', name: 'app_add_image_to_trick')]
    public function __invoke(Request $request,string $slug, ImageRepository $imageRepository): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Impossible de trouver ce trick');
        }
        // On prépare le formulaire
        $imageData = new Image();
        $form = $this->createForm(ImageType::class, $imageData);
        $form->handleRequest($request);
        // Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
//            dd($imageData);
            $imageFile= $form->get('fileName')->getData();
            //Ici il faut copier l'image dans le bon répertoire
//            dd($imageFile);
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('image_directory'),
                $newFilename
            );
            // * Un setFilename pour mettre le bon chemin local)
            $imageData->setFilename($newFilename);
            // * Un setTrick($trick);
            $imageData->setTrick($trick);
            // * Sauvegarder l'image en BDD
//            dump($imageData,$imageFile);
            $this->imageRepository->save($imageData, true);
            // L'image est ajoutéee et on retourne à la page home
            return $this->redirectToRoute('app_home');
        }
        return $this->renderForm('image/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/add/video/to/{slug}', name: 'app_add_video_to_trick')]
    public function index(Request $request,string $slug, VideoRepository $videoRepository,SluggerInterface $slugger): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {
            throw new NotFoundHttpException('Impossible de trouver ce trick');
        }
        // On prépare le formulaire
        $videoFile = new Video();
        $form = $this->createForm(VideoType::class, $videoFile);
        $form->handleRequest($request);
        // Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $videoFile */
            $videoFile= $form->get('video')->getData();
            //Ici il faut copier l'image dans le bon répertoire
            $originalFilename = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = uniqid() . '.' . $videoFile->guessExtension();
            $videoFile->move(
                $this->getParameter('video_directory'),
                $newFilename
            );
            $videoFile->setLink($newFilename);
            $videoFile->setTrick($trick);
            $this->videoRepository->save($videoFile);

            // L'image est ajoutéee et on retourne à la page home
            return $this->redirectToRoute('app_home');
        }
        // Sinon on affiche la page et le formulaire

        return $this->renderForm('video/_form.html.twig', [
            'form' => $form,
        ]);
    }
}
