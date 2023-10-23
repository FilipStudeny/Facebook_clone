<?php

namespace App\Models;
use Nette;

final class UserModel
{
    public function __construct(private Nette\Database\Explorer $database) {}

    public function getUser(string $username): ?Nette\Database\Table\ActiveRow
    {
        return $this->database->table('Users')->where('username', $username)->fetch();
    }

}