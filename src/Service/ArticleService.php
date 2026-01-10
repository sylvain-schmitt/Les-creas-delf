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
        $article->setStatus(ArticleStatus::PUBLISHED->value);
        $article->setPublishedAt(new \DateTime());
        $article->save();
    }

    /**
     * Mettre un article en brouillon
     */
    public function unpublish(Article $article): void
    {
        $article->setStatus(ArticleStatus::DRAFT->value);
        $article->setPublishedAt(null);
        $article->save();
    }

    /**
     * Archiver un article
     */
    public function archive(Article $article): void
    {
        $article->setStatus(ArticleStatus::ARCHIVED->value);
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

        $article->setTitle($data['title'] ?? null);
        $article->setContent($data['content'] ?? null);

        // Auto-generate excerpt if not provided
        $excerpt = $data['excerpt'] ?? null;
        if (empty($excerpt) && !empty($data['content'])) {
            $excerpt = \Ogan\Util\Text::excerpt($data['content'], 150);
        }
        $article->setExcerpt($excerpt);

        // Convertir les chaînes vides en null pour les int nullable
        $categoryId = !empty($data['category_id']) ? (int) $data['category_id'] : null;
        $featuredImageId = !empty($data['featured_image_id']) ? (int) $data['featured_image_id'] : null;

        $article->setCategoryId($categoryId);
        $article->setFeaturedImageId($featuredImageId);
        $article->setUserId($userId);
        $article->setStatus($data['status'] ?? ArticleStatus::DRAFT->value);
        $article->setCreatedAt(new \DateTime());
        $article->setUpdatedAt(new \DateTime());

        // Generate slug
        $article->generateUniqueSlug();

        // Create or update
        if ($article->getId()) {
            $article->save();
        } else {
            $id = $article->save();
            // Model::save() returns the ID for inserts, fix for redirect missing ID
            if ($id) {
                $article->setId((int)$id);
            }
        }

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
            $article->setTitle($data['title']);
            // Regenerate slug if title changed (implied by setTitle? No, separate logic usually)
            $article->regenerateSlug();
        }

        if (array_key_exists('excerpt', $data)) {
            $article->setExcerpt($data['excerpt']);
        }

        if (array_key_exists('content', $data)) {
            $article->setContent($data['content']);

            // Auto-update excerpt if not provided in data
            if (empty($data['excerpt'])) {
                $article->setExcerpt(\Ogan\Util\Text::excerpt($data['content'], 150));
            }
        }

        if (array_key_exists('category_id', $data)) {
            $article->setCategoryId((int)$data['category_id']);
        }

        if (array_key_exists('featured_image_id', $data)) {
            $article->setFeaturedImageId((int)$data['featured_image_id']);
        }

        if (isset($data['status'])) {
            $article->setStatus($data['status']);
            if ($data['status'] === ArticleStatus::PUBLISHED->value && !$article->getPublishedAt()) {
                $article->setPublishedAt(new \DateTime());
            }
        }

        $article->setUpdatedAt(new \DateTime());
        $article->save();

        // Sync tags if provided
        if (isset($data['tags'])) {
            $tags = is_string($data['tags']) ? explode(',', $data['tags']) : $data['tags'];
            // Ensure array and filter empty
            $tags = array_filter((array) $tags);
            $this->syncTags($article, $tags);
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
