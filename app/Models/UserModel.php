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

    public function getAllUsers(){
        $query = $this->database->query('SELECT * FROM users');
        return $query->fetchAll();
    }

    public function getUserById(int $id): ?Nette\Database\Table\ActiveRow
    {
        return $this->database->table('Users')->where('id', $id)->fetch();
    }

    public function setUserRole(string $username, string $role){
        $this->database->table('Users')->where('username', $username)->update(['role' => $role]);
    }

    public function setUserBan(string $username, int $ban){
        $this->database->table('Users')->where('username', $username)->update(['banned' => $ban]);
    }

    public function updateUser(string $username, array $data){
        $this->database->table('Users')->where('username', $username)->update($data);
    }

    public function getTotalCount()
    {
        return $this->database->table('users')->count('id');
    }

    public function getUserIdByUsername(string $username): ?int
    {
        $user = $this->getUser($username);
        return $user ? $user->id : null;
    }

    public function updateProfilePicture(int $userId, string $image){
        return $this->database->table('Users')->where('id', $userId)->update(['profile_picture' => $image]);

    }

}