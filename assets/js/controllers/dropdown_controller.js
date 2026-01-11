/**
 * Dropdown Controller
 * Gère les menus déroulants avec support mobile (touch events)
 *
 * Usage:
 *   <div data-controller="dropdown">
 *     <button data-action="click->dropdown#toggle">Menu</button>
 *     <div data-dropdown-target="menu" class="hidden">...</div>
 *   </div>
 */
import { Controller } from '../ogan-stimulus.js';

export default class DropdownController extends Controller {
    static targets = ['menu'];

    connect() {
        this.isOpen = false;
        // Fermer le dropdown quand on clique ailleurs
        this.handleClickOutside = this.handleClickOutside.bind(this);
        document.addEventListener('click', this.handleClickOutside);
    }

    disconnect() {
        document.removeEventListener('click', this.handleClickOutside);
    }

    toggle(event) {
        event.preventDefault();
        event.stopPropagation();

        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        if (!this.hasMenuTarget) return;

        this.isOpen = true;
        this.menuTarget.classList.remove('hidden');
    }

    close() {
        if (!this.hasMenuTarget) return;

        this.isOpen = false;
        this.menuTarget.classList.add('hidden');
    }

    handleClickOutside(event) {
        // Ne rien faire si le menu n'est pas ouvert
        if (!this.isOpen) return;

        // Fermer si le clic est en dehors du dropdown
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }
}
