import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["ingredientsSelect"]

     initialize() {
        window.addEventListener('filters:reset', () => {
            this.ingredientsSelectTargets.forEach(select => {
                select.tomselect.clear()
            });
        });

        window.addEventListener('page:changed', (event) => {
            this.element.scrollIntoView({ behavior: 'smooth' });
        })
    };
    
}
