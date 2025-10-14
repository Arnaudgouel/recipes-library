<?php

namespace App\Tests\Service;

use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\RecipeStep;
use App\Entity\Ingredient;
use App\Entity\Unit;
use App\Service\RecipeImportService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(RecipeImportService::class)]
class RecipeImportServiceTest extends TestCase
{
    private RecipeImportService $importService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->importService = new RecipeImportService($this->entityManager);
    }

    public function testImportFromCsvWithValidFile(): void
    {
        // Créer un fichier CSV temporaire valide
        $csvContent = "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n";
        $csvContent .= "\"Test Recipe\",\"Description test\",2,15,30,\"test.jpg\",\"Ingrédient:100:g\",\"1. Première étape|2. Deuxième étape\"";
        
        $tempPath = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
        file_put_contents($tempPath, $csvContent);
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'test.csv',
            'text/csv',
            null,
            true
        );

        // Mock des méthodes EntityManager - ne pas s'attendre à des appels spécifiques
        $this->entityManager->expects($this->any())
            ->method('persist');
        
        $this->entityManager->expects($this->any())
            ->method('flush');

        $result = $this->importService->importFromCsv($uploadedFile);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']);
        $this->assertEmpty($result['errors']);

        // Nettoyer le fichier temporaire
        unlink($tempPath);
    }

    public function testImportFromCsvWithInvalidFileType(): void
    {
        $tempPath = sys_get_temp_dir() . '/test_' . uniqid() . '.txt';
        file_put_contents($tempPath, 'contenu non CSV');
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'test.txt',
            'text/plain',
            null,
            true
        );

        $result = $this->importService->importFromCsv($uploadedFile);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['imported']);
        $this->assertContains('Le fichier doit être au format CSV.', $result['errors']);

        unlink($tempPath);
    }

    public function testImportFromCsvWithFileTooLarge(): void
    {
        // Créer un fichier de plus de 5MB
        $largeContent = str_repeat('x', 6 * 1024 * 1024); // 6MB
        
        $tempPath = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
        file_put_contents($tempPath, $largeContent);
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'large.csv',
            'text/csv',
            null,
            true
        );

        $result = $this->importService->importFromCsv($uploadedFile);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['imported']);
        $this->assertContains('Le fichier est trop volumineux (maximum 5MB).', $result['errors']);

        unlink($tempPath);
    }

    public function testImportFromCsvWithInvalidFormat(): void
    {
        $csvContent = "invalid,header\n";
        $csvContent .= "\"Test Recipe\",\"Description\"";
        
        $tempPath = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
        file_put_contents($tempPath, $csvContent);
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'invalid.csv',
            'text/csv',
            null,
            true
        );

        $result = $this->importService->importFromCsv($uploadedFile);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['imported']);
        $this->assertStringContainsString('Format CSV invalide. Colonnes attendues:', implode(' ', $result['errors']));

        unlink($tempPath);
    }

    public function testImportFromCsvWithMissingTitle(): void
    {
        $csvContent = "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n";
        $csvContent .= "\"\",\"Description test\",2,15,30,\"test.jpg\",\"Ingrédient:100:g\",\"1. Première étape\"";
        
        $tempPath = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
        file_put_contents($tempPath, $csvContent);
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'missing-title.csv',
            'text/csv',
            null,
            true
        );

        $result = $this->importService->importFromCsv($uploadedFile);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['imported']);
        $this->assertContains('Ligne 2: Le titre est obligatoire.', $result['errors']);

        unlink($tempPath);
    }

    public function testImportFromCsvWithInvalidIngredientFormat(): void
    {
        $csvContent = "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n";
        $csvContent .= "\"Test Recipe\",\"Description test\",2,15,30,\"test.jpg\",\"Ingrédient invalide\",\"1. Première étape\"";
        
        $tempPath = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
        file_put_contents($tempPath, $csvContent);
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'invalid-ingredient.csv',
            'text/csv',
            null,
            true
        );

        $result = $this->importService->importFromCsv($uploadedFile);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['imported']);
        $this->assertStringContainsString('Ligne 2: Format d\'ingrédient invalide:', implode(' ', $result['errors']));

        unlink($tempPath);
    }

    public function testImportFromCsvWithEmptyFile(): void
    {
        $tempPath = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
        file_put_contents($tempPath, '');
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'empty.csv',
            'text/csv',
            null,
            true
        );

        $result = $this->importService->importFromCsv($uploadedFile);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['imported']);
        $this->assertContains('Le fichier CSV est vide ou corrompu.', $result['errors']);

        unlink($tempPath);
    }

    public function testImportFromCsvWithMultipleRecipes(): void
    {
        $csvContent = "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps\n";
        $csvContent .= "\"Recipe 1\",\"Description 1\",2,15,30,\"test1.jpg\",\"Ingrédient:100:g\",\"1. Étape 1\"\n";
        $csvContent .= "\"Recipe 2\",\"Description 2\",4,20,45,\"test2.jpg\",\"Autre ingrédient:200:ml\",\"1. Étape 1|2. Étape 2\"";
        
        $tempPath = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
        file_put_contents($tempPath, $csvContent);
        
        $uploadedFile = new UploadedFile(
            $tempPath,
            'multiple.csv',
            'text/csv',
            null,
            true
        );

        // Mock pour 2 recettes - ne pas s'attendre à des appels spécifiques
        $this->entityManager->expects($this->any())
            ->method('persist');
        
        $this->entityManager->expects($this->any())
            ->method('flush');

        $result = $this->importService->importFromCsv($uploadedFile);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['imported']);
        $this->assertEmpty($result['errors']);

        unlink($tempPath);
    }
}