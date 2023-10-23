<?php

namespace App\Models;
use Nette;
final class CommentsModel
{
    public function __construct(private Nette\Database\Explorer $database) {}

    public function getCommentsByPostId(int $postId, int $loggedInUserId)
    {
        $userId = $loggedInUserId;

        $comments = $this->database->query("
        SELECT c.*, u.username AS comment_creator,
        EXISTS(SELECT 1 FROM Likes WHERE user_id = ? AND type = 'comment' AND liked_entity_id = c.id) AS liked
        FROM Comments c JOIN Users u ON c.user_id = u.id 
        WHERE c.post_id = ? ORDER BY created_at DESC", $userId, $postId);

        return $comments->fetchAll();
    }

    public function create(string $content, int $user_id, \DateTime $created_at, int $post_id){
        $this->database->table('Comments')->insert([
            'content' => $content,
            'user_id' => $user_id,
            'post_id' => $post_id,
            'created_at' => $created_at
        ]);
    }
}