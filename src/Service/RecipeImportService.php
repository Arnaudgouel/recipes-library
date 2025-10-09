<?php

namespace App\Service;

use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\RecipeStep;
use App\Entity\Ingredient;
use App\Entity\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RecipeImportService
{
    private EntityManagerInterface $entityManager;
    private array $errors = [];
    private int $importedCount = 0;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function importFromCsv(UploadedFile $file): array
    {
        $this->errors = [];
        $this->importedCount = 0;

        // Validation du fichier
        if (!$this->validateFile($file)) {
            return ['success' => false, 'errors' => $this->errors];
        }

        // Lecture du CSV
        $data = $this->readCsvFile($file);
        if (!$data) {
            return ['success' => false, 'errors' => $this->errors];
        }

        // Validation du format
        if (!$this->validateCsvFormat($data)) {
            return ['success' => false, 'errors' => $this->errors];
        }

        // Import des recettes
        $this->importRecipes($data);

        return [
            'success' => empty($this->errors),
            'imported' => $this->importedCount,
            'errors' => $this->errors
        ];
    }

    private function validateFile(UploadedFile $file): bool
    {
        if ($file->getClientMimeType() !== 'text/csv' && $file->getClientOriginalExtension() !== 'csv') {
            $this->errors[] = 'Le fichier doit être au format CSV.';
            return false;
        }

        if ($file->getSize() > 5 * 1024 * 1024) { // 5MB max
            $this->errors[] = 'Le fichier est trop volumineux (maximum 5MB).';
            return false;
        }

        return true;
    }

    private function readCsvFile(UploadedFile $file): ?array
    {
        $handle = fopen($file->getPathname(), 'r');
        if (!$handle) {
            $this->errors[] = 'Impossible de lire le fichier CSV.';
            return null;
        }

        $data = [];
        $header = fgetcsv($handle, 0, ',');
        
        if (!$header) {
            $this->errors[] = 'Le fichier CSV est vide ou corrompu.';
            fclose($handle);
            return null;
        }

        $expectedColumns = ['title', 'description', 'servings', 'prepMinutes', 'cookMinutes', 'image', 'ingredients', 'steps'];
        if ($header !== $expectedColumns) {
            $this->errors[] = 'Format CSV invalide. Colonnes attendues: ' . implode(', ', $expectedColumns);
            fclose($handle);
            return null;
        }

        $lineNumber = 1;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;
            if (count($row) !== count($header)) {
                $this->errors[] = "Ligne $lineNumber: Nombre de colonnes incorrect.";
                continue;
            }
            $data[] = array_combine($header, $row);
        }

        fclose($handle);
        return $data;
    }

    private function validateCsvFormat(array $data): bool
    {
        if (empty($data)) {
            $this->errors[] = 'Aucune donnée à importer.';
            return false;
        }

        foreach ($data as $index => $row) {
            $lineNumber = $index + 2; // +2 car on commence à la ligne 2 (après l'en-tête)
            
            // Validation du titre (obligatoire)
            if (empty(trim($row['title']))) {
                $this->errors[] = "Ligne $lineNumber: Le titre est obligatoire.";
            }

            // Validation des ingrédients
            if (!empty($row['ingredients'])) {
                $ingredients = explode('|', $row['ingredients']);
                foreach ($ingredients as $ingredient) {
                    $parts = explode(':', $ingredient);
                    if (count($parts) !== 3) {
                        $this->errors[] = "Ligne $lineNumber: Format d'ingrédient invalide: $ingredient";
                    }
                }
            }
        }

        return empty($this->errors);
    }

    private function importRecipes(array $data): void
    {
        foreach ($data as $row) {
            try {
                $this->importRecipe($row);
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->errors[] = 'Erreur lors de l\'import: ' . $e->getMessage();
            }
        }
    }

    private function importRecipe(array $row): void
    {
        // Créer la recette
        $recipe = new Recipe();
        $recipe->setTitle(trim($row['title']));
        $recipe->setDescription(!empty($row['description']) ? trim($row['description']) : null);
        $recipe->setServings(!empty($row['servings']) ? (int)$row['servings'] : null);
        $recipe->setPrepMinutes(!empty($row['prepMinutes']) ? (int)$row['prepMinutes'] : null);
        $recipe->setCookMinutes(!empty($row['cookMinutes']) ? (int)$row['cookMinutes'] : null);
        $recipe->setImage(!empty($row['image']) ? trim($row['image']) : null);

        $this->entityManager->persist($recipe);

        // Importer les ingrédients
        if (!empty($row['ingredients'])) {
            $this->importIngredients($recipe, $row['ingredients']);
        }

        // Importer les étapes
        if (!empty($row['steps'])) {
            $this->importSteps($recipe, $row['steps']);
        }

        $this->entityManager->flush();
    }

    private function importIngredients(Recipe $recipe, string $ingredientsString): void
    {
        $ingredients = explode('|', $ingredientsString);
        $position = 1;

        foreach ($ingredients as $ingredientString) {
            $parts = explode(':', $ingredientString);
            if (count($parts) !== 3) {
                continue;
            }

            $ingredientName = trim($parts[0]);
            $quantity = trim($parts[1]);
            $unitCode = trim($parts[2]);

            // Créer ou récupérer l'ingrédient
            $ingredient = $this->findOrCreateIngredient($ingredientName);

            // Récupérer l'unité
            $unit = $this->entityManager->getRepository(Unit::class)->find($unitCode);

            // Créer l'ingrédient de recette
            $recipeIngredient = new RecipeIngredient();
            $recipeIngredient->setRecipe($recipe);
            $recipeIngredient->setIngredient($ingredient);
            $recipeIngredient->setPosition($position);
            $recipeIngredient->setQuantity($quantity);
            $recipeIngredient->setUnit($unit);

            $this->entityManager->persist($recipeIngredient);
            $position++;
        }
    }

    private function importSteps(Recipe $recipe, string $stepsString): void
    {
        $steps = explode('|', $stepsString);
        $position = 1;

        foreach ($steps as $stepInstruction) {
            $instruction = trim($stepInstruction);
            if (empty($instruction)) {
                continue;
            }

            $recipeStep = new RecipeStep();
            $recipeStep->setRecipe($recipe);
            $recipeStep->setPosition($position);
            $recipeStep->setInstruction($instruction);

            $this->entityManager->persist($recipeStep);
            $position++;
        }
    }

    private function findOrCreateIngredient(string $name): Ingredient
    {
        $ingredient = $this->entityManager->getRepository(Ingredient::class)
            ->findOneBy(['name' => $name]);

        if (!$ingredient) {
            $ingredient = new Ingredient();
            $ingredient->setName($name);
            $this->entityManager->persist($ingredient);
        }

        return $ingredient;
    }
}
