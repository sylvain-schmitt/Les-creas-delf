/**
 * Clipboard Controller
 * Copie du texte dans le presse-papiers avec notification toast
 */
import { Controller } from '../ogan-stimulus.js';

export default class ClipboardController extends Controller {
    copy(event) {
        // Récupérer le texte depuis l'attribut data-clipboard-text-param
        const button = event.currentTarget;
        const text = button.dataset.clipboardTextParam;

        if (!text) {
            console.warn('Clipboard: No text to copy. Add data-clipboard-text-param attribute.');
            return;
        }

        const fullUrl = text.startsWith('http') ? text : window.location.origin + text;

        navigator.clipboard.writeText(fullUrl).then(() => {
            this.showToast('URL copiée !', 'success');
        }).catch(err => {
            console.error('Clipboard: Failed to copy', err);
            // Fallback pour les anciens navigateurs
            this.fallbackCopy(fullUrl);
        });
    }

    fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
            this.showToast('URL copiée !', 'success');
        } catch (err) {
            this.showToast('Erreur lors de la copie', 'error');
        }

        document.body.removeChild(textarea);
    }

    showToast(message, type = 'success') {
        // Créer le toast
        const toast = document.createElement('div');
        toast.className = `fixed top-12 right-12 z-50 px-4 py-3 rounded-xl shadow-lg text-white font-medium transition-all transform translate-y-full opacity-0 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                ${type === 'success'
                ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
            }
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Animation d'entrée
        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });

        // Fermer après 2 secondes
        setTimeout(() => {
            toast.style.transform = 'translateY(100%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }
}
