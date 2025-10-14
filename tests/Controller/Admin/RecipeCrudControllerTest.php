<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RecipeCrudControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private RecipeRepository $recipeRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->recipeRepository = $this->entityManager->getRepository(Recipe::class);
    }

    public function testImportPageAccess(): void
    {
        $this->client->request('GET', '/admin/recipe-import');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Import de recettes');
    }

    public function testImportWithValidCsvFile(): void
    {
        // Créer un fichier CSV temporaire valide
        $csvContent = "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n";
        $csvContent .= "\"Pasta Carbonara\",\"Délicieuse recette de pâtes\",4,15,20,\"carbonara.jpg\",\"Pâtes:400:g|Œufs:4:pièce\",\"1. Cuire les pâtes|2. Préparer la sauce\"";
        
        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'test-recipes.csv',
            'text/csv',
            null,
            true
        );

        // Compter les recettes avant l'import
        $initialCount = $this->recipeRepository->count([]);

        $this->client->request('POST', '/admin/recipe-import', [], [
            'csv_file' => $uploadedFile
        ]);

        // Vérifier la redirection après succès
        $this->assertResponseRedirects('/admin/recipe');
        
        // Vérifier qu'une recette a été ajoutée
        $finalCount = $this->recipeRepository->count([]);
        $this->assertEquals($initialCount + 1, $finalCount);

        // Vérifier que la recette a été créée correctement
        $recipe = $this->recipeRepository->findOneBy(['title' => 'Pasta Carbonara']);
        $this->assertNotNull($recipe);
        $this->assertEquals('Délicieuse recette de pâtes', $recipe->getDescription());
        $this->assertEquals(4, $recipe->getServings());
        $this->assertEquals(15, $recipe->getPrepMinutes());
        $this->assertEquals(20, $recipe->getCookMinutes());

        fclose($tempFile);
    }

    public function testImportWithNoFile(): void
    {
        $this->client->request('POST', '/admin/recipe-import');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Veuillez sélectionner un fichier CSV');
    }

    public function testImportWithInvalidFileType(): void
    {
        $tempFile = tmpfile();
        fwrite($tempFile, 'contenu non CSV');
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'test.txt',
            'text/plain',
            null,
            true
        );

        $this->client->request('POST', '/admin/recipe-import', [], [
            'csv_file' => $uploadedFile
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Le fichier doit être au format CSV');

        fclose($tempFile);
    }

    public function testImportWithInvalidCsvFormat(): void
    {
        $csvContent = "invalid,header\n";
        $csvContent .= "\"Test Recipe\",\"Description\"";
        
        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'invalid.csv',
            'text/csv',
            null,
            true
        );

        $this->client->request('POST', '/admin/recipe-import', [], [
            'csv_file' => $uploadedFile
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Format CSV invalide');

        fclose($tempFile);
    }

    public function testImportWithMissingTitle(): void
    {
        $csvContent = "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n";
        $csvContent .= "\"\",\"Description test\",2,15,30,\"test.jpg\",\"Ingrédient:100:g\",\"1. Première étape\"";
        
        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'missing-title.csv',
            'text/csv',
            null,
            true
        );

        $this->client->request('POST', '/admin/recipe-import', [], [
            'csv_file' => $uploadedFile
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Le titre est obligatoire');

        fclose($tempFile);
    }

    public function testImportWithMultipleRecipes(): void
    {
        $csvContent = "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n";
        $csvContent .= "\"Recipe 1\",\"Description 1\",2,15,30,\"test1.jpg\",\"Ingrédient:100:g\",\"1. Étape 1\"\n";
        $csvContent .= "\"Recipe 2\",\"Description 2\",4,20,45,\"test2.jpg\",\"Autre ingrédient:200:ml\",\"1. Étape 1|2. Étape 2\"";
        
        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'multiple.csv',
            'text/csv',
            null,
            true
        );

        $initialCount = $this->recipeRepository->count([]);

        $this->client->request('POST', '/admin/recipe-import', [], [
            'csv_file' => $uploadedFile
        ]);

        $this->assertResponseRedirects('/admin/recipe');
        
        $finalCount = $this->recipeRepository->count([]);
        $this->assertEquals($initialCount + 2, $finalCount);

        // Vérifier que les deux recettes ont été créées
        $recipe1 = $this->recipeRepository->findOneBy(['title' => 'Recipe 1']);
        $recipe2 = $this->recipeRepository->findOneBy(['title' => 'Recipe 2']);
        
        $this->assertNotNull($recipe1);
        $this->assertNotNull($recipe2);

        fclose($tempFile);
    }

    protected function tearDown(): void
    {
        // Nettoyer les données de test si nécessaire
        parent::tearDown();
    }
}
