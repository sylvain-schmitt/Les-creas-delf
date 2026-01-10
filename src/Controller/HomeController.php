<?php

namespace App\Controller;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use App\Repository\ArticleRepository;
use App\Model\Category;

class HomeController extends AbstractController
{
    private ArticleRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
    }

    #[Route(path: '/', methods: ['GET'], name: 'index')]
    public function index(): Response
    {
        // Récupérer les 3 derniers articles publiés
        $latestArticles = $this->articleRepository->findLatest(3);

        // Récupérer toutes les catégories pour les tags
        $categories = Category::all();

        return $this->render('home/index.ogan', [
            'title' => 'Les Créas D\'elf',
            'latestArticles' => $latestArticles,
            'categories' => $categories
        ]);
    }
}
