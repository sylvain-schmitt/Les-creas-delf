<?php

namespace App\Controller;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', methods: ['GET'], name: 'index')]
    public function index(): Response
    {
        return $this->render('home/index.ogan', [
            'title' => 'Les Créas D\'elf'
        ]);
    }
}
