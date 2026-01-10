import { Controller } from '../ogan-stimulus.js';

/**
 * Search Bar Controller
 *
 * Gère l'affichage distinct du bouton "Ouvrir" et du formulaire de recherche.
 */
export default class SearchBarController extends Controller {
    static targets = ["opener", "form", "input"]

    connect() {
        // État initial : fermé
        this.close()

        // Gestionnaire de clic global (bind pour garder le contexte 'this')
        this.boundCloseHandler = this.closeOnClickOutside.bind(this)

        // Délai pour éviter que le clic d'ouverture ne déclenche immédiatement la fermeture
        setTimeout(() => {
            document.addEventListener('click', this.boundCloseHandler)
        }, 100)
    }

    disconnect() {
        document.removeEventListener('click', this.boundCloseHandler)
    }

    open(event) {
        if (event) {
            event.preventDefault()
            event.stopPropagation() // Important pour éviter que le click global ne le ferme
        }

        if (this.hasOpenerTarget) {
            this.openerTarget.classList.add('hidden')
        }

        if (this.hasFormTarget) {
            this.formTarget.classList.remove('hidden')
            this.formTarget.classList.add('flex')

            // Animation/Focus
            requestAnimationFrame(() => {
                if (this.hasInputTarget) {
                    this.inputTarget.focus()
                }
            })
        }
    }

    close() {
        // Masquer le formulaire
        if (this.hasFormTarget) {
            this.formTarget.classList.add('hidden')
            this.formTarget.classList.remove('flex')

            // Vider l'input si on ferme ? Optionnel.
            // Pour l'instant on garde le texte si l'utilisateur y revient.
        }

        // Réafficher le bouton loupe
        if (this.hasOpenerTarget) {
            this.openerTarget.classList.remove('hidden')
        }
    }

    closeOnClickOutside(event) {
        // Si le clic est DANS le contrôleur, on ne fait rien
        if (this.element.contains(event.target)) {
            return
        }

        // Sinon (clic dehors), on ferme si c'est ouvert
        if (this.hasFormTarget && !this.formTarget.classList.contains('hidden')) {
            this.close()
        }
    }

    onKeydown(event) {
        if (event.key === 'Escape') {
            this.close()
        }
    }
}
