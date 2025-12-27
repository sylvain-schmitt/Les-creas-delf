<?php

namespace App\Repository;

use App\Model\Comment;
use App\Enum\CommentStatus;
use Ogan\Database\Database;

class CommentRepository
{
    /**
     * Trouver les commentaires en attente de modération (paginés)
     */
    public function findPendingPaginated(int $perPage = 15)
    {
        return Comment::where('status', '=', CommentStatus::PENDING->value)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Trouver les commentaires en attente de modération
     */
    public function findPending(): array
    {
        return Comment::where('status', '=', CommentStatus::PENDING->value)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Trouver les commentaires approuvés d'un article
     */
    public function findByArticle(int $articleId): array
    {
        return Comment::where('article_id', '=', $articleId)
            ->where('status', '=', CommentStatus::APPROVED->value)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Compter les commentaires en attente
     */
    public function countPending(): int
    {
        return Comment::where('status', '=', CommentStatus::PENDING->value)->count();
    }

    /**
     * Compter les commentaires approuvés d'un article
     */
    public function countByArticle(int $articleId): int
    {
        return Comment::where('article_id', '=', $articleId)
            ->where('status', '=', CommentStatus::APPROVED->value)
            ->count();
    }

    /**
     * Trouver un commentaire par son ID
     */
    public function find(int $id): ?Comment
    {
        return Comment::find($id);
    }

    /**
     * Trouver les commentaires récents (pour admin, tous les statuts)
     */
    public function findRecentPaginated(int $perPage = 15): array
    {
        $pdo = Database::getConnection();

        // Utilise SQL brut car pas de where avant orderBy
        $sql = "SELECT * FROM comments ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Comment($row), $results);
    }

    /**
     * Trouver les commentaires d'un utilisateur
     */
    public function findByUser(int $userId): array
    {
        return Comment::where('user_id', '=', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
