<?php

namespace App\Repository;

use App\Model\Category;
use Ogan\Database\Database;

class CategoryRepository
{
    /**
     * Trouver toutes les catégories
     */
    public function findAll(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Category($row), $results);
    }

    /**
     * Trouver une catégorie par son slug
     */
    public function findBySlug(string $slug): ?Category
    {
        // Utilisation directe de PDO pour éviter les problèmes potentiels avec l'ORM
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new Category($data);
    }

    /**
     * Trouver une catégorie par son ID
     */
    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Trouver les catégories avec le nombre d'articles publiés (via SQL brut)
     */
    public function findWithArticleCount(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT c.*, COUNT(a.id) as articles_count
                FROM categories c
                LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
                GROUP BY c.id
                ORDER BY c.name ASC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Trouver les catégories non vides (avec au moins un article publié)
     */
    public function findNonEmpty(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT DISTINCT c.*
                FROM categories c
                INNER JOIN articles a ON c.id = a.category_id
                WHERE a.status = 'published'
                ORDER BY c.name ASC";

        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Category($row), $results);
    }
}
