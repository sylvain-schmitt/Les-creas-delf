// assets/js/controllers/scroll_reveal_controller.js
import { Controller } from '../ogan-stimulus.js';

/**
 * Scroll Reveal Controller
 * Triggers smooth fade-in animation when elements enter the viewport
 * For elements visible on load: shows blank page first, then fades in content
 */
export default class ScrollRevealController extends Controller {
    connect() {
        // Get delay from data attribute (default 300ms to see the blank state)
        const delay = parseInt(this.data('delay') || '500', 10);

        // Get animation distance (default 30px, 0 for pure opacity fade)
        const distance = parseInt(this.data('distance') || '30', 10);

        // Get animation duration (default 1000ms)
        const duration = parseInt(this.data('duration') || '1000', 10);

        // Store config
        this.config = { delay, distance, duration };

        // Set initial hidden state - completely invisible
        this.element.style.opacity = '0';
        if (distance > 0) {
            this.element.style.transform = `translateY(${distance}px)`;
        }

        // Check if element is already in viewport on page load
        const rect = this.element.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom > 0;

        if (isVisible) {
            // Wait for the page to fully render, then animate after delay
            // This ensures the user sees the blank state first
            setTimeout(() => {
                this.reveal();
            }, delay);
            return;
        }

        // Create Intersection Observer for elements not yet visible
        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        // Small delay for scroll-triggered elements
                        setTimeout(() => {
                            this.reveal();
                        }, 50);
                        this.observer.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            }
        );

        this.observer.observe(this.element);
    }

    reveal() {
        const { duration, distance } = this.config;

        // Apply smooth fade-in transition
        this.element.style.transition = `opacity ${duration}ms ease-out, transform ${duration}ms ease-out`;
        this.element.style.opacity = '1';
        if (distance > 0) {
            this.element.style.transform = 'translateY(0)';
        }

        // After animation completes, clean up inline styles
        setTimeout(() => {
            this.element.style.transform = '';
            this.element.style.transition = '';
        }, duration + 100);
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}
