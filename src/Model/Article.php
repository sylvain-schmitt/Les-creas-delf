<?php

namespace App\Model;

use Ogan\Database\Model;
use Ogan\Database\Trait\HasSlug;
use App\Enum\ArticleStatus;

class Article extends Model
{
    use HasSlug;

    protected static ?string $table = 'articles';
    protected static ?string $primaryKey = 'id';

    // Source field for slug generation (constant to avoid DB persistence)
    protected const SLUG_SOURCE = 'title';

    /**
     * Get the slug source field (for HasSlug trait)
     */
    protected function getSlugSource(): string
    {
        return self::SLUG_SOURCE;
    }

    /**
     * Get an attribute by name (required by HasSlug trait)
     */
    /**
     * Get an attribute by name (required by HasSlug trait)
     */
    public function getAttribute(string $key): mixed
    {
        // 1. Try direct property
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        // 2. Try CamelCase getter (category_id -> getCategoryId)
        $camelKey = str_replace('_', '', ucwords($key, '_'));
        $getter = 'get' . $camelKey;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        return $this->$key ?? null;
    }

    /**
     * Set an attribute by name (required by HasSlug trait)
     */
    public function setAttribute(string $key, mixed $value): self
    {
        // 1. Try CamelCase setter (category_id -> setCategoryId)
        $camelKey = str_replace('_', '', ucwords($key, '_'));
        $setter = 'set' . $camelKey;

        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        }

        // 2. Default to dynamic property
        $this->$key = $value;
        return $this;
    }

    /**
     * Override findBySlug to ensure proper return type
     */
    public static function findBySlug(string $slug): ?self
    {
        $result = static::where('slug', '=', $slug)->first();

        if (is_array($result)) {
            return new self($result);
        }

        return $result instanceof self ? $result : null;
    }

    // Propriétés privées (pattern User)
    private ?int $id = null;
    private ?string $title = null;
    private ?string $slug = null;
    private ?string $excerpt = null;
    private ?string $content = null;
    private ?int $featuredImageId = null;
    private ?int $categoryId = null;
    private ?int $userId = null;
    private string $status = 'draft';
    private ?string $publishedAt = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    // ─────────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    /**
     * Get excerpt or generate from content if empty
     */
    public function getExcerptOrGenerate(int $length = 150): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }
        return \Ogan\Util\Text::excerpt($this->content ?? '', $length);
    }

    /**
     * Get reading time formatted string
     */
    public function getReadingTime(): string
    {
        return \Ogan\Util\Text::readingTimeFormatted($this->content ?? '');
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getFeaturedImageId(): ?int
    {
        return $this->featuredImageId;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getStatus(): string
    {
        return $this->status ?? 'draft';
    }

    public function getStatusEnum(): ArticleStatus
    {
        return ArticleStatus::from($this->status ?? 'draft');
    }

    public function getPublishedAt(): ?string
    {
        return $this->publishedAt;
    }

    /**
     * Get formatted published date in French
     */
    public function getFormattedPublishedAt(string $format = 'd F Y'): ?string
    {
        if (!$this->publishedAt) {
            return null;
        }

        $date = new \DateTime($this->publishedAt);

        // Mois en français
        $months = [
            'January' => 'janvier',
            'February' => 'février',
            'March' => 'mars',
            'April' => 'avril',
            'May' => 'mai',
            'June' => 'juin',
            'July' => 'juillet',
            'August' => 'août',
            'September' => 'septembre',
            'October' => 'octobre',
            'November' => 'novembre',
            'December' => 'décembre'
        ];

        $formatted = $date->format($format);
        return str_replace(array_keys($months), array_values($months), $formatted);
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    // ─────────────────────────────────────────────────────────────
    // SETTERS
    // ─────────────────────────────────────────────────────────────

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt;
        return $this;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setFeaturedImageId(?int $featuredImageId): self
    {
        $this->featuredImageId = $featuredImageId;
        return $this;
    }

    public function setCategoryId(?int $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setPublishedAt(string|\DateTime|null $publishedAt): self
    {
        if ($publishedAt instanceof \DateTime) {
            $this->publishedAt = $publishedAt->format('Y-m-d H:i:s');
        } else {
            $this->publishedAt = $publishedAt;
        }
        return $this;
    }

    public function setCreatedAt(string|\DateTime|null $createdAt): self
    {
        if ($createdAt instanceof \DateTime) {
            $this->createdAt = $createdAt->format('Y-m-d H:i:s');
        } else {
            $this->createdAt = $createdAt;
        }
        return $this;
    }

    public function setUpdatedAt(string|\DateTime|null $updatedAt): self
    {
        if ($updatedAt instanceof \DateTime) {
            $this->updatedAt = $updatedAt->format('Y-m-d H:i:s');
        } else {
            $this->updatedAt = $updatedAt;
        }
        return $this;
    }

    // ─────────────────────────────────────────────────────────────
    // STATUS HELPERS
    // ─────────────────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === ArticleStatus::DRAFT->value;
    }

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::PUBLISHED->value;
    }

    public function isArchived(): bool
    {
        return $this->status === ArticleStatus::ARCHIVED->value;
    }

    // ─────────────────────────────────────────────────────────────
    // MAGIC METHODS (Pour compatibilité Twig/Templates)
    // ─────────────────────────────────────────────────────────────



    // ─────────────────────────────────────────────────────────────
    // RELATIONS (ORM style)
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the article's category (ManyToOne) - Relation definition
     */
    public function categoryRelation(): \Ogan\Database\Relations\ManyToOne
    {
        return $this->manyToOne(Category::class, 'category_id');
    }

    /**
     * Get the article's author (ManyToOne) - Relation definition
     */
    public function authorRelation(): \Ogan\Database\Relations\ManyToOne
    {
        return $this->manyToOne(User::class, 'user_id');
    }

    /**
     * Get the featured image (ManyToOne) - Relation definition
     */
    public function featuredImageRelation(): \Ogan\Database\Relations\ManyToOne
    {
        return $this->manyToOne(Media::class, 'featured_image_id');
    }

    /**
     * Get all tags for this article (ManyToMany) - Relation definition
     */
    public function tagsRelation(): \Ogan\Database\Relations\ManyToMany
    {
        return $this->manyToMany(
            Tag::class,
            'article_tag',
            'article_id',
            'tag_id'
        );
    }

    /**
     * Get all comments for this article (OneToMany) - Relation definition
     */
    public function commentsRelation(): \Ogan\Database\Relations\OneToMany
    {
        return $this->oneToMany(Comment::class, 'article_id');
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER METHODS (Accessors for Templates/Magic Props)
    // ─────────────────────────────────────────────────────────────

    /**
     * Get category instance
     */
    public function getCategory(): ?Category
    {
        return $this->categoryRelation()->getResults();
    }

    /**
     * Get author instance
     */
    public function getAuthor(): ?User
    {
        return $this->authorRelation()->getResults();
    }

    /**
     * Get featured image instance
     */
    public function getFeaturedImage(): ?Media
    {
        return $this->featuredImageRelation()->getResults();
    }

    /**
     * Get tags array
     */
    public function getTags(): array
    {
        return $this->tagsRelation()->getResults();
    }

    /**
     * Add a tag to the article
     */
    public function addTag(Tag $tag): bool
    {
        if (!$this->id) {
            return false;
        }
        return $this->tagsRelation()->attach($tag->getId());
    }

    /**
     * Get approved comments
     */
    public function getComments(): array
    {
        return $this->commentsRelation()
            ->where('status', '=', 'approved')
            ->orderBy('created_at', 'DESC')
            ->getResults();
    }

    /**
     * Count approved comments
     */
    /**
     * Count approved comments
     */
    public function commentsCount(): int
    {
        $pdo = \Ogan\Database\Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE article_id = ? AND status = 'approved'");
        $stmt->execute([$this->id]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Public wrapper for protected hydrate method
     */
    public static function hydrateResults(array $results): array
    {
        return parent::hydrate($results);
    }
}
