<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\RecipeStep;
use App\Entity\Unit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DataFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        echo "DataFixtures starting...\n";
        
        // Créer des unités
        $units = $this->createUnits($manager);
        echo "Units created: " . count($units) . "\n";
        
        // Créer des ingrédients
        $ingredients = $this->createIngredients($manager);
        echo "Ingredients created: " . count($ingredients) . "\n";

        // Créer des recettes avec leurs ingrédients et étapes
        $this->createRecipes($manager, $ingredients, $units);
        echo "Recipes created\n";
        
        $manager->flush();
        echo "DataFixtures completed successfully!\n";
    }

    private function createUnits(ObjectManager $manager): array
    {
        $unitsData = [
            ['code' => 'g', 'label' => 'Gramme', 'kind' => 'weight'],
            ['code' => 'kg', 'label' => 'Kilogramme', 'kind' => 'weight'],
            ['code' => 'ml', 'label' => 'Millilitre', 'kind' => 'volume'],
            ['code' => 'cl', 'label' => 'Centilitre', 'kind' => 'volume'],
            ['code' => 'l', 'label' => 'Litre', 'kind' => 'volume'],
            ['code' => 'cs', 'label' => 'Cuillère à soupe', 'kind' => 'volume'],
            ['code' => 'cc', 'label' => 'Cuillère à café', 'kind' => 'volume'],
            ['code' => 'pcs', 'label' => 'Pièce', 'kind' => 'count'],
            ['code' => 'pincée', 'label' => 'Pincée', 'kind' => 'quantity'],
        ];

        $units = [];
        foreach ($unitsData as $unitData) {
            $unit = new Unit();
            $unit->setCode($unitData['code'])
                 ->setLabel($unitData['label'])
                 ->setKind($unitData['kind']);
            
            $manager->persist($unit);
            $units[$unitData['code']] = $unit;
        }

        return $units;
    }

    private function createIngredients(ObjectManager $manager): array
    {
        $ingredientsData = [
            'Farine', 'Sucre', 'Œufs', 'Beurre', 'Lait', 'Sel', 'Poivre',
            'Tomates', 'Oignons', 'Ail', 'Basilic', 'Origan', 'Thym',
            'Pommes de terre', 'Carottes', 'Courgettes', 'Aubergines',
            'Poulet', 'Bœuf', 'Porc', 'Saumon', 'Crevettes',
            'Fromage râpé', 'Crème fraîche', 'Huile d\'olive',
            'Vinaigre balsamique', 'Moutarde', 'Miel', 'Citron'
        ];

        $ingredients = [];
        foreach ($ingredientsData as $ingredientName) {
            $ingredient = new Ingredient();
            $ingredient->setName($ingredientName);
            
            $manager->persist($ingredient);
            $ingredients[$ingredientName] = $ingredient;
        }

        return $ingredients;
    }

    private function createRecipes(ObjectManager $manager, array $ingredients, array $units): void
    {
        $recipesData = [
            [
                'title' => 'Spaghetti Carbonara',
                'description' => 'Un classique de la cuisine italienne, crémeux et savoureux.',
                'servings' => 4,
                'prepMinutes' => 15,
                'cookMinutes' => 20,
                'ingredients' => [
                    ['name' => 'Spaghetti', 'quantity' => 400, 'unit' => 'g'],
                    ['name' => 'Œufs', 'quantity' => 4, 'unit' => 'pcs'],
                    ['name' => 'Fromage râpé', 'quantity' => 100, 'unit' => 'g'],
                    ['name' => 'Lardons', 'quantity' => 200, 'unit' => 'g'],
                    ['name' => 'Poivre', 'quantity' => 1, 'unit' => 'pincée'],
                ],
                'steps' => [
                    ['position' => 1, 'instruction' => 'Faire cuire les spaghetti dans l\'eau bouillante salée selon les instructions du paquet.', 'durationMin' => 10],
                    ['position' => 2, 'instruction' => 'Pendant ce temps, faire revenir les lardons dans une poêle.', 'durationMin' => 5],
                    ['position' => 3, 'instruction' => 'Battre les œufs avec le fromage râpé et le poivre.', 'durationMin' => 2],
                    ['position' => 4, 'instruction' => 'Égoutter les pâtes et les mélanger avec les lardons.', 'durationMin' => 1],
                    ['position' => 5, 'instruction' => 'Ajouter le mélange œufs-fromage et mélanger rapidement.', 'durationMin' => 2],
                ]
            ],
            [
                'title' => 'Salade César',
                'description' => 'Une salade fraîche et croquante avec une sauce crémeuse.',
                'servings' => 2,
                'prepMinutes' => 20,
                'cookMinutes' => 10,
                'ingredients' => [
                    ['name' => 'Salade romaine', 'quantity' => 1, 'unit' => 'pcs'],
                    ['name' => 'Poulet', 'quantity' => 200, 'unit' => 'g'],
                    ['name' => 'Pain de mie', 'quantity' => 2, 'unit' => 'pcs'],
                    ['name' => 'Fromage râpé', 'quantity' => 50, 'unit' => 'g'],
                    ['name' => 'Ail', 'quantity' => 2, 'unit' => 'pcs'],
                    ['name' => 'Huile d\'olive', 'quantity' => 3, 'unit' => 'cs'],
                ],
                'steps' => [
                    ['position' => 1, 'instruction' => 'Laver et couper la salade en morceaux.', 'durationMin' => 5],
                    ['position' => 2, 'instruction' => 'Couper le poulet en dés et le faire cuire.', 'durationMin' => 8],
                    ['position' => 3, 'instruction' => 'Préparer les croûtons avec le pain de mie.', 'durationMin' => 5],
                    ['position' => 4, 'instruction' => 'Mélanger tous les ingrédients dans un saladier.', 'durationMin' => 2],
                ]
            ]
        ];

        foreach ($recipesData as $recipeData) {
            $recipe = new Recipe();
            $recipe->setTitle($recipeData['title'])
                   ->setDescription($recipeData['description'])
                   ->setServings($recipeData['servings'])
                   ->setPrepMinutes($recipeData['prepMinutes'])
                   ->setCookMinutes($recipeData['cookMinutes']);

            $manager->persist($recipe);

            // Ajouter les ingrédients
            foreach ($recipeData['ingredients'] as $index => $ingredientData) {
                $recipeIngredient = new RecipeIngredient();
                $recipeIngredient->setRecipe($recipe)
                                ->setPosition($index + 1)
                                ->setQuantity((string)$ingredientData['quantity'])
                                ->setUnit($units[$ingredientData['unit']]);
                
                // Trouver l'ingrédient par nom ou créer un nouveau
                $ingredientName = $ingredientData['name'];
                if (isset($ingredients[$ingredientName])) {
                    $recipeIngredient->setIngredient($ingredients[$ingredientName]);
                } else {
                    $newIngredient = new Ingredient();
                    $newIngredient->setName($ingredientName);
                    $manager->persist($newIngredient);
                    $ingredients[$ingredientName] = $newIngredient;
                    $recipeIngredient->setIngredient($newIngredient);
                }
                
                $manager->persist($recipeIngredient);
            }

            // Ajouter les étapes
            foreach ($recipeData['steps'] as $stepData) {
                $step = new RecipeStep();
                $step->setRecipe($recipe)
                     ->setPosition($stepData['position'])
                     ->setInstruction($stepData['instruction'])
                     ->setDurationMin($stepData['durationMin']);
                
                $manager->persist($step);
            }
        }
    }
}
