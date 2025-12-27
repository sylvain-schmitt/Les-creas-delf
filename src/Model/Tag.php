<?php

namespace App\Model;

use Ogan\Database\Model;
use Ogan\Database\Trait\HasSlug;

class Tag extends Model
{
    use HasSlug;

    protected static ?string $table = 'tags';
    protected static ?string $primaryKey = 'id';

    // Source field for slug generation
    protected string $slugSource = 'name';

    private ?int $id = null;
    private ?string $name = null;
    private ?string $slug = null;
    private ?string $color = '#C07459'; // Default terracotta color
    private ?string $createdAt = null;

    // ─────────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    // ─────────────────────────────────────────────────────────────
    // SETTERS
    // ─────────────────────────────────────────────────────────────

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;
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

    // ─────────────────────────────────────────────────────────────
    // RELATIONS (ORM style)
    // ─────────────────────────────────────────────────────────────

    /**
     * Get all articles with this tag (ManyToMany)
     */
    public function getArticles(): \Ogan\Database\Relations\ManyToMany
    {
        return $this->manyToMany(
            Article::class,
            'article_tag',
            'tag_id',
            'article_id'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────────────────────

    /**
     * Get articles array
     */
    public function articles(): array
    {
        return $this->getArticles()->getResults();
    }

    /**
     * Count articles with this tag
     */
    public function articlesCount(): int
    {
        return $this->getArticles()->count();
    }
}
