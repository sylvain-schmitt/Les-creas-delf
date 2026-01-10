<?php

namespace App\Model;

use Ogan\Database\Model;

class Media extends Model
{
    protected static ?string $table = 'media';
    protected static ?string $primaryKey = 'id';

    private ?int $id = null;
    private ?string $filename = null;
    private ?string $originalName = null;
    private ?string $path = null;
    private ?string $mimeType = null;
    private ?int $size = null;
    private ?string $alt = null;
    private ?int $userId = null;
    private ?string $createdAt = null;

    // ─────────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Get the full URL to the media file
     */
    public function getUrl(): string
    {
        return '/uploads/' . $this->path . '/original.webp';
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(): string
    {
        return '/uploads/' . $this->path . '/original_thumbnail.webp';
    }

    /**
     * Get medium size URL
     */
    public function getMediumUrl(): string
    {
        return '/uploads/' . $this->path . '/original_medium.webp';
    }

    /**
     * Get large size URL
     */
    public function getLargeUrl(): string
    {
        return '/uploads/' . $this->path . '/original_large.webp';
    }

    /**
     * Get human readable file size
     */
    public function getHumanSize(): string
    {
        $bytes = $this->size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // ─────────────────────────────────────────────────────────────
    // SETTERS
    // ─────────────────────────────────────────────────────────────

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;
        return $this;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
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
    // RELATIONS
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the user who uploaded this media
     */
    public function user(): ?User
    {
        if (!$this->userId) {
            return null;
        }
        return User::find($this->userId);
    }

    /**
     * Check if media is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mimeType ?? '', 'image/');
    }
}
