<?php

require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class Chat implements MessageComponentInterface
{
    protected $clients;
    protected $users;
    protected $userConnections;
    protected $database;
    protected $rooms;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->users = [];
        $this->userConnections = [];
        $this->database = new PDO('mysql:host=localhost;dbname=socialapp', 'root', '');
        $this->rooms = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if ($data['action'] === 'registerUser') {
            $this->registerUser($from, $data['username']);
            $this->loadMessages($from, $data['username']);
            return;
        }

        if ($data['action'] === 'createRoom') {
            $recipient = $data['recipient'];
            $this->createRoom($from, $recipient);
            $this->loadPreviousMessages($from, $data['room']);
            return;
        }

        if ($data['action'] === 'sendMessage') {
            $room = $data['room'];
            $message = $data['message'];
            $recipient = $data['recipient'];
            $sender = $this->users[$from->resourceId];

            if ($this->roomExists($room) && $this->userInRoom($from, $room)) {
                $this->sendMessage($room, $sender, $message);
                $this->saveMessage($room, $sender, $recipient, $message);
            } else {
                $this->sendMessage($room, $sender, "Room not found or you are not a member of the room: {$room}");
            }

            return;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->removeUser($conn);
        $this->leaveRoom($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function registerUser(ConnectionInterface $conn, $username)
    {
        $this->users[$conn->resourceId] = $username;
        $this->userConnections[$username] = $conn;
        echo "User {$username} registered\n";
        $this->loadMessages($conn, $username); // Load messages for the user
    }

    protected function removeUser(ConnectionInterface $conn)
    {
        if (isset($this->users[$conn->resourceId])) {
            $username = $this->users[$conn->resourceId];
            unset($this->users[$conn->resourceId]);
            unset($this->userConnections[$username]);
            echo "User {$username} removed\n";
        }
    }

    protected function createRoom(ConnectionInterface $conn, $recipient)
    {
        $sender = $this->users[$conn->resourceId];
        $room = $this->generateRoomId($sender, $recipient);

        if (!$this->roomExists($room)) {
            $this->rooms[$room] = new \SplObjectStorage();
        }

        $this->rooms[$room]->attach($conn);
        $conn->room = $room;
        echo "Room created: {$room}\n";
    }

    protected function leaveRoom(ConnectionInterface $conn)
    {
        if (isset($conn->room) && isset($this->rooms[$conn->room])) {
            $this->rooms[$conn->room]->detach($conn);
            echo "Connection {$conn->resourceId} has left room: {$conn->room}\n";
        }
    }

    protected function roomExists($room)
    {
        return isset($this->rooms[$room]);
    }

    protected function userInRoom(ConnectionInterface $conn, $room)
    {
        return isset($conn->room) && $conn->room === $room;
    }

    protected function generateRoomId($userId1, $userId2)
    {
        $userIds = [$userId1, $userId2];
        sort($userIds);
        return implode('_', $userIds);
    }

    protected function sendMessage($room, $sender, $message)
    {
        foreach ($this->rooms[$room] as $client) {
            if ($client !== $sender) {
                $client->send(json_encode([
                    'sender' => $sender,
                    'message' => $message
                ]));
            }
        }
    }

    protected function saveMessage($room, $sender, $recipient, $message)
    {
        $query = "INSERT INTO messages (room, sender, recipient, message) VALUES (:room, :sender, :recipient, :message)";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':room', $room);
        $statement->bindValue(':sender', $sender);
        $statement->bindValue(':recipient', $recipient);
        $statement->bindValue(':message', $message);
        $statement->execute();
    }

    protected function loadPreviousMessages(ConnectionInterface $connection, string $roomID){
        $query = "SELECT sender, message, room FROM messages WHERE room = :room";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':room', $roomID);
        $statement->execute();
        $messages = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as $message) {
            $sender = $message['sender'];
            $content = $message['message'];
            $room = $message['room'];

            if ($this->userInRoom($connection, $room)) {
                $connection->send(json_encode([
                    'sender' => $sender,
                    'message' => $content
                ]));
            }
        }
    }

    protected function loadMessages(ConnectionInterface $conn, $username)
    {
        $query = "SELECT sender, message, room FROM messages WHERE recipient = :username";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $messages = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as $message) {
            $sender = $message['sender'];
            $content = $message['message'];
            $room = $message['room'];

            if ($this->userInRoom($conn, $room)) {
                $conn->send(json_encode([
                    'sender' => $sender,
                    'message' => $content
                ]));
            }
        }
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

echo "Server started\n";

$server->run();