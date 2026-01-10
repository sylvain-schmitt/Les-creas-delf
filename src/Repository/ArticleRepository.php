<?php

namespace App\Repository;

use App\Model\Article;
use App\Enum\ArticleStatus;
use Ogan\Database\Pagination\Paginator;
use Ogan\Database\Database;

class ArticleRepository
{


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
     * Trouver les derniers articles (pour homepage)
     */
    public function findLatest(int $limit = 5): array
    {
        $results = Article::where('status', '=', ArticleStatus::PUBLISHED->value)
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get();

        // Hydrater les résultats en objets Article
        $models = [];
        foreach ($results as $row) {
            $models[] = new Article($row);
        }
        return $models;
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

    /**
     * Trouver les articles publiés paginés
     */
    public function findPublishedPaginated(int $perPage = 9): \Ogan\Database\Pagination\Paginator
    {
        return Article::where('status', '=', ArticleStatus::PUBLISHED->value)
            ->orderBy('published_at', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Trouver les articles publiés d'une catégorie paginés
     */
    public function findPublishedByCategory(int $categoryId, int $perPage = 9): \Ogan\Database\Pagination\Paginator
    {
        return Article::where('status', '=', ArticleStatus::PUBLISHED->value)
            ->where('category_id', '=', $categoryId)
            ->orderBy('published_at', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Trouver les articles similaires (même catégorie, excluant l'article actuel)
     */
    public function findRelated(int $excludeId, int $categoryId, int $limit = 3): array
    {
        $results = Article::where('status', '=', ArticleStatus::PUBLISHED->value)
            ->where('category_id', '=', $categoryId)
            ->where('id', '!=', $excludeId)
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get();

        // Hydrater les résultats en objets Article
        $models = [];
        foreach ($results as $row) {
            $models[] = new Article($row);
        }
        return $models;
    }

    /**
     * Recherche full-text dans les articles publiés
     *
     * Utilise PostgreSQL full-text search natif si disponible,
     * sinon fallback sur LIKE pour les autres bases de données.
     */
    public function search(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }

        $pdo = Database::getConnection();
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if (strtolower($driver) === 'pgsql') {
            // PostgreSQL: utiliser full-text search natif
            return $this->searchPostgres($pdo, $query, $limit);
        }

        // Fallback: recherche LIKE pour MySQL/SQLite
        return $this->searchLike($query, $limit);
    }

    /**
     * Recherche full-text paginée
     */
    public function searchPaginated(string $query, int $page = 1, int $perPage = 9): Paginator
    {
        $query = trim($query);
        if (empty($query)) {
            return new Paginator([], 0, $perPage, $page);
        }

        $pdo = Database::getConnection();
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if (strtolower($driver) === 'pgsql') {
            return $this->searchPostgresPaginated($pdo, $query, $page, $perPage);
        }

        return $this->searchLikePaginated($query, $page, $perPage);
    }

    /**
     * Recherche PostgreSQL avec full-text search (Améliorée)
     */
    private function searchPostgres(\PDO $pdo, string $query, int $limit): array
    {
        // 1. Essai avec websearch_to_tsquery (supporte "guillemets", OR, -exclusion)
        // C'est le mode le plus standard et intuitif
        $results = $this->executePostgresSearch($pdo, $query, 'websearch_to_tsquery', $limit);

        // 2. Si aucun résultat, essaie en mode "OU" (l'un des mots-clés)
        if (empty($results) && str_word_count($query) > 1) {
            // Remplace les espaces par des | pour faire un OR
            $orQuery = implode(' | ', array_filter(explode(' ', trim($query))));
            $results = $this->executePostgresSearch($pdo, $orQuery, 'to_tsquery', $limit);
        }

        // 3. Toujours rien ? C'est peut-être des stop-words (ex: "ses", "le", "la").
        // On fallback sur une recherche LIKE classique.
        if (empty($results)) {
            return $this->searchLike($query, $limit);
        }

        return $results;
    }

    private function executePostgresSearch(\PDO $pdo, string $query, string $function, int $limit): array
    {
        try {
            $sql = "
                SELECT a.*, ts_rank(search_vector, $function('french', :query)) AS rank
                FROM articles a
                WHERE a.status = :status
                AND search_vector @@ $function('french', :query)
                ORDER BY rank DESC, a.published_at DESC
                LIMIT :limit
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':query', $query, \PDO::PARAM_STR);
            $stmt->bindValue(':status', ArticleStatus::PUBLISHED->value, \PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();

            $results = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $results[] = new Article($row);
            }
            return $results;
        } catch (\PDOException $e) {
            // En cas d'erreur de syntaxe de requête (ex: parenthèses non fermées), on retourne vide
            return [];
        }
    }

    /**
     * Recherche PostgreSQL paginée (Améliorée)
     */
    private function searchPostgresPaginated(\PDO $pdo, string $query, int $page, int $perPage): Paginator
    {
        // Stratégie identique : websearch (AND) -> OR -> LIKE fallback

        // 1. Websearch (AND)
        $paginator = $this->executePostgresSearchPaginated($pdo, $query, 'websearch_to_tsquery', $page, $perPage);
        if ($paginator->total() > 0) return $paginator;

        // 2. Mode OR
        if (str_word_count($query) > 1) {
            $orQuery = implode(' | ', array_filter(explode(' ', trim($query))));
            $paginator = $this->executePostgresSearchPaginated($pdo, $orQuery, 'to_tsquery', $page, $perPage);
            if ($paginator->total() > 0) return $paginator;
        }

        // 3. Fallback LIKE (Stopwords)
        return $this->searchLikePaginated($query, $page, $perPage);
    }

    private function executePostgresSearchPaginated(\PDO $pdo, string $query, string $function, int $page, int $perPage): Paginator
    {
        try {
            // Count
            $countSql = "
                SELECT COUNT(*)
                FROM articles
                WHERE status = :status
                AND search_vector @@ $function('french', :query)
            ";
            $stmt = $pdo->prepare($countSql);
            $stmt->bindValue(':query', $query, \PDO::PARAM_STR);
            $stmt->bindValue(':status', ArticleStatus::PUBLISHED->value, \PDO::PARAM_STR);
            $stmt->execute();
            $total = (int) $stmt->fetchColumn();

            if ($total === 0) {
                return new Paginator([], 0, $perPage, $page);
            }

            // Fetch
            $offset = ($page - 1) * $perPage;
            $sql = "
                SELECT a.*, ts_rank(search_vector, $function('french', :query)) AS rank
                FROM articles a
                WHERE a.status = :status
                AND search_vector @@ $function('french', :query)
                ORDER BY rank DESC, a.published_at DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':query', $query, \PDO::PARAM_STR);
            $stmt->bindValue(':status', ArticleStatus::PUBLISHED->value, \PDO::PARAM_STR);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            $results = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $results[] = new Article($row);
            }

            return new Paginator($results, $total, $perPage, $page);
        } catch (\Exception $e) {
            return new Paginator([], 0, $perPage, $page);
        }
    }

    /**
     * Recherche fallback avec LIKE pour MySQL/SQLite
     */
    private function searchLike(string $query, int $limit): array
    {
        $pdo = Database::getConnection();
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT * FROM articles
                WHERE status = ?
                AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
                ORDER BY published_at DESC
                LIMIT ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([ArticleStatus::PUBLISHED->value, $searchTerm, $searchTerm, $searchTerm, $limit]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Article($row), $results);
    }
    /**
     * Recherche fallback paginée avec LIKE
     */
    private function searchLikePaginated(string $query, int $page, int $perPage): Paginator
    {
        $pdo = Database::getConnection();
        $searchTerm = '%' . $query . '%';
        $params = [ArticleStatus::PUBLISHED->value, $searchTerm, $searchTerm, $searchTerm];

        // 1. Compter le total
        $countSql = "
            SELECT COUNT(*) FROM articles
            WHERE status = ?
            AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
        ";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        if ($total === 0) {
            return new Paginator([], 0, $perPage, $page);
        }

        // 2. Récupérer les résultats
        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT * FROM articles
            WHERE status = ?
            AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
            ORDER BY published_at DESC
            LIMIT ? OFFSET ?
        ";

        // Ajouter limit/offset aux params
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $models = array_map(fn($row) => new Article($row), $results);

        return new Paginator($models, $total, $perPage, $page);
    }
}
