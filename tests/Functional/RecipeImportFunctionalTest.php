<?php

namespace App\Tests\Functional;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RecipeImportFunctionalTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private RecipeRepository $recipeRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->recipeRepository = $this->entityManager->getRepository(Recipe::class);
    }

    public function testCompleteImportWorkflow(): void
    {
        // 1. Accéder à la page d'import
        $crawler = $this->client->request('GET', '/admin/recipe-import');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Import de recettes');

        // 2. Vérifier que le formulaire est présent
        $form = $crawler->selectButton('Importer les recettes')->form();
        $this->assertNotNull($form);

        // 3. Préparer un fichier CSV valide
        $csvContent = $this->getValidCsvContent();
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

        // 4. Compter les recettes avant l'import
        $initialCount = $this->recipeRepository->count([]);

        // 5. Soumettre le formulaire avec le fichier
        $crawler = $this->client->submit($form, [
            'csv_file' => $uploadedFile
        ]);

        // 6. Vérifier la redirection et le message de succès
        $this->assertResponseRedirects('/admin/recipe');
        
        // 7. Suivre la redirection pour vérifier le message flash
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Import réussi');

        // 8. Vérifier que les recettes ont été créées
        $finalCount = $this->recipeRepository->count([]);
        $this->assertEquals($initialCount + 3, $finalCount);

        // 9. Vérifier les détails des recettes importées
        $this->assertRecipeExists('Pasta Carbonara', 'Une délicieuse recette de pâtes à la carbonara traditionnelle', 4, 15, 20);
        $this->assertRecipeExists('Salade César', 'Salade fraîche et croquante avec une sauce césar maison', 2, 10, 0);
        $this->assertRecipeExists('Tarte aux pommes', 'Une tarte aux pommes traditionnelle et savoureuse', 6, 30, 45);

        fclose($tempFile);
    }

    public function testImportWithValidationErrors(): void
    {
        $crawler = $this->client->request('GET', '/admin/recipe-import');
        
        // Préparer un fichier CSV avec des erreurs
        $csvContent = $this->getInvalidCsvContent();
        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'invalid-recipes.csv',
            'text/csv',
            null,
            true
        );

        $form = $crawler->selectButton('Importer les recettes')->form();
        $crawler = $this->client->submit($form, [
            'csv_file' => $uploadedFile
        ]);

        // Vérifier que l'import a échoué et que les erreurs sont affichées
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Erreur lors de l\'import');

        fclose($tempFile);
    }

    public function testImportWithEmptyFile(): void
    {
        $crawler = $this->client->request('GET', '/admin/recipe-import');
        
        $form = $crawler->selectButton('Importer les recettes')->form();
        $crawler = $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Veuillez sélectionner un fichier CSV');
    }

    public function testImportWithLargeFile(): void
    {
        $crawler = $this->client->request('GET', '/admin/recipe-import');
        
        // Créer un fichier de plus de 5MB
        $largeContent = str_repeat('x', 6 * 1024 * 1024);
        $tempFile = tmpfile();
        fwrite($tempFile, $largeContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'large.csv',
            'text/csv',
            null,
            true
        );

        $form = $crawler->selectButton('Importer les recettes')->form();
        $crawler = $this->client->submit($form, [
            'csv_file' => $uploadedFile
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Le fichier est trop volumineux');

        fclose($tempFile);
    }

    public function testImportWithWrongFileType(): void
    {
        $crawler = $this->client->request('GET', '/admin/recipe-import');
        
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

        $form = $crawler->selectButton('Importer les recettes')->form();
        $crawler = $this->client->submit($form, [
            'csv_file' => $uploadedFile
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Le fichier doit être au format CSV');

        fclose($tempFile);
    }

    private function getValidCsvContent(): string
    {
        return "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n" .
               "\"Pasta Carbonara\",\"Une délicieuse recette de pâtes à la carbonara traditionnelle\",4,15,20,\"carbonara.jpg\",\"Pâtes:400:g|Oeufs:4:pièce|Lardons:200:g|Parmesan:100:g|Poivre noir:1:pincée\",\"1. Faire cuire les pâtes dans l'eau bouillante salée|2. Faire revenir les lardons dans une poêle|3. Battre les oeufs avec le parmesan râpé|4. Mélanger les pâtes chaudes avec les lardons|5. Ajouter le mélange oeufs-parmesan hors du feu|6. Assaisonner avec le poivre noir\"\n" .
               "\"Salade César\",\"Salade fraîche et croquante avec une sauce césar maison\",2,10,0,\"cesar.jpg\",\"Laitue romaine:1:pièce|Croûtons:100:g|Parmesan:50:g|Anchois:4:pièce|Huile d'olive:50:ml|Citron:1:pièce\",\"1. Laver et couper la laitue en morceaux|2. Préparer la sauce césar avec les anchois et l'huile|3. Mélanger la laitue avec la sauce|4. Ajouter les croûtons et le parmesan|5. Arroser de jus de citron\"\n" .
               "\"Tarte aux pommes\",\"Une tarte aux pommes traditionnelle et savoureuse\",6,30,45,\"tarte-pommes.jpg\",\"Pâte brisée:1:pièce|Pommes:6:pièce|Sucre:100:g|Beurre:50:g|Cannelle:1:pincée\",\"1. Étaler la pâte dans un moule à tarte|2. Éplucher et couper les pommes en lamelles|3. Disposer les pommes sur la pâte|4. Saupoudrer de sucre et de cannelle|5. Ajouter des noisettes de beurre|6. Cuire au four à 180°C pendant 45 minutes\"";
    }

    private function getInvalidCsvContent(): string
    {
        return "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n" .
               "\"\",\"Description sans titre\",2,15,30,\"test.jpg\",\"Ingrédient:100:g\",\"1. Première étape\"\n" .
               "\"Recette avec ingrédient invalide\",\"Description test\",2,15,30,\"test.jpg\",\"Format invalide\",\"1. Première étape\"";
    }

    private function assertRecipeExists(string $title, string $description, int $servings, int $prepMinutes, int $cookMinutes): void
    {
        $recipe = $this->recipeRepository->findOneBy(['title' => $title]);
        $this->assertNotNull($recipe, "La recette '$title' n'a pas été trouvée");
        $this->assertEquals($description, $recipe->getDescription());
        $this->assertEquals($servings, $recipe->getServings());
        $this->assertEquals($prepMinutes, $recipe->getPrepMinutes());
        $this->assertEquals($cookMinutes, $recipe->getCookMinutes());
    }

    protected function tearDown(): void
    {
        // Nettoyer les données de test si nécessaire
        parent::tearDown();
    }
}
