<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/category')]
class CategoryController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;


    public function __construct(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
    }
    #[Route('/', name: 'app_category', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/show.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }
    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $name = $category->getName();
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($name);
            $category->setSlug($slug);
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            $this->addFlash('success', 'Nouvelle catégorie a été ajouté avec succès!');
            return $this->redirectToRoute('app_category', [
                'slug' => $category->getSlug()]);
        }
        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{slug}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $this->entityManager->persist($category);

            $this->entityManager->flush();

            return $this->redirectToRoute('app_category', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }
    #[Route('/{slug}/delete', name: 'app_category_delete')]
    public function delete(Category $category): Response
    {

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        $this->addFlash('success', "La catégorie a été supprimé avec succès!");
        return $this->redirectToRoute('app_category', [], Response::HTTP_SEE_OTHER);
    }

}
