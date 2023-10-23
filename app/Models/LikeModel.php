<?php

namespace App\Models;
use Nette;

final class LikeModel
{
    public function __construct(private Nette\Database\Explorer $database) {}

    public function hasUserLikedPost(int $userId, int $postId): bool
    {
        $result = $this->database->table('Likes')
            ->where('user_id', $userId)
            ->where('type', 'post')
            ->where('liked_entity_id', $postId)
            ->fetch();

        return (bool)$result;
    }

    public function hasUserLikedComment(int $userId, int $postId): bool
    {
        $result = $this->database->table('Likes')
            ->where('user_id', $userId)
            ->where('type', 'comment')
            ->where('liked_entity_id', $postId)
            ->fetch();

        return (bool)$result;
    }

    public function deleteLike(int $userId, string $type, int $entityId): void{
        $existingLike = $this->database->table('Likes')
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('liked_entity_id', $entityId)
            ->fetch();

        $existingLike->delete();
    }
}