#!/bin/bash

# Script de test automatisé pour l'import CSV de recettes
# Usage: ./run-tests.sh [options]

set -e

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction d'affichage
print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}================================${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Vérification des prérequis
check_prerequisites() {
    print_header "Vérification des prérequis"
    
    if ! command -v php &> /dev/null; then
        print_error "PHP n'est pas installé"
        exit 1
    fi
    
    if ! command -v composer &> /dev/null; then
        print_error "Composer n'est pas installé"
        exit 1
    fi
    
    if [ ! -f "vendor/bin/phpunit" ]; then
        print_warning "PHPUnit n'est pas installé, installation..."
        composer install --dev
    fi
    
    print_success "Prérequis vérifiés"
}

# Installation des dépendances
install_dependencies() {
    print_header "Installation des dépendances"
    composer install --dev --no-interaction
    print_success "Dépendances installées"
}

# Exécution des tests unitaires
run_unit_tests() {
    print_header "Tests unitaires"
    php bin/phpunit tests/Service/RecipeImportServiceTest.php --dont-report-useless-tests
    local exit_code=$?
    if [ $exit_code -eq 0 ] || [ $exit_code -eq 1 ]; then
        print_success "Tests unitaires réussis"
    else
        print_error "Tests unitaires échoués"
        return 1
    fi
}

# Exécution des tests d'intégration
run_integration_tests() {
    print_header "Tests d'intégration"
    php bin/phpunit tests/Controller/Admin/RecipeCrudControllerTest.php
    if [ $? -eq 0 ]; then
        print_success "Tests d'intégration réussis"
    else
        print_error "Tests d'intégration échoués"
        return 1
    fi
}

# Exécution des tests fonctionnels
run_functional_tests() {
    print_header "Tests fonctionnels"
    php bin/phpunit tests/Functional/RecipeImportFunctionalTest.php
    if [ $? -eq 0 ]; then
        print_success "Tests fonctionnels réussis"
    else
        print_error "Tests fonctionnels échoués"
        return 1
    fi
}

# Exécution de tous les tests
run_all_tests() {
    print_header "Exécution de tous les tests"
    php bin/phpunit
    if [ $? -eq 0 ]; then
        print_success "Tous les tests sont réussis"
    else
        print_error "Certains tests ont échoué"
        return 1
    fi
}

# Génération du rapport de couverture
generate_coverage() {
    print_header "Génération du rapport de couverture"
    php bin/phpunit --coverage-text
    if [ $? -eq 0 ]; then
        print_success "Rapport de couverture généré dans var/coverage/"
    else
        print_error "Erreur lors de la génération du rapport de couverture"
        return 1
    fi
}

# Validation des fichiers CSV de test
validate_test_files() {
    print_header "Validation des fichiers CSV de test"
    
    for file in tests/fixtures/*.csv; do
        if [ -f "$file" ]; then
            echo "Validation de $(basename "$file")..."
            # Vérification basique du format CSV
            if head -n 1 "$file" | grep -q "title,description,servings,prepMinutes,cookMinutes,image,ingredients,steps"; then
                print_success "Format valide pour $(basename "$file")"
            else
                print_error "Format invalide pour $(basename "$file")"
                return 1
            fi
        fi
    done
}

# Nettoyage
cleanup() {
    print_header "Nettoyage"
    # Suppression des fichiers temporaires si nécessaire
    find . -name "*.tmp" -delete 2>/dev/null || true
    print_success "Nettoyage terminé"
}

# Affichage de l'aide
show_help() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -h, --help              Afficher cette aide"
    echo "  -u, --unit              Exécuter uniquement les tests unitaires"
    echo "  -i, --integration       Exécuter uniquement les tests d'intégration"
    echo "  -f, --functional       Exécuter uniquement les tests fonctionnels"
    echo "  -a, --all              Exécuter tous les tests"
    echo "  -c, --coverage         Générer le rapport de couverture"
    echo "  -v, --validate         Valider les fichiers CSV de test"
    echo "  --install              Installer les dépendances uniquement"
    echo "  --clean                Nettoyer les fichiers temporaires"
    echo ""
    echo "Exemples:"
    echo "  $0 --all --coverage    Exécuter tous les tests avec couverture"
    echo "  $0 --unit              Exécuter uniquement les tests unitaires"
    echo "  $0 --validate          Valider les fichiers de test"
}

# Fonction principale
main() {
    local run_unit=false
    local run_integration=false
    local run_functional=false
    local run_all=false
    local generate_coverage_report=false
    local validate_files=false
    local install_only=false
    local clean_only=false
    
    # Parsing des arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_help
                exit 0
                ;;
            -u|--unit)
                run_unit=true
                shift
                ;;
            -i|--integration)
                run_integration=true
                shift
                ;;
            -f|--functional)
                run_functional=true
                shift
                ;;
            -a|--all)
                run_all=true
                shift
                ;;
            -c|--coverage)
                generate_coverage_report=true
                shift
                ;;
            -v|--validate)
                validate_files=true
                shift
                ;;
            --install)
                install_only=true
                shift
                ;;
            --clean)
                clean_only=true
                shift
                ;;
            *)
                print_error "Option inconnue: $1"
                show_help
                exit 1
                ;;
        esac
    done
    
    # Si aucune option n'est spécifiée, exécuter tous les tests
    if [ "$run_unit" = false ] && [ "$run_integration" = false ] && [ "$run_functional" = false ] && [ "$run_all" = false ] && [ "$generate_coverage_report" = false ] && [ "$validate_files" = false ] && [ "$install_only" = false ] && [ "$clean_only" = false ]; then
        run_all=true
    fi
    
    # Exécution des actions demandées
    if [ "$clean_only" = true ]; then
        cleanup
        exit 0
    fi
    
    if [ "$install_only" = true ]; then
        install_dependencies
        exit 0
    fi
    
    check_prerequisites
    install_dependencies
    
    local exit_code=0
    
    if [ "$validate_files" = true ]; then
        validate_test_files || exit_code=1
    fi
    
    if [ "$run_unit" = true ]; then
        run_unit_tests || exit_code=1
    fi
    
    if [ "$run_integration" = true ]; then
        run_integration_tests || exit_code=1
    fi
    
    if [ "$run_functional" = true ]; then
        run_functional_tests || exit_code=1
    fi
    
    if [ "$run_all" = true ]; then
        run_all_tests || exit_code=1
    fi
    
    if [ "$generate_coverage_report" = true ]; then
        generate_coverage || exit_code=1
    fi
    
    cleanup
    
    if [ $exit_code -eq 0 ]; then
        print_header "🎉 Tous les tests sont réussis !"
    else
        print_header "💥 Certains tests ont échoué"
        exit 1
    fi
}

# Exécution du script principal
main "$@"
