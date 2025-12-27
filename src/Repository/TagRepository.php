<?php

namespace App\Repository;

use App\Model\Tag;
use Ogan\Database\Database;

class TagRepository
{
    /**
     * Trouver tous les tags
     */
    public function findAll(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM tags ORDER BY name ASC";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Tag($row), $results);
    }

    /**
     * Trouver un tag par son slug
     */
    public function findBySlug(string $slug): ?Tag
    {
        return Tag::findBySlug($slug);
    }

    /**
     * Trouver un tag par son ID
     */
    public function find(int $id): ?Tag
    {
        return Tag::find($id);
    }

    /**
     * Trouver les tags avec le nombre d'articles (via SQL brut)
     */
    public function findWithArticleCount(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT t.*, COUNT(CASE WHEN a.status = 'published' THEN a.id END) as articles_count
                FROM tags t
                LEFT JOIN article_tag at ON t.id = at.tag_id
                LEFT JOIN articles a ON at.article_id = a.id
                GROUP BY t.id
                ORDER BY t.name ASC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Trouver les tags populaires (les plus utilisés)
     */
    public function findPopular(int $limit = 10): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT t.*, COUNT(a.id) as articles_count
                FROM tags t
                INNER JOIN article_tag at ON t.id = at.tag_id
                INNER JOIN articles a ON at.article_id = a.id
                WHERE a.status = 'published'
                GROUP BY t.id
                ORDER BY articles_count DESC
                LIMIT " . (int) $limit;

        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Tag($row), $results);
    }

    /**
     * Trouver les tags d'un article
     */
    public function findByArticle(int $articleId): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT t.* FROM tags t
                INNER JOIN article_tag at ON t.id = at.tag_id
                WHERE at.article_id = ?
                ORDER BY t.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$articleId]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Tag($row), $results);
    }
}
