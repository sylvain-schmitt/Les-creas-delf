<?php

namespace App\Model;

use Ogan\Database\Model;

class Setting extends Model
{
    protected static ?string $table = 'settings';
    protected static ?string $primaryKey = 'id';

    private ?int $id = null;
    private ?string $key = null;
    private ?string $value = null;
    private string $type = 'text';
    private string $groupName = 'general';
    private ?string $label = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    // ─────────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function getLabel(): ?string
    {
        return $this->label;
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

    public function setKey(?string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setGroupName(string $groupName): self
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;
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
    // HELPER D'HYDRATATION
    // ─────────────────────────────────────────────────────────────

    /**
     * Marquer l'entité comme existante en BDD
     * Utilisé lors de l'hydratation depuis le Repository
     */
    public function markAsExisting(): self
    {
        $this->exists = true;
        return $this;
    }
}
