<?php

namespace App\Service;

use App\Entity\CategoryRecipe;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvImportService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function importIngredients(UploadedFile $file): int
    {
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 0, ';');
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_combine($header, $row);
            
            $ingredient = new Ingredient();
            $ingredient->setName($data['name'] ?? $data['nom'] ?? '');
            
            $this->em->persist($ingredient);
            $count++;
        }

        fclose($handle);
        $this->em->flush();

        return $count;
    }

    public function importCategories(UploadedFile $file): int
    {
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 0, ';');
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_combine($header, $row);
            
            $category = new CategoryRecipe();
            $category->setName($data['name'] ?? $data['nom'] ?? '');
            
            $this->em->persist($category);
            $count++;
        }

        fclose($handle);
        $this->em->flush();

        return $count;
    }

    public function importUnits(UploadedFile $file): int
    {
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 0, ';');
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_combine($header, $row);
            
            $unit = new Unit();
            $unit->setCode($data['code'] ?? '');
            $unit->setLabel($data['label'] ?? $data['libelle'] ?? '');
            $unit->setPluralLabel($data['pluralLabel'] ?? $data['libelle_pluriel'] ?? null);
            $unit->setKind($data['kind'] ?? $data['type'] ?? '');
            
            $this->em->persist($unit);
            $count++;
        }

        fclose($handle);
        $this->em->flush();

        return $count;
    }

    public function importRecipes(UploadedFile $file): int
    {
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 0, ';');
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_combine($header, $row);
            
            $recipe = new Recipe();
            $recipe->setTitle($data['title'] ?? $data['titre'] ?? '');
            $recipe->setDescription($data['description'] ?? null);
            $recipe->setServings(isset($data['servings']) ? (int)$data['servings'] : null);
            $recipe->setPrepMinutes(isset($data['prepMinutes']) ? (int)$data['prepMinutes'] : null);
            $recipe->setCookMinutes(isset($data['cookMinutes']) ? (int)$data['cookMinutes'] : null);
            $recipe->setImage($data['image'] ?? null);
            
            $this->em->persist($recipe);
            $count++;
        }

        fclose($handle);
        $this->em->flush();

        return $count;
    }
}

