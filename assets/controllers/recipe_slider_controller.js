import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['container', 'item', 'prevButton', 'nextButton', 'controls'];
    static values = {
        itemsPerView: { type: Number, default: 3 },
        gap: { type: Number, default: 16 }
    };

    connect() {
        this.currentIndex = 0;
        this.updateLayout();
        
        // Réagir aux changements de taille d'écran
        this.resizeObserver = new ResizeObserver(() => {
            this.updateLayout();
        });
        
        if (this.hasContainerTarget) {
            this.resizeObserver.observe(this.containerTarget);
        }
    }

    disconnect() {
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
    }

    updateLayout() {
        if (!this.hasContainerTarget) return;
        
        const items = this.itemTargets;
        
        if (items.length === 0) {
            this.updateButtons();
            return;
        }
        
        // Ajuster itemsPerView selon la taille d'écran
        let visibleItems = this.itemsPerViewValue;
        if (window.innerWidth < 768) {
            visibleItems = 1;
        } else if (window.innerWidth < 992) {
            visibleItems = 2;
        } else {
            visibleItems = this.itemsPerViewValue;
        }
        
        this.visibleItems = Math.min(visibleItems, items.length);
        this.maxIndex = Math.max(0, items.length - this.visibleItems);
        
        // S'assurer que currentIndex ne dépasse pas maxIndex
        if (this.currentIndex > this.maxIndex) {
            this.currentIndex = this.maxIndex;
        }
        
        this.updateButtons();
    }

    scrollToIndex() {
        if (!this.hasContainerTarget || !this.hasItemTarget) return;
        
        const container = this.containerTarget;
        const items = this.itemTargets;
        
        if (items.length === 0 || this.currentIndex >= items.length) return;
        
        // Utiliser l'élément actuel pour calculer la position de scroll
        const currentItem = items[this.currentIndex];
        if (currentItem) {
            const containerRect = container.getBoundingClientRect();
            const itemRect = currentItem.getBoundingClientRect();
            const scrollLeft = container.scrollLeft;
            const itemLeft = itemRect.left - containerRect.left + scrollLeft;
            
            container.scrollTo({
                left: itemLeft,
                behavior: 'smooth'
            });
        }
    }

    next() {
        if (this.currentIndex < this.maxIndex) {
            this.currentIndex++;
            this.scrollToIndex();
            this.updateButtons();
        }
    }

    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.scrollToIndex();
            this.updateButtons();
        }
    }

    updateButtons() {
        // Masquer les contrôles si tous les éléments sont visibles
        if (this.hasControlsTarget) {
            const shouldShowControls = this.maxIndex > 0;
            this.controlsTarget.style.display = shouldShowControls ? 'flex' : 'none';
        }
        
        if (this.hasPrevButtonTarget) {
            this.prevButtonTarget.disabled = this.currentIndex === 0;
            this.prevButtonTarget.classList.toggle('disabled', this.currentIndex === 0);
        }
        
        if (this.hasNextButtonTarget) {
            this.nextButtonTarget.disabled = this.currentIndex >= this.maxIndex;
            this.nextButtonTarget.classList.toggle('disabled', this.currentIndex >= this.maxIndex);
        }
    }
}

