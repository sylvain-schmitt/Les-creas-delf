<?php

namespace App\Controller;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;

class PageController extends AbstractController
{
    #[Route(path: '/creations', methods: ['GET'], name: 'creations')]
    public function creations(): Response
    {
        return $this->render('pages/creations.ogan', [
            'title' => 'Mes Créations - Les Créas d\'Elf'
        ]);
    }

    #[Route(path: '/contact', methods: ['GET', 'POST'], name: 'contact')]
    public function contact(): Response
    {
        // TODO: Ajouter le formulaire de contact plus tard
        if ($this->request->isMethod('POST')) {
            // Traitement du formulaire
            $this->addFlash('success', 'Votre message a bien été envoyé !');
            return $this->redirect('/contact');
        }

        return $this->render('pages/contact.ogan', [
            'title' => 'Contact - Les Créas d\'Elf'
        ]);
    }

    #[Route(path: '/a-propos', methods: ['GET'], name: 'about')]
    public function about(): Response
    {
        return $this->render('pages/about.ogan', [
            'title' => 'À propos - Les Créas d\'Elf'
        ]);
    }

    #[Route(path: '/mentions-legales', methods: ['GET'], name: 'legal')]
    public function legal(): Response
    {
        return $this->render('pages/legal.ogan', [
            'title' => 'Mentions Légales - Les Créas d\'Elf'
        ]);
    }

    #[Route(path: '/cgv', methods: ['GET'], name: 'cgv')]
    public function cgv(): Response
    {
        return $this->render('pages/cgv.ogan', [
            'title' => 'CGV - Les Créas d\'Elf'
        ]);
    }

    #[Route(path: '/confidentialite', methods: ['GET'], name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('pages/privacy.ogan', [
            'title' => 'Politique de Confidentialité - Les Créas d\'Elf'
        ]);
    }
}
