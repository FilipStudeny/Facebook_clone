<?php


namespace App\Models;

use Nette;
use Nette\Utils\DateTime;

final class MessagesModel
{
    public function __construct(private Nette\Database\Explorer $database)
    {
    }

    public function getChatRooms(int $userId): array
    {
        return $this->database->query("
        SELECT 
            chats.*, 
            CASE 
                WHEN user1_id = ? THEN (SELECT username FROM users WHERE users.id = chats.user2_id)
                ELSE (SELECT username FROM users WHERE users.id = chats.user1_id)
            END AS username,
            CASE 
                WHEN user1_id = ? THEN (SELECT profile_picture FROM users WHERE users.id = chats.user2_id)
                ELSE (SELECT profile_picture FROM users WHERE users.id = chats.user1_id)
            END AS profile_picture
        FROM 
            chats
        WHERE 
            user1_id = ? OR user2_id = ?;
    ", $userId, $userId, $userId, $userId)->fetchAll();
    }

    public function createRoom(int $user1, int $user2, string $room_name){

        $createdAt = new DateTime();

        $this->database->table('chats')->insert([
            'user1_id' => $user1,
            'user2_id' => $user2,
            'time_of_creation' => $createdAt,
            'room_name' => $room_name,
            'last_message' => "No new message"
        ]);
    }
    public function createMessagesTable(string $tableName): void
    {
        $this->database->query("
        CREATE TABLE $tableName (
            id INT AUTO_INCREMENT,
            sender_id INT NOT NULL,
            recipient_id INT NOT NULL,
            message TEXT NOT NULL,
            time_of_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (sender_id) REFERENCES Users(id),
            FOREIGN KEY (recipient_id) REFERENCES Users(id)
            )
        ");
    }

    public function chatRoomExists(string $roomName): bool
    {
        return (bool) $this->database->table('chats')->where('room_name', $roomName)->fetch();
    }

    public function messageTableExists(string $tableName): bool
    {
        $result = $this->database->query("SHOW TABLES LIKE '$tableName'")->fetch();
        return (bool)$result;
    }
}