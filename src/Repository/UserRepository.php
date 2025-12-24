<?php

namespace App\Repository;

use App\Model\User;
use Ogan\Database\AbstractRepository;

class UserRepository extends AbstractRepository
{
    protected string $entityClass = User::class;
    protected string $table = 'users';

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
}