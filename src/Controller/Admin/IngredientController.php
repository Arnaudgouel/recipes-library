<?php

namespace App\Controller\Admin;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use App\Service\CsvImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ingredient')]
#[IsGranted('ROLE_ADMIN')]
class IngredientController extends AbstractController
{
    #[Route('', name: 'admin_ingredient_index')]
    public function index(): Response
    {
        return $this->render('admin/ingredient/index.html.twig', [
            'entityClass' => Ingredient::class,
        ]);
    }

    #[Route('/new', name: 'admin_ingredient_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ingredient);
            $em->flush();
            
            $this->addFlash('success', 'Ingrédient créé avec succès.');
            return $this->redirectToRoute('admin_ingredient_index');
        }

        return $this->render('admin/ingredient/form.html.twig', [
            'form' => $form,
            'ingredient' => $ingredient,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_ingredient_edit')]
    public function edit(Ingredient $ingredient, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'Ingrédient modifié avec succès.');
            return $this->redirectToRoute('admin_ingredient_index');
        }

        return $this->render('admin/ingredient/form.html.twig', [
            'form' => $form,
            'ingredient' => $ingredient,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_ingredient_delete', methods: ['GET', 'POST'])]
    public function delete(Ingredient $ingredient, EntityManagerInterface $em): Response
    {
        $em->remove($ingredient);
        $em->flush();
        
        $this->addFlash('success', 'Ingrédient supprimé avec succès.');
        return $this->redirectToRoute('admin_ingredient_index');
    }

    #[Route('/import', name: 'admin_ingredient_import', methods: ['POST'])]
    public function import(Request $request, CsvImportService $csvImportService): Response
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('csv_file');
        
        if ($file) {
            try {
                $count = $csvImportService->importIngredients($file);
                $this->addFlash('success', sprintf('%d ingrédients importés avec succès.', $count));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'import : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('admin_ingredient_index');
    }
}

