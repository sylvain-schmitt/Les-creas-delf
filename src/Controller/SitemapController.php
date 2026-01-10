<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Ogan\Controller\AbstractController;
use Ogan\Router\Attributes\Route;
use Ogan\Http\Response;

/**
 * Contrôleur pour le sitemap XML dynamique
 */
class SitemapController extends AbstractController
{
    private ArticleRepository $articleRepository;
    private CategoryRepository $categoryRepository;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
        $this->categoryRepository = new CategoryRepository();
    }

    #[Route(path: '/sitemap.xml', methods: ['GET'], name: 'sitemap')]
    public function index(): Response
    {
        $baseUrl = $this->getBaseUrl();

        // Pages statiques
        $staticPages = [
            ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/blog', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/about', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        // Articles publiés
        $articles = $this->articleRepository->findPublishedPaginated(1000)->items();

        // Catégories
        $categories = $this->categoryRepository->findAll();

        // Générer le XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Pages statiques
        foreach ($staticPages as $page) {
            $xml .= $this->generateUrlEntry($page['loc'], $page['priority'], $page['changefreq']);
        }

        // Articles
        foreach ($articles as $article) {
            $lastmod = $article->getUpdatedAt() ?? $article->getCreatedAt();
            $xml .= $this->generateUrlEntry(
                $baseUrl . '/blog/' . $article->getSlug(),
                '0.8',
                'weekly',
                $lastmod instanceof \DateTime ? $lastmod->format('Y-m-d') : null
            );
        }

        // Catégories (filtrage blog)
        foreach ($categories as $category) {
            $xml .= $this->generateUrlEntry(
                $baseUrl . '/blog?category=' . $category->getSlug(),
                '0.6',
                'weekly'
            );
        }

        $xml .= '</urlset>';

        $response = new Response($xml, 200);
        $response->setHeader('Content-Type', 'application/xml; charset=utf-8');

        return $response;
    }

    /**
     * Génère une entrée URL pour le sitemap
     */
    private function generateUrlEntry(
        string $loc,
        string $priority = '0.5',
        string $changefreq = 'weekly',
        ?string $lastmod = null
    ): string {
        $entry = "  <url>\n";
        $entry .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
        if ($lastmod) {
            $entry .= "    <lastmod>" . $lastmod . "</lastmod>\n";
        }
        $entry .= "    <changefreq>" . $changefreq . "</changefreq>\n";
        $entry .= "    <priority>" . $priority . "</priority>\n";
        $entry .= "  </url>\n";

        return $entry;
    }

    /**
     * Récupère l'URL de base du site
     */
    private function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $protocol . '://' . $host;
    }
}
