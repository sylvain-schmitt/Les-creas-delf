<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Ogan\Controller\AbstractController;
use Ogan\Router\Attributes\Route;
use Ogan\Http\Response;

/**
 * Contrôleur pour la recherche d'articles
 */
class SearchController extends AbstractController
{
    private ArticleRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
    }

    #[Route(path: '/recherche', methods: ['GET'], name: 'search')]
    public function index(): Response
    {
        $query = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));

        if (empty($query)) {
            return $this->render('pages/search.ogan', [
                'query' => '',
                'results' => [],
                'resultsCount' => 0,
            ]);
        }

        $results = $this->articleRepository->searchPaginated($query, $page);

        // Si requête HTMX ciblant spécifiquement la liste (pagination), on renvoie seulement le partial
        if (
            isset($_SERVER['HTTP_HX_REQUEST']) &&
            isset($_SERVER['HTTP_HX_TARGET']) &&
            $_SERVER['HTTP_HX_TARGET'] === 'search-results-container'
        ) {
            return $this->render('pages/search/_results_list.ogan', [
                'results' => $results,
            ]);
        }

        return $this->render('pages/search.ogan', [
            'query' => $query,
            'results' => $results,
            'resultsCount' => $results->total(),
        ]);
    }
}
