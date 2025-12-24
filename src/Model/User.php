<?php

namespace App\Model;

use Ogan\Database\Model;
use Ogan\Security\UserInterface;

class User extends Model implements UserInterface
{
    protected static ?string $table = 'users';
    protected static ?string $primaryKey = 'id';

    private ?int $id = null;
    private ?string $email = null;
    private ?string $password = null;
    private ?string $name = null;
    private array $roles = ['ROLE_USER'];
    private ?string $emailVerifiedAt = null;
    private ?string $emailVerificationToken = null;
    private ?string $passwordResetToken = null;
    private ?string $passwordResetExpiresAt = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    // ─────────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function getEmailVerifiedAt(): ?string
    {
        return $this->emailVerifiedAt;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function getPasswordResetExpiresAt(): ?string
    {
        return $this->passwordResetExpiresAt;
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

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles ?? ['ROLE_USER'];
        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function setEmailVerifiedAt(?string $date): self
    {
        $this->emailVerifiedAt = $date;
        return $this;
    }

    public function setEmailVerificationToken(?string $token): self
    {
        $this->emailVerificationToken = $token;
        return $this;
    }

    public function setPasswordResetToken(?string $token): self
    {
        $this->passwordResetToken = $token;
        return $this;
    }

    public function setPasswordResetExpiresAt(?string $datetime): self
    {
        $this->passwordResetExpiresAt = $datetime;
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
    // MÉTHODES DE REQUÊTE
    // ─────────────────────────────────────────────────────────────

    public static function findByEmail(string $email): ?self
    {
        $result = self::where('email', '=', $email)->first();

        if ($result === null) {
            return null;
        }

        $user = new static($result);
        $user->exists = true;
        $user->hydrateFromAttributes();
        return $user;
    }

    public function isVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }
}