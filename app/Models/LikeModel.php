<?php

namespace App\Models;
use Nette;

final class LikeModel
{
    public function __construct(private Nette\Database\Explorer $database) {}
    public function getAllPaginated(int $userId, int $offset, int $limit)
    {
        $query = $this->database->query("
        SELECT *
        FROM (
            SELECT
                'post' AS type,
                JSON_OBJECT(
                    'id', p.id,
                    'content', p.content,
                    'tags', JSON_ARRAY(p.tag1, p.tag2, p.tag3),
                    'created_at', p.created_at,
                    'image', p.image,
                    'username', u.username,
                    'profile_picture', u.profile_picture,
                    'tag_name_1', (SELECT t1.name FROM tags t1 WHERE t1.id = p.tag1),
                    'tag_name_2', (SELECT t2.name FROM tags t2 WHERE t2.id = p.tag2),
                    'tag_name_3', (SELECT t3.name FROM tags t3 WHERE t3.id = p.tag3),
                     'liked', EXISTS(
                        SELECT 1 FROM Likes WHERE user_id = ? AND liked_entity_id = p.id AND type = 'post'
                    )
                ) AS data,
                l.created_at AS like_created_at
            FROM Likes AS l
            JOIN Posts AS p ON l.liked_entity_id = p.id AND l.type = 'post'
            JOIN Users AS u ON p.creator = u.id
            WHERE l.user_id = ?

            UNION ALL

            SELECT
                'comment' AS type,
                JSON_OBJECT(
                    'id', c.id,
                    'content', c.content,
                    'comment_creator', u2.username,
                    'created_at', c.created_at,
                    'profile_picture', u2.profile_picture,
                    'liked', EXISTS(
                        SELECT 1 FROM Likes WHERE user_id = c.user_id AND type = 'comment' AND liked_entity_id = c.id
                    )
                ) AS data,
                l.created_at AS like_created_at
            FROM Likes AS l
            JOIN Comments AS c ON l.liked_entity_id = c.id AND l.type = 'comment'
            JOIN Users AS u2 ON c.user_id = u2.id
            WHERE l.user_id = ?
        ) AS combined_data
        ORDER BY like_created_at DESC
        LIMIT ? OFFSET ?;", $userId, $userId, $userId, $limit, $offset);

        return $query->fetchAll();
    }



    public function getTotalCountByUser(int $userId){
        $query = $this->database->table('likes')
            ->where('user_id', $userId)
            ->count();

        return $query;
    }

    public function hasUserLikedPost(int $userId, string $postId): bool
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