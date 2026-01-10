<?php

namespace App\Model;

use Ogan\Database\Model;
use App\Enum\CommentStatus;

class Comment extends Model
{
    protected static ?string $table = 'comments';
    protected static ?string $primaryKey = 'id';

    private ?int $id = null;
    private ?int $articleId = null;
    private ?int $userId = null;
    private ?string $authorName = null;
    private ?string $authorEmail = null;
    private ?string $content = null;
    private string $status = 'pending';
    private ?string $createdAt = null;

    // ─────────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticleId(): ?int
    {
        return $this->articleId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStatusEnum(): CommentStatus
    {
        return CommentStatus::from($this->status);
    }

    public function getCreatedAt(): ?\DateTime
    {
        if ($this->createdAt === null) {
            return null;
        }
        return new \DateTime($this->createdAt);
    }

    /**
     * Get display name (user name or author name)
     */
    public function getDisplayName(): string
    {
        if ($this->userId) {
            $user = $this->user();
            return $user ? $user->getName() : $this->authorName ?? 'Anonyme';
        }
        return $this->authorName ?? 'Anonyme';
    }

    // ─────────────────────────────────────────────────────────────
    // SETTERS
    // ─────────────────────────────────────────────────────────────

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setArticleId(?int $articleId): self
    {
        $this->articleId = $articleId;
        return $this;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setAuthorName(?string $authorName): self
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function setAuthorEmail(?string $authorEmail): self
    {
        $this->authorEmail = $authorEmail;
        return $this;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
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
    // STATUS HELPERS
    // ─────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === CommentStatus::PENDING->value;
    }

    public function isApproved(): bool
    {
        return $this->status === CommentStatus::APPROVED->value;
    }

    public function isRejected(): bool
    {
        return $this->status === CommentStatus::REJECTED->value;
    }

    // ─────────────────────────────────────────────────────────────
    // RELATIONS
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the article this comment belongs to
     */
    public function article(): ?Article
    {
        if (!$this->articleId) {
            return null;
        }
        return Article::find($this->articleId);
    }

    /**
     * Get formatted created date in French
     */
    public function getFormattedCreatedAt(string $format = 'd/m/Y'): ?string
    {
        $date = $this->getCreatedAt();

        if (!$date) {
            return null;
        }

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

    /**
     * Get the user who wrote this comment (if logged in)
     */
    public function user(): ?User
    {
        if (!$this->userId) {
            return null;
        }
        return User::find($this->userId);
    }
}
