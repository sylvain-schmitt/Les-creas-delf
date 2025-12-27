<?php

namespace App\Repository;

use App\Model\Article;
use App\Enum\ArticleStatus;
use Ogan\Database\Database;

class ArticleRepository
{
    /**
     * Trouver tous les articles publiés (paginés)
     */
    public function findPublishedPaginated(int $perPage = 10)
    {
        return Article::where('status', '=', ArticleStatus::PUBLISHED->value)
            ->orderBy('published_at', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Trouver tous les articles publiés (sans pagination)
     */
    public function findPublished(?int $limit = null): array
    {
        $query = Article::where('status', '=', ArticleStatus::PUBLISHED->value)
            ->orderBy('published_at', 'DESC');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Trouver tous les brouillons
     */
    public function findDrafts(): array
    {
        return Article::where('status', '=', ArticleStatus::DRAFT->value)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * Trouver les articles archivés
     */
    public function findArchived(): array
    {
        return Article::where('status', '=', ArticleStatus::ARCHIVED->value)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * Trouver un article par son slug
     */
    public function findBySlug(string $slug): ?Article
    {
        return Article::findBySlug($slug);
    }

    /**
     * Trouver un article par son ID
     */
    public function find(int $id): ?Article
    {
        return Article::find($id);
    }

    /**
     * Trouver les articles d'une catégorie (paginés)
     */
    public function findByCategoryPaginated(int $categoryId, int $perPage = 10)
    {
        return Article::where('category_id', '=', $categoryId)
            ->where('status', '=', ArticleStatus::PUBLISHED->value)
            ->orderBy('published_at', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Trouver les articles d'une catégorie (sans pagination)
     */
    public function findByCategory(int $categoryId, ?int $limit = null): array
    {
        $query = Article::where('category_id', '=', $categoryId)
            ->where('status', '=', ArticleStatus::PUBLISHED->value)
            ->orderBy('published_at', 'DESC');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Trouver les articles avec un tag (via SQL brut pour le JOIN)
     */
    public function findByTag(int $tagId, ?int $limit = null): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT a.* FROM articles a
                INNER JOIN article_tag at ON a.id = at.article_id
                WHERE at.tag_id = ? AND a.status = ?
                ORDER BY a.published_at DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tagId, ArticleStatus::PUBLISHED->value]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Article($row), $results);
    }

    /**
     * Recherche dans les articles (via SQL brut pour LIKE OR)
     */
    public function search(string $query, ?int $limit = 20): array
    {
        $pdo = Database::getConnection();
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT * FROM articles
                WHERE status = ?
                AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
                ORDER BY published_at DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([ArticleStatus::PUBLISHED->value, $searchTerm, $searchTerm, $searchTerm]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Article($row), $results);
    }

    /**
     * Trouver les articles récents (pour admin, tous les statuts)
     */
    public function findRecentPaginated(int $perPage = 15)
    {
        $pdo = Database::getConnection();

        // Utilise SQL brut car pas de where avant orderBy
        $sql = "SELECT * FROM articles ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Article($row), $results);
    }

    /**
     * Trouver les derniers articles (pour homepage)
     */
    public function findLatest(int $limit = 5): array
    {
        return Article::where('status', '=', ArticleStatus::PUBLISHED->value)
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Compter les articles par statut
     */
    public function countByStatus(ArticleStatus $status): int
    {
        return Article::where('status', '=', $status->value)->count();
    }

    /**
     * Trouver les articles d'un auteur
     */
    public function findByAuthor(int $userId): array
    {
        return Article::where('user_id', '=', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
