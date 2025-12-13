import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {

    static targets = ['autocompleteField'];

    async initialize() {
        this.component = await getComponent(this.element);
        window.addEventListener('clearFilters', () => {
            this.clearFilters()
        });
        this.autocompleteFieldTargets.forEach(target => {
            let tomselect = target.tomselect;
            if (tomselect) {
                tomselect.on('change', () => {
                    tomselect.sync();
                    let input = document.querySelector(`input[data-filter-value-name="${target.dataset.filterName}"]`);
                    if(input) {
                        input.value = tomselect.getValue();
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if(tomselect.getValue().length === 0) {
                        this.component.emitSelf('clearONEFILTER', {
                            filterName: target.dataset.filterName,
                        });
                    }
                });
            }
        });
    }

    clearFilters() {
        this.autocompleteFieldTargets.forEach(target => {
            let tomselect = target.tomselect;
            if (tomselect) {
                tomselect.clear();
            }
        });
    }
}

