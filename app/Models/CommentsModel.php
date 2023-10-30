<?php

namespace App\Models;
use Nette;
final class CommentsModel
{
    public function __construct(private Nette\Database\Explorer $database) {}

    public function getAllPaginated(int $userId, int $offset, int $limit)
    {
        $query = $this->database->query("
        SELECT c.*, u.username AS comment_creator, u.profile_picture,
        EXISTS(SELECT 1 FROM Likes WHERE user_id = c.user_id AND type = 'comment' AND liked_entity_id = c.id) AS liked
        FROM Comments c 
        JOIN Users u ON c.user_id = u.id 
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC 
        LIMIT ? OFFSET ?", $userId, $limit, $offset);

        return $query->fetchAll();
    }

    public function getCommentsByPostId(int $postId, int $loggedInUserId)
    {
        $userId = $loggedInUserId;

        $comments = $this->database->query("
        SELECT c.*, u.username AS comment_creator, u.profile_picture, 
        EXISTS(SELECT 1 FROM Likes WHERE user_id = ? AND type = 'comment' AND liked_entity_id = c.id) AS liked
        FROM Comments c JOIN Users u ON c.user_id = u.id 
        WHERE c.post_id = ? ORDER BY created_at DESC", $userId, $postId);

        return $comments->fetchAll();
    }

    public function getCommentsByUser(int $userId)
    {
        $comments = $this->database->query("
        SELECT c.*, u.username AS comment_creator,
        EXISTS(SELECT 1 FROM Likes WHERE user_id = c.user_id AND type = 'comment' AND liked_entity_id = c.id) AS liked
        FROM Comments c JOIN Users u ON c.user_id = u.id 
        WHERE c.user_id = ? ORDER BY c.created_at DESC", $userId);

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

    public function getTotalCountByUser(string $username){
        $user = $this->database->table('users')->where('username', $username)->fetch();
        if(!$user){
            return 0;
        }
        $userId = $user->id;
        $query = $this->database->table('comments')
            ->where('user_id', $userId)
            ->count();

        return $query;
    }
}