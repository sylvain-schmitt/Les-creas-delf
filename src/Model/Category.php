<?php

namespace App\Model;

use Ogan\Database\Model;
use Ogan\Database\Trait\HasSlug;

class Category extends Model
{
    use HasSlug;

    protected static ?string $table = 'categories';
    protected static ?string $primaryKey = 'id';

    // Source field for slug generation (constant to avoid DB persistence)
    protected const SLUG_SOURCE = 'name';

    /**
     * Get the slug source field (for HasSlug trait)
     */
    protected function getSlugSource(): string
    {
        return self::SLUG_SOURCE;
    }

    private ?int $id = null;
    private ?string $name = null;
    private ?string $slug = null;
    private ?string $description = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    // ─────────────────────────────────────────────────────────────
    // ATTRIBUTE ACCESS (required by HasSlug trait)
    // ─────────────────────────────────────────────────────────────

    public function getAttribute(string $key): mixed
    {
        $getter = 'get' . ucfirst($key);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        return $this->$key ?? null;
    }

    public function setAttribute(string $key, mixed $value): static
    {
        $setter = 'set' . ucfirst($key);
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        }
        $this->$key = $value;
        return $this;
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;
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
    // RELATIONS
    // ─────────────────────────────────────────────────────────────

    /**
     * Get all articles in this category
     */
    public function articles(): array
    {
        return Article::where('category_id', '=', $this->id)->get();
    }

    /**
     * Count articles in this category
     */
    public function articlesCount(): int
    {
        return Article::where('category_id', '=', $this->id)->count();
    }

    /**
     * Find by slug (override to fix return type)
     */
    public static function findBySlug(string $slug): ?static
    {
        $result = static::where('slug', '=', $slug)->first();
        return $result instanceof static ? $result : null;
    }
}
