# Tests pour l'import CSV de recettes

## Vue d'ensemble

Cette documentation décrit la suite de tests complète pour la fonctionnalité d'import CSV de recettes dans l'application Recipes Library.

## Structure des tests

### 1. Tests unitaires (`tests/Service/RecipeImportServiceTest.php`)

Ces tests vérifient le comportement isolé du service `RecipeImportService` :

#### Cas de test couverts :
- ✅ **Import réussi** : Fichier CSV valide avec une recette
- ✅ **Type de fichier invalide** : Fichier non-CSV
- ✅ **Fichier trop volumineux** : Fichier > 5MB
- ✅ **Format CSV invalide** : En-têtes incorrects
- ✅ **Titre manquant** : Recette sans titre
- ✅ **Format d'ingrédient invalide** : Ingrédient mal formaté
- ✅ **Fichier vide** : Fichier CSV vide
- ✅ **Import multiple** : Plusieurs recettes dans un fichier

#### Méthodes testées :
- `importFromCsv()` : Méthode principale d'import
- `validateFile()` : Validation du fichier
- `readCsvFile()` : Lecture du fichier CSV
- `validateCsvFormat()` : Validation du format
- `importRecipes()` : Import des recettes

### 2. Tests d'intégration (`tests/Controller/Admin/RecipeCrudControllerTest.php`)

Ces tests vérifient l'intégration entre le contrôleur et le service :

#### Cas de test couverts :
- ✅ **Accès à la page d'import** : Vérification de l'affichage
- ✅ **Import avec fichier valide** : Test complet du workflow
- ✅ **Import sans fichier** : Gestion de l'erreur
- ✅ **Import avec type de fichier invalide** : Validation côté contrôleur
- ✅ **Import avec format CSV invalide** : Gestion des erreurs
- ✅ **Import avec titre manquant** : Validation des données
- ✅ **Import multiple** : Plusieurs recettes

#### Vérifications effectuées :
- Redirection après succès
- Messages flash appropriés
- Création des entités en base de données
- Validation des données importées

### 3. Tests fonctionnels (`tests/Functional/RecipeImportFunctionalTest.php`)

Ces tests simulent le comportement complet de l'application :

#### Cas de test couverts :
- ✅ **Workflow complet d'import** : De la page à la base de données
- ✅ **Import avec erreurs de validation** : Gestion des erreurs
- ✅ **Import avec fichier vide** : Validation côté formulaire
- ✅ **Import avec fichier volumineux** : Limite de taille
- ✅ **Import avec mauvais type de fichier** : Validation MIME

#### Vérifications effectuées :
- Navigation dans l'interface
- Soumission de formulaires
- Messages d'erreur et de succès
- Intégrité des données

### 4. Fichiers CSV de test (`tests/fixtures/`)

#### `valid-recipes.csv`
Fichier CSV contenant des recettes valides pour tester les cas de succès :
- Pasta Carbonara (recette complète)
- Salade César (recette avec temps de cuisson à 0)
- Tarte aux pommes (recette avec plusieurs ingrédients et étapes)

#### `invalid-recipes.csv`
Fichier CSV contenant des données invalides pour tester la validation :
- Recette sans titre
- Format d'ingrédient invalide
- Colonnes manquantes
- Colonnes en trop

#### `edge-cases-recipes.csv`
Fichier CSV contenant des cas limites :
- Recette minimale (données optionnelles vides)
- Recette complète
- Caractères spéciaux et accents
- Guillemets dans les données

## Format CSV attendu

### Structure des colonnes :
```csv
title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps
```

### Format des ingrédients :
```
nom:quantité:unité
```
Exemple : `"Pâtes:400:g|Œufs:4:pièce|Lardons:200:g"`

### Format des étapes :
```
1. Première étape|2. Deuxième étape|3. Troisième étape
```

## Exécution des tests

### Installation des dépendances
```bash
composer install --dev
```

### Exécution de tous les tests
```bash
php bin/phpunit
```

### Exécution par catégorie
```bash
# Tests unitaires uniquement
php bin/phpunit --testsuite=unit

# Tests d'intégration uniquement
php bin/phpunit --testsuite=integration

# Tests fonctionnels uniquement
php bin/phpunit --testsuite=functional

# Tests du service d'import uniquement
php bin/phpunit tests/Service/RecipeImportServiceTest.php
```

### Exécution avec couverture de code
```bash
php bin/phpunit --coverage-html var/coverage
```

## Bonnes pratiques

### 1. Isolation des tests
- Chaque test est indépendant
- Utilisation de mocks pour les dépendances
- Nettoyage des données après chaque test

### 2. Données de test
- Utilisation de fichiers CSV temporaires
- Données réalistes mais simplifiées
- Couverture des cas limites

### 3. Assertions
- Vérification des résultats attendus
- Vérification des messages d'erreur
- Vérification de l'état de la base de données

### 4. Maintenance
- Tests maintenus à jour avec le code
- Ajout de nouveaux tests pour les nouvelles fonctionnalités
- Documentation des cas de test complexes

## Cas d'erreur gérés

### Validation du fichier
- Type MIME incorrect
- Extension de fichier incorrecte
- Taille de fichier > 5MB
- Fichier vide ou corrompu

### Validation du format CSV
- En-têtes manquants ou incorrects
- Nombre de colonnes incorrect
- Titre de recette manquant
- Format d'ingrédient invalide

### Validation des données
- Types de données incorrects
- Valeurs négatives pour les durées
- Caractères spéciaux non gérés

## Améliorations futures

### Tests de performance
- Import de fichiers volumineux
- Temps d'exécution des imports
- Utilisation mémoire

### Tests de sécurité
- Injection de code malveillant
- Validation des uploads
- Permissions de fichiers

### Tests de compatibilité
- Différents encodages CSV
- Séparateurs différents
- Formats de ligne variés

## Dépannage

### Problèmes courants
1. **Tests qui échouent** : Vérifier la configuration de la base de données de test
2. **Fichiers temporaires** : S'assurer que les fichiers sont fermés correctement
3. **Mocks** : Vérifier que les mocks correspondent aux interfaces réelles

### Logs de débogage
```bash
# Exécution avec verbosité
php bin/phpunit --debug

# Exécution d'un test spécifique
php bin/phpunit tests/Service/RecipeImportServiceTest.php::testImportFromCsvWithValidFile
```
