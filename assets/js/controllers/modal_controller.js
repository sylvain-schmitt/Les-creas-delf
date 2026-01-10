/**
 * Modal Controller
 * Gestion simple des modales
 */
import { Controller } from '../ogan-stimulus.js';

export default class ModalController extends Controller {
    static targets = ['dialog'];

    open(event) {
        // Récupérer la cible du modal depuis les paramètres ou l'élément courant
        const targetSelector = event.params?.target || event.currentTarget.dataset.modalTargetParam;
        const modal = targetSelector ? document.querySelector(targetSelector) : this.element;

        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    close() {
        // Fermer le modal courant ou le plus proche parent
        const modal = this.element.closest('[id$="-modal"]') || this.element;

        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    closeAndReset() {
        // Ferme le modal et reset le formulaire s'il existe
        this.close();
        const form = this.element.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}
