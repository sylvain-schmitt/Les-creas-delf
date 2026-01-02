import { Controller } from '../ogan-stimulus.js';

/**
 * Confirm Modal Controller
 */
export default class extends Controller {
    connect() {
        // Create modal HTML if not exists
        if (!document.getElementById('confirm-modal')) {
            this.createModal()
        }
        this.modal = document.getElementById('confirm-modal')
        this.titleEl = document.getElementById('confirm-modal-title')
        this.messageEl = document.getElementById('confirm-modal-message')
        this.confirmBtn = document.getElementById('confirm-modal-confirm')

        // Bind click event manually
        this.element.addEventListener('click', (e) => this.open(e))
    }

    createModal() {
        const modalHtml = `
            <div id="confirm-modal" class="fixed inset-0 z-50 hidden">
                <!-- Backdrop with blur -->
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" id="confirm-modal-backdrop"></div>

                <!-- Modal Container -->
                <div class="fixed inset-0 flex items-center justify-center p-4">
                    <div class="bg-card rounded-2xl shadow-xl max-w-md w-full p-8 relative border border-default animate-scale-in">
                        <!-- Decorative accent -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-cta rounded-t-2xl"></div>

                        <!-- Icon -->
                        <div class="w-14 h-14 rounded-full bg-terracotta-10 flex items-center justify-center mx-auto mb-5">
                            <svg class="w-7 h-7 text-craft-terracotta" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>

                        <!-- Title -->
                        <h3 id="confirm-modal-title" class="text-xl font-semibold text-foreground text-center mb-3 font-serif">
                            Confirmer
                        </h3>

                        <!-- Message -->
                        <p id="confirm-modal-message" class="text-muted text-center mb-8 leading-relaxed">
                            Êtes-vous sûr de vouloir continuer ?
                        </p>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <button type="button" id="confirm-modal-cancel"
                                class="btn-outline flex-1">
                                Annuler
                            </button>
                            <button type="button" id="confirm-modal-confirm"
                                class="flex-1 px-4 py-2.5 rounded-xl bg-craft-terracotta text-white font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg">
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `
        document.body.insertAdjacentHTML('beforeend', modalHtml)

        // Bind close events
        document.getElementById('confirm-modal-backdrop').addEventListener('click', () => this.close())
        document.getElementById('confirm-modal-cancel').addEventListener('click', () => this.close())
    }

    open(event) {
        event.preventDefault()
        event.stopPropagation()
        console.log('Open modal called');

        const title = this.element.dataset.confirmModalTitleParam
        const message = this.element.dataset.confirmModalMessageParam
        const url = this.element.dataset.confirmModalUrlParam
        const method = this.element.dataset.confirmModalMethodParam

        this.titleEl.textContent = title || "Confirmer l'action"
        this.messageEl.textContent = message || "Êtes-vous sûr ?"
        this.actionUrl = url
        this.actionMethod = method || 'POST'
        this.targetElement = this.element.closest('tr')

        this.modal.classList.remove('hidden')
        document.body.classList.add('overflow-hidden')
        this.confirmBtn.onclick = () => this.confirm()
    }

    close() {
        this.modal.classList.add('hidden')
        document.body.classList.remove('overflow-hidden')
    }

    async confirm() {
        try {
            const response = await fetch(this.actionUrl, {
                method: this.actionMethod,
                headers: { 'HX-Request': 'true', 'X-Requested-With': 'XMLHttpRequest' }
            })

            if (response.ok && this.targetElement) {
                this.targetElement.style.transition = 'opacity 0.3s, transform 0.3s'
                this.targetElement.style.opacity = '0'
                this.targetElement.style.transform = 'translateX(-20px)'
                setTimeout(() => this.targetElement.remove(), 300)

                const html = await response.text()
                if (html.includes('flashes-container')) {
                    const doc = new DOMParser().parseFromString(html, 'text/html')
                    const flashes = doc.getElementById('flashes-container')
                    if (flashes) {
                        document.getElementById('flashes-container').innerHTML = flashes.innerHTML
                    }
                }
            }
        } catch (error) {
            console.error('Delete failed:', error)
        }
        this.close()
    }
}
