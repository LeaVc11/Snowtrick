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

class MediaToTrickController extends AbstractController
{
    private TrickRepository $trickRepository;
    private ImageRepository $imageRepository;
    private VideoRepository $videoRepository;


    public function __construct( TrickRepository $trickRepository,ImageRepository $imageRepository, VideoRepository $videoRepository) {
        $this->trickRepository = $trickRepository;
        $this->imageRepository = $imageRepository;
        $this->videoRepository = $videoRepository;


    }
    #[Route('/add/image/to/{slug}', name: 'app_add_image_to_trick')]
    public function new(Request $request,string $slug, ImageRepository $imageRepository): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Impossible de trouver ce trick');
        }

        $imageData = new Image();
        $form = $this->createForm(ImageType::class, $imageData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */

            $imageFile= $form->get('fileName')->getData();

            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('image_directory'),
                $newFilename
            );

            $imageData->setFilename($newFilename);

            $imageData->setTrick($trick);

            $this->imageRepository->save($imageData, true);

            return $this->redirectToRoute('app_add_video_to_trick', [
                'slug' => $trick->getSlug()]);
        }
        return $this->renderForm('image/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/edit/image/to/{slug}', name: 'app_edit_image_to_trick')]
    public function edit(Request $request,string $slug): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Impossible de trouver ce trick');
        }
        $imageData = new Image();
        $form = $this->createForm(ImageType::class, $imageData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile= $form->get('fileName')->getData();
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('image_directory'),
                $newFilename
            );
            $imageData->setFilename($newFilename);
            $imageData->setTrick($trick);
            $this->imageRepository->save($imageData, true);
            return $this->redirectToRoute('app_add_video_to_trick', [
                'slug' => $trick->getSlug()]);
        }
        return $this->renderForm('image/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/add/video/to/{slug}', name: 'app_add_video_to_trick')]
    public function index(Request $request,string $slug): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if ($trick === null) {
            throw new NotFoundHttpException('Impossible de trouver ce trick');
        }
        $videoData = new Video();
        $form = $this->createForm(VideoType::class, $videoData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $video */
            $video= $form->get('link')->getData();
            $videoData->setLink($video);
            $videoData->setTrick($trick);
            $this->videoRepository->save($videoData,true);

            return $this->redirectToRoute('app_home');
        }
        return $this->renderForm('video/index.html.twig', [
            'video'=> $videoData,
            'form' => $form,
            'trick' => $trick,
        ]);
    }
}
