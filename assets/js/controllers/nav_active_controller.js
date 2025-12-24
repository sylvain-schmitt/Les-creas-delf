// assets/js/controllers/nav_active_controller.js
import { Controller } from '../ogan-stimulus.js';

/**
 * Nav Active Controller
 * Highlights the current page link in navigation
 */
export default class NavActiveController extends Controller {
    connect() {
        this.highlightCurrentLink();
    }

    highlightCurrentLink() {
        const currentPath = window.location.pathname;
        const links = this.element.querySelectorAll('.nav-link[data-nav-path]');

        links.forEach(link => {
            const navPath = link.dataset.navPath;
            const isActive = this.isPathActive(currentPath, navPath);

            if (isActive) {
                // Desktop style: add underline and primary color
                link.classList.remove('text-muted');
                link.classList.add('text-primary');

                // Add underline indicator for desktop nav
                if (!link.classList.contains('flex')) {
                    const underline = document.createElement('span');
                    underline.className = 'absolute -bottom-1 left-0 w-full h-0.5 rounded-full bg-primary';
                    link.appendChild(underline);
                } else {
                    // Mobile nav: add background
                    link.classList.remove('text-muted');
                    link.classList.add('bg-primary-10', 'text-primary');
                }
            }
        });
    }

    isPathActive(currentPath, navPath) {
        // Exact match for home
        if (navPath === '/') {
            return currentPath === '/';
        }
        // Prefix match for other pages
        return currentPath === navPath || currentPath.startsWith(navPath + '/');
    }
}
