<?php

namespace App\Service;

use App\Model\Article;
use App\Enum\ArticleStatus;
use Ogan\Database\Database;

class ArticleService
{
    /**
     * Publier un article
     */
    public function publish(Article $article): void
    {
        $article->status = ArticleStatus::PUBLISHED->value;
        $article->published_at = date('Y-m-d H:i:s');
        $article->save();
    }

    /**
     * Mettre un article en brouillon
     */
    public function unpublish(Article $article): void
    {
        $article->status = ArticleStatus::DRAFT->value;
        $article->published_at = null;
        $article->save();
    }

    /**
     * Archiver un article
     */
    public function archive(Article $article): void
    {
        $article->status = ArticleStatus::ARCHIVED->value;
        $article->save();
    }

    /**
     * Synchroniser les tags d'un article
     */
    public function syncTags(Article $article, array $tagIds): void
    {
        $pdo = Database::getConnection();

        // Remove existing tags
        $stmt = $pdo->prepare("DELETE FROM article_tag WHERE article_id = ?");
        $stmt->execute([$article->id]);

        // Add new tags
        $stmt = $pdo->prepare("INSERT INTO article_tag (article_id, tag_id) VALUES (?, ?)");
        foreach ($tagIds as $tagId) {
            $stmt->execute([$article->id, $tagId]);
        }
    }

    /**
     * Créer un article à partir de données
     */
    public function create(array $data, int $userId): Article
    {
        $article = new Article();
        $article->title = $data['title'];
        $article->excerpt = $data['excerpt'] ?? null;
        $article->content = $data['content'] ?? null;
        $article->category_id = $data['category_id'] ?? null;
        $article->featured_image_id = $data['featured_image_id'] ?? null;
        $article->user_id = $userId;
        $article->status = ArticleStatus::DRAFT->value;
        $article->created_at = date('Y-m-d H:i:s');
        $article->updated_at = date('Y-m-d H:i:s');

        // Generate slug
        $article->generateUniqueSlug();

        $article->save();

        // Sync tags if provided
        if (!empty($data['tags'])) {
            $this->syncTags($article, $data['tags']);
        }

        return $article;
    }

    /**
     * Mettre à jour un article
     */
    public function update(Article $article, array $data): Article
    {
        if (isset($data['title'])) {
            $article->title = $data['title'];
            // Regenerate slug if title changed
            $article->regenerateSlug();
        }

        if (array_key_exists('excerpt', $data)) {
            $article->excerpt = $data['excerpt'];
        }

        if (array_key_exists('content', $data)) {
            $article->content = $data['content'];
        }

        if (array_key_exists('category_id', $data)) {
            $article->category_id = $data['category_id'];
        }

        if (array_key_exists('featured_image_id', $data)) {
            $article->featured_image_id = $data['featured_image_id'];
        }

        if (isset($data['status'])) {
            $article->status = $data['status'];
            if ($data['status'] === ArticleStatus::PUBLISHED->value && !$article->published_at) {
                $article->published_at = date('Y-m-d H:i:s');
            }
        }

        $article->updated_at = date('Y-m-d H:i:s');
        $article->save();

        // Sync tags if provided
        if (isset($data['tags'])) {
            $this->syncTags($article, $data['tags']);
        }

        return $article;
    }

    /**
     * Supprimer un article
     */
    public function delete(Article $article): void
    {
        $pdo = Database::getConnection();

        // Remove tags association
        $stmt = $pdo->prepare("DELETE FROM article_tag WHERE article_id = ?");
        $stmt->execute([$article->id]);

        // Delete the article
        $article->delete();
    }
}
