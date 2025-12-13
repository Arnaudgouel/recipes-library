<?php

namespace App\Controller\Admin;

use App\Entity\CategoryRecipe;
use App\Form\CategoryRecipeType;
use App\Service\CsvImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/category')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    #[Route('', name: 'admin_category_index')]
    public function index(): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'entityClass' => CategoryRecipe::class,
        ]);
    }

    #[Route('/new', name: 'admin_category_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $category = new CategoryRecipe();
        $form = $this->createForm(CategoryRecipeType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            
            $this->addFlash('success', 'Catégorie créée avec succès.');
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/form.html.twig', [
            'form' => $form,
            'category' => $category,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_category_edit')]
    public function edit(CategoryRecipe $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryRecipeType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'Catégorie modifiée avec succès.');
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/form.html.twig', [
            'form' => $form,
            'category' => $category,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_category_delete', methods: ['GET', 'POST'])]
    public function delete(CategoryRecipe $category, EntityManagerInterface $em): Response
    {
        $em->remove($category);
        $em->flush();
        
        $this->addFlash('success', 'Catégorie supprimée avec succès.');
        return $this->redirectToRoute('admin_category_index');
    }

    #[Route('/import', name: 'admin_category_import', methods: ['POST'])]
    public function import(Request $request, CsvImportService $csvImportService): Response
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('csv_file');
        
        if ($file) {
            try {
                $count = $csvImportService->importCategories($file);
                $this->addFlash('success', sprintf('%d catégories importées avec succès.', $count));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'import : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('admin_category_index');
    }
}

