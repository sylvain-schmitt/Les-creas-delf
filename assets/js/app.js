/**
 * ═══════════════════════════════════════════════════════════════════════
 * 🚀 APP.JS - Point d'entrée JavaScript Ogan Framework
 * ═══════════════════════════════════════════════════════════════════════
 *
 * Ce fichier centralise tout le JavaScript de l'application.
 * Les imports utilisent l'importmap généré par OganAssetMapper.
 *
 * ═══════════════════════════════════════════════════════════════════════
 */

// ─────────────────────────────────────────────────────────────────────────
// Import OganStimulus
// ─────────────────────────────────────────────────────────────────────────
import { Application } from './ogan-stimulus.js';

// ─────────────────────────────────────────────────────────────────────────
// Import des contrôleurs framework
// ─────────────────────────────────────────────────────────────────────────
import FlashController from './controllers/flash_controller.js';
import ThemeController from './controllers/theme_controller.js';
import SidebarController from './controllers/sidebar_controller.js';
import ScrollRevealController from './controllers/scroll_reveal_controller.js';
import NavActiveController from './controllers/nav_active_controller.js';
import ConfirmModalController from './controllers/confirm_modal_controller.js';
import MediaPickerController from './controllers/media_picker_controller.js';
import ClipboardController from './controllers/clipboard_controller.js';
import ModalController from './controllers/modal_controller.js';
import SearchBarController from './controllers/search-bar_controller.js';
import DropdownController from './controllers/dropdown_controller.js';

// ─────────────────────────────────────────────────────────────────────────
// Initialisation de l'application
// ─────────────────────────────────────────────────────────────────────────
const app = Application.start();

// Enregistrement des contrôleurs framework
app.register('flash', FlashController);
app.register('theme', ThemeController);
app.register('sidebar', SidebarController);
app.register('scroll-reveal', ScrollRevealController);
app.register('nav-active', NavActiveController);
app.register('confirm-modal', ConfirmModalController);
app.register('media-picker', MediaPickerController);
app.register('clipboard', ClipboardController);
app.register('modal', ModalController);
app.register('search-bar', SearchBarController);
app.register('dropdown', DropdownController);

// ─────────────────────────────────────────────────────────────────────────
// Export pour utilisation avancée
// ─────────────────────────────────────────────────────────────────────────
window.OganApp = app;

console.log('🚀 Ogan Framework initialized');
