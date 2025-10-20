<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeListingController extends AbstractController
{
    #[Route('/recettes', name: 'app_recipe_listing')]
    public function index(): Response
    {
        return $this->render('recipe_listing/index.html.twig');
    }
}
