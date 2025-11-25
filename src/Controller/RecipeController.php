<?php

namespace App\Controller;

use App\Entity\Recipe;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/recette', name: 'app_recipe_')]
final class RecipeController extends AbstractController
{
    #[Route('/{id}', name: 'show')]
    public function show(Recipe $recipe): Response
    {

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }

    #[Route('/{id}/pdf', name: 'pdf')]
    public function pdf(Recipe $recipe): Response
    {
        // Configuration de DomPDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);

        // Instancier DomPDF
        $dompdf = new Dompdf($pdfOptions);

        // Générer le HTML depuis le template Twig
        $html = $this->renderView('recipe/pdf.html.twig', [
            'recipe' => $recipe
        ]);

        // Charger le HTML dans DomPDF
        $dompdf->loadHtml($html);

        // Rendre le PDF
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le nom du fichier
        $filename = 'recette-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($recipe->getTitle())) . '.pdf';

        // Retourner la réponse PDF (affichage dans le navigateur)
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }
}
