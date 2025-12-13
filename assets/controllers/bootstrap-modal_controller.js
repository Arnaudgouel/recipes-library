import { Controller } from '@hotwired/stimulus';

/**
 * Gère les modals Bootstrap avec les événements Live Component
 */
export default class extends Controller {
    modal = null;

    connect() {
        // Attendre que Bootstrap soit chargé
        if (typeof bootstrap !== 'undefined') {
            this.modal = bootstrap.Modal.getOrCreateInstance(this.element);
        }
        
        document.addEventListener('modal:close', () => {
            if (this.modal) {
                this.modal.hide();
            }
        });
    }
}

