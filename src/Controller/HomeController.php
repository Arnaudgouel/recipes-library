<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        // 4 recettes mises en avant au hasard
        $featuredRecipes = $recipeRepository->findRandomRecipes(4);
        
        // 4 recettes de la saison courante (ou sans saison = toutes les saisons)
        $seasonalRecipes = $recipeRepository->findRecipesByCurrentSeason(4);
        
        return $this->render('home/index.html.twig', [
            'featuredRecipes' => $featuredRecipes,
            'seasonalRecipes' => $seasonalRecipes,
        ]);
    }
}

