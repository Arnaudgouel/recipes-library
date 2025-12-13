<?php

namespace App\Controller\Admin;

use App\Repository\CategoryRecipeRepository;
use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        RecipeRepository $recipeRepository,
        IngredientRepository $ingredientRepository,
        CategoryRecipeRepository $categoryRepository,
        SeasonRepository $seasonRepository,
        UserRepository $userRepository
    ): Response {
        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => [
                'recipes' => $recipeRepository->count([]),
                'ingredients' => $ingredientRepository->count([]),
                'categories' => $categoryRepository->count([]),
                'seasons' => $seasonRepository->count([]),
                'users' => $userRepository->count([]),
            ],
        ]);
    }
}
