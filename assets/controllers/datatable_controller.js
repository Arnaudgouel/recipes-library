import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import * as bootstrap from 'bootstrap';

export default class extends Controller {

    static targets = ['autocompleteField'];

    async connect() {
        this.component = await getComponent(this.element);
        
        // Écouter l'événement clearFilters
        this.clearFiltersHandler = () => this.clearFilters();
        window.addEventListener('clearFilters', this.clearFiltersHandler);
        
        // Écouter quand le composant est rendu pour réinitialiser
        this.element.addEventListener('live:connect', () => {
            this.initializeBootstrapComponents();
            this.initializeAutocompleteFields();
        });
        
        // Initialiser au premier chargement
        this.initializeBootstrapComponents();
        this.initializeAutocompleteFields();
    }

    /**
     * Initialise les composants Bootstrap (Collapse, Dropdowns, etc.)
     */
    initializeBootstrapComponents() {
        // Initialiser les Collapses
        this.element.querySelectorAll('[data-bs-toggle="collapse"]').forEach(el => {
            if (!bootstrap.Collapse.getInstance(el)) {
                new bootstrap.Collapse(el, { toggle: false });
            }
        });
        
        // Initialiser les Dropdowns
        this.element.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => {
            if (!bootstrap.Dropdown.getInstance(el)) {
                new bootstrap.Dropdown(el);
            }
        });
        
        // Initialiser les Tooltips
        this.element.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            if (!bootstrap.Tooltip.getInstance(el)) {
                new bootstrap.Tooltip(el);
            }
        });
    }

    disconnect() {
        window.removeEventListener('clearFilters', this.clearFiltersHandler);
    }

    /**
     * Appelé automatiquement quand un nouveau target autocompleteField est ajouté au DOM
     */
    autocompleteFieldTargetConnected(target) {
        this.setupAutocompleteField(target);
    }

    /**
     * Initialise tous les champs autocomplete
     */
    initializeAutocompleteFields() {
        // Attendre un peu que TomSelect soit initialisé
        setTimeout(() => {
            this.autocompleteFieldTargets.forEach(target => {
                this.setupAutocompleteField(target);
            });
        }, 100);
    }

    /**
     * Configure un champ autocomplete individuel
     */
    setupAutocompleteField(target) {
        // Éviter les doubles initialisations
        if (target.dataset.datatableInitialized) {
            return;
        }
        
        const checkAndSetup = () => {
            const tomselect = target.tomselect;
            if (tomselect) {
                target.dataset.datatableInitialized = 'true';
                
                // Supprimer l'ancien listener si présent
                if (target._datatableChangeHandler) {
                    tomselect.off('change', target._datatableChangeHandler);
                }
                
                // Créer le nouveau handler
                target._datatableChangeHandler = () => {
                    tomselect.sync();
                    const input = document.querySelector(`input[data-filter-value-name="${target.dataset.filterName}"]`);
                    if (input) {
                        input.value = tomselect.getValue();
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (tomselect.getValue().length === 0) {
                        this.component.emitSelf('clearONEFILTER', {
                            filterName: target.dataset.filterName,
                        });
                    }
                };
                
                tomselect.on('change', target._datatableChangeHandler);
            } else {
                // TomSelect pas encore prêt, réessayer
                setTimeout(checkAndSetup, 50);
            }
        };
        
        checkAndSetup();
    }

    clearFilters() {
        this.autocompleteFieldTargets.forEach(target => {
            const tomselect = target.tomselect;
            if (tomselect) {
                tomselect.clear();
            }
        });
    }
}
