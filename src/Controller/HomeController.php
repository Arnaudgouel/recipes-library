<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        // Obtenir la saison courante
        $currentSeason = $this->getCurrentSeason();
        
        // Récupérer des recettes aléatoires (6 recettes)
        $randomRecipes = $recipeRepository->findRandom(6);
        
        // Récupérer des recettes de la saison courante (6 recettes)
        $seasonRecipes = $recipeRepository->findBySeason($currentSeason, 6);
        
        return $this->render('home/index.html.twig', [
            'randomRecipes' => $randomRecipes,
            'seasonRecipes' => $seasonRecipes,
            'currentSeason' => $currentSeason,
        ]);
    }

    /**
     * Détermine la saison courante en fonction du mois
     */
    private function getCurrentSeason(): string
    {
        $month = (int) date('n');
        
        // Printemps : mars (3), avril (4), mai (5)
        // Été : juin (6), juillet (7), août (8)
        // Automne : septembre (9), octobre (10), novembre (11)
        // Hiver : décembre (12), janvier (1), février (2)
        
        if ($month >= 3 && $month <= 5) {
            return 'printemps';
        } elseif ($month >= 6 && $month <= 8) {
            return 'ete';
        } elseif ($month >= 9 && $month <= 11) {
            return 'automne';
        } else {
            return 'hiver';
        }
    }
}

