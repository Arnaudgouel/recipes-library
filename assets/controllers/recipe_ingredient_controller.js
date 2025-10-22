import { Controller } from "@hotwired/stimulus"

export default class extends Controller {

    handleToggle(event) {
        let checkbox = event.currentTarget;
        if (checkbox.checked) {
            this.element.classList.add('checked');
        } else {
            this.element.classList.remove('checked');
        }
    }
    
}
