<?php

namespace App\Models;

use Nette;

final class PostModel
{
    public function __construct(private Nette\Database\Explorer $database) {}

    public function getAllPaginated(int $userId, int $offset, int $limit)
    {
        $query = $this->database->query("
        SELECT 
            p.id,
            p.content,
            (SELECT t1.name FROM tags t1 WHERE t1.id = p.tag1) as tag_name_1,
            (SELECT t2.name FROM tags t2 WHERE t2.id = p.tag2) as tag_name_2,
            (SELECT t3.name FROM tags t3 WHERE t3.id = p.tag3) as tag_name_3,
            p.created_at,
            p.image,
            u.username,
            u.profile_picture,
            EXISTS(
                SELECT 1 
                FROM Likes 
                WHERE user_id = ? AND liked_entity_id = p.id AND type = 'post'
            ) as liked
        FROM posts p 
        JOIN users u ON p.creator = u.id 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?", $userId, $limit, $offset);

        return $query->fetchAll();
    }

    public function getAllPaginatedByUser(int $userId, int $offset, int $limit)
    {
        $query = $this->database->query("
        SELECT 
            p.id,
            p.content,
            (SELECT t1.name FROM tags t1 WHERE t1.id = p.tag1) as tag_name_1,
            (SELECT t2.name FROM tags t2 WHERE t2.id = p.tag2) as tag_name_2,
            (SELECT t3.name FROM tags t3 WHERE t3.id = p.tag3) as tag_name_3,
            p.created_at,
            p.image,
            u.username,
            u.profile_picture,
            EXISTS(
                SELECT 1 
                FROM Likes 
                WHERE user_id = ? AND liked_entity_id = p.id AND type = 'post'
            ) as liked
        FROM posts p 
        JOIN users u ON p.creator = u.id 
        WHERE p.creator = ?
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?", $userId, $userId, $limit, $offset);

        return $query->fetchAll();
    }

    public function getTotalCount()
    {
        return $this->database->table('posts')->count('*');
    }

    public function getTotalCountByTag($tag)
    {
        $query = $this->database->table('posts')
            ->where('(tag1 IN (SELECT id FROM tags WHERE name = ?) OR tag2 IN (SELECT id FROM tags WHERE name = ?) OR tag3 IN (SELECT id FROM tags WHERE name = ?))', $tag, $tag, $tag)
            ->count('*');

        return $query;
    }

    public function getTotalCountByUser(string $username){
        $user = $this->database->table('users')->where('username', $username)->fetch();
        if(!$user){
            return 0;
        }
        $userId = $user->id;
        $query = $this->database->table('posts')
            ->where('creator', $userId)
            ->count();

        return $query;
    }

    public function getTags()
    {
        $tags = $this->database->table('tags')->fetchAll();
        return $tags;
    }

    public function get(int $postId, int $userId) {

        $post = $this->database->query("
            SELECT p.*, u.username, u.email, u.profile_picture, 
            (SELECT t1.name FROM tags t1 WHERE t1.id = p.tag1) as tag_name_1, 
            (SELECT t2.name FROM tags t2 WHERE t2.id = p.tag2) as tag_name_2, 
            (SELECT t3.name FROM tags t3 WHERE t3.id = p.tag3) as tag_name_3,
            (CASE WHEN EXISTS (SELECT 1 FROM likes WHERE user_id = ? AND type = 'post' AND liked_entity_id = p.id) THEN 1 ELSE 0 END) as liked
            FROM posts p 
            JOIN users u ON p.creator = u.id 
            WHERE p.id = ?", $userId, $postId)
            ->fetch();

        return $post;
    }

    public function getByTagPaginated(int $userId, string $tag, int $offset, int $limit)
    {
        $query = $this->database->query("
            SELECT p.*, u.username, u.profile_picture, 
            t1.name as tag_name_1, 
            t2.name as tag_name_2, 
            t3.name as tag_name_3,
            EXISTS(
                SELECT 1 
                FROM Likes 
                WHERE user_id = ? AND liked_entity_id = p.id AND type = 'post'
            ) as liked
            FROM posts p 
            JOIN users u ON p.creator = u.id 
            LEFT JOIN tags t1 ON p.tag1 = t1.id
            LEFT JOIN tags t2 ON p.tag2 = t2.id
            LEFT JOIN tags t3 ON p.tag3 = t3.id
            WHERE t1.name = ? OR t2.name = ? OR t3.name = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?", $userId ,$tag, $tag, $tag, $limit, $offset);

        return $query->fetchAll();
    }
}