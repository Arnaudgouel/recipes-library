<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Service\CsvImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/recipe')]
#[IsGranted('ROLE_ADMIN')]
class RecipeController extends AbstractController
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/recipes-images')]
        private readonly string $uploadDir,
    ) {
    }

    #[Route('', name: 'admin_recipe_index')]
    public function index(): Response
    {
        return $this->render('admin/recipe/index.html.twig', [
            'entityClass' => Recipe::class,
        ]);
    }

    #[Route('/new', name: 'admin_recipe_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $recipe);
            
            $em->persist($recipe);
            $em->flush();
            
            $this->addFlash('success', 'Recette créée avec succès.');
            return $this->redirectToRoute('admin_recipe_index');
        }

        return $this->render('admin/recipe/form.html.twig', [
            'form' => $form,
            'recipe' => $recipe,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_recipe_edit')]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $recipe, $request);
            
            $em->flush();
            
            $this->addFlash('success', 'Recette modifiée avec succès.');
            return $this->redirectToRoute('admin_recipe_index');
        }

        return $this->render('admin/recipe/form.html.twig', [
            'form' => $form,
            'recipe' => $recipe,
            'is_edit' => true,
        ]);
    }

    private function handleImageUpload($form, Recipe $recipe, ?Request $request = null): void
    {
        // Supprimer l'image si demandé
        if ($request && $request->request->get('delete_image')) {
            $currentImage = $recipe->getImage();
            if ($currentImage) {
                $imagePath = $this->uploadDir . '/' . $currentImage;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $recipe->setImage(null);
            }
            return;
        }
        
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('image')->getData();
        
        if ($imageFile) {
            // Supprimer l'ancienne image si elle existe
            $currentImage = $recipe->getImage();
            if ($currentImage) {
                $imagePath = $this->uploadDir . '/' . $currentImage;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
            
            $imageFile->move($this->uploadDir, $newFilename);
            $recipe->setImage($newFilename);
        }
    }

    #[Route('/{id}/delete', name: 'admin_recipe_delete', methods: ['GET', 'POST'])]
    public function delete(Recipe $recipe, EntityManagerInterface $em): Response
    {
        $em->remove($recipe);
        $em->flush();
        
        $this->addFlash('success', 'Recette supprimée avec succès.');
        return $this->redirectToRoute('admin_recipe_index');
    }

    #[Route('/import', name: 'admin_recipe_import', methods: ['POST'])]
    public function import(Request $request, CsvImportService $csvImportService): Response
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('csv_file');
        
        if ($file) {
            try {
                $count = $csvImportService->importRecipes($file);
                $this->addFlash('success', sprintf('%d recettes importées avec succès.', $count));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'import : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('admin_recipe_index');
    }
}

