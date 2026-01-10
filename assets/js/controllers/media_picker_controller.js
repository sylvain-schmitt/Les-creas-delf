/**
 * Media Picker Controller
 * Permet de sélectionner une image depuis la médiathèque
 */
import { Controller } from '../ogan-stimulus.js';

export default class MediaPickerController extends Controller {
    static targets = ['input', 'preview', 'previewImage', 'modal', 'grid', 'placeholder', 'confirmModal'];

    connect() {
        console.log('MediaPickerController connected', this.element);
        // Charger les médias au connect
        this.loadMedia();
    }

    async loadMedia() {
        try {
            const response = await fetch('/admin/api/media');
            const media = await response.json();
            console.log('Media loaded:', media.length, 'items');
            this.renderGrid(media);
        } catch (error) {
            console.error('Erreur chargement médias:', error);
        }
    }

    renderGrid(media) {
        if (!this.hasGridTarget) return;

        if (media.length === 0) {
            this.gridTarget.innerHTML = `
                <div class="col-span-full text-center py-8 text-muted">
                    <p>Aucune image disponible.</p>
                    <a href="/admin/media" class="text-primary hover:underline">Ajouter des images</a>
                </div>
            `;
            return;
        }

        this.gridTarget.innerHTML = media.map(item => `
            <button type="button"
                    class="aspect-square bg-muted/10 rounded-lg overflow-hidden hover:ring-2 hover:ring-primary transition-all focus:ring-2 focus:ring-primary outline-none"
                    data-media-id="${item.id}"
                    data-media-url="${item.originalUrl}"
                    data-media-alt="${item.alt || ''}">
                <img src="${item.url}" alt="${item.alt || ''}" class="w-full h-full object-cover">
            </button>
        `).join('');

        // Event delegation pour les boutons générés dynamiquement
        this.gridTarget.addEventListener('click', (e) => {
            const button = e.target.closest('button[data-media-id]');
            if (button) {
                this.select({ currentTarget: button });
            }
        });
    }

    open() {
        console.log('MediaPickerController.open() called', this.hasModalTarget);
        if (this.hasModalTarget) {
            this.modalTarget.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    close() {
        if (this.hasModalTarget) {
            this.modalTarget.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    select(event) {
        const button = event.currentTarget;
        const mediaId = button.dataset.mediaId;
        const mediaUrl = button.dataset.mediaUrl;
        const mediaAlt = button.dataset.mediaAlt;

        // Mettre à jour l'input hidden
        if (this.hasInputTarget) {
            this.inputTarget.value = mediaId;
        }

        // Afficher la preview
        if (this.hasPreviewTarget && this.hasPreviewImageTarget) {
            this.previewImageTarget.src = mediaUrl;
            this.previewImageTarget.alt = mediaAlt;
            this.previewTarget.classList.remove('hidden');
        }

        // Masquer le placeholder
        if (this.hasPlaceholderTarget) {
            this.placeholderTarget.classList.add('hidden');
        }

        // Fermer le modal
        this.close();
    }

    remove(event) {
        // Ouvrir la modal de confirmation au lieu de supprimer directement
        if (this.hasConfirmModalTarget) {
            this.confirmModalTarget.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            // Fallback si pas de modal : confirm native
            if (confirm('Voulez-vous vraiment retirer cette image ?')) {
                this.doRemove();
            }
        }
    }

    confirmRemove() {
        this.doRemove();
        this.closeConfirmModal();
    }

    cancelRemove() {
        this.closeConfirmModal();
    }

    closeConfirmModal() {
        if (this.hasConfirmModalTarget) {
            this.confirmModalTarget.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    doRemove() {
        // Vider l'input
        if (this.hasInputTarget) {
            this.inputTarget.value = '';
        }

        // Masquer la preview
        if (this.hasPreviewTarget) {
            this.previewTarget.classList.add('hidden');
        }

        // Afficher le placeholder
        if (this.hasPlaceholderTarget) {
            this.placeholderTarget.classList.remove('hidden');
        }
    }
}
