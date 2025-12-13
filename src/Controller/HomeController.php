<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use App\Service\SeasonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository, SeasonService $seasonService): Response
    {
        // Récupérer des recettes aléatoires (6 recettes)
        $randomRecipes = $recipeRepository->findRandomRecipes(6);
        
        // Récupérer des recettes de la saison courante (6 recettes)
        $seasonRecipes = $recipeRepository->findRecipesByCurrentSeason(6);
        
        return $this->render('home/index.html.twig', [
            'randomRecipes' => $randomRecipes,
            'seasonRecipes' => $seasonRecipes,
        ]);
    }
}

