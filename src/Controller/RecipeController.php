<?php

namespace App\Controller;

use App\Entity\Recipe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/recette', name: 'app_recipe_')]
final class RecipeController extends AbstractController
{
    #[Route('/liste', name: 'index')]
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Recipe $recipe): Response
    {

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }
}
