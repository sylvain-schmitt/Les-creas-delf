<?php

namespace App\Service;

use App\Model\Comment;
use App\Enum\CommentStatus;

class CommentService
{
    /**
     * Approuver un commentaire
     */
    public function approve(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::APPROVED->value);
        $comment->save();
    }

    /**
     * Rejeter un commentaire
     */
    public function reject(Comment $comment): void
    {
        $comment->setStatus(CommentStatus::REJECTED->value);
        $comment->save();
    }

    /**
     * Créer un commentaire (visiteur ou utilisateur connecté)
     */
    public function create(array $data, int $articleId, ?int $userId = null): Comment
    {
        $comment = new Comment();
        $comment->setArticleId($articleId);
        $comment->setContent($data['content']);
        $comment->setStatus(CommentStatus::PENDING->value);
        $comment->setCreatedAt(new \DateTime());

        if ($userId) {
            // Utilisateur connecté
            $comment->setUserId($userId);
        } else {
            // Visiteur
            $comment->setAuthorName($data['author_name']);
            $comment->setAuthorEmail($data['author_email']);
        }

        $comment->save();

        return $comment;
    }

    /**
     * Supprimer un commentaire
     */
    public function delete(Comment $comment): void
    {
        $comment->delete();
    }

    /**
     * Approuver tous les commentaires en attente d'un article
     */
    public function approveAllByArticle(int $articleId): int
    {
        $count = 0;
        $comments = Comment::where('article_id', '=', $articleId)
            ->where('status', '=', CommentStatus::PENDING->value)
            ->get();

        foreach ($comments as $comment) {
            $this->approve($comment);
            $count++;
        }

        return $count;
    }
}
