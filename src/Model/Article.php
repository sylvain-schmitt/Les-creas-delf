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

    // Source field for slug generation
    protected string $slugSource = 'title';

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
        return $this->status;
    }

    public function getStatusEnum(): ArticleStatus
    {
        return ArticleStatus::from($this->status);
    }

    public function getPublishedAt(): ?string
    {
        return $this->publishedAt;
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
    // RELATIONS (ORM style)
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the article's category (ManyToOne)
     */
    public function getCategory(): \Ogan\Database\Relations\ManyToOne
    {
        return $this->manyToOne(Category::class, 'category_id');
    }

    /**
     * Get the article's author (ManyToOne)
     */
    public function getAuthor(): \Ogan\Database\Relations\ManyToOne
    {
        return $this->manyToOne(User::class, 'user_id');
    }

    /**
     * Get the featured image (ManyToOne)
     */
    public function getFeaturedImage(): \Ogan\Database\Relations\ManyToOne
    {
        return $this->manyToOne(Media::class, 'featured_image_id');
    }

    /**
     * Get all tags for this article (ManyToMany)
     */
    public function getTags(): \Ogan\Database\Relations\ManyToMany
    {
        return $this->manyToMany(
            Tag::class,
            'article_tag',
            'article_id',
            'tag_id'
        );
    }

    /**
     * Get all comments for this article (OneToMany)
     */
    public function getComments(): \Ogan\Database\Relations\OneToMany
    {
        return $this->oneToMany(Comment::class, 'article_id');
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────────────────────

    /**
     * Get category instance
     */
    public function category(): ?Category
    {
        return $this->getCategory()->getResults();
    }

    /**
     * Get author instance
     */
    public function author(): ?User
    {
        return $this->getAuthor()->getResults();
    }

    /**
     * Get featured image instance
     */
    public function featuredImage(): ?Media
    {
        return $this->getFeaturedImage()->getResults();
    }

    /**
     * Get tags array
     */
    public function tags(): array
    {
        return $this->getTags()->getResults();
    }

    /**
     * Get approved comments
     */
    public function comments(): array
    {
        return $this->getComments()
            ->where('status', '=', 'approved')
            ->orderBy('created_at', 'DESC')
            ->getResults();
    }

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
}
