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
        $this->database = new PDO('mysql:host=localhost;dbname=facefook', 'root', '');
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
        $roomId = $data['room'];
        $sender_id = $data['sender_id'];
        $recipient_id = $data['recipient_id'];

        if ($data['action'] === 'registerUser') {
            $this->registerUser($from, $data['username'], $roomId, $sender_id, $recipient_id);
            $this->loadMessages($from, $data['username'], $roomId, $sender_id, $recipient_id);
            return;
        }

        if ($data['action'] === 'createRoom') {
            $recipient = $data['recipient'];
            $this->createRoom($from, $recipient, $roomId);
            $this->loadPreviousMessages($from, $data['room']);
            return;
        }

        if ($data['action'] === 'sendMessage') {
            $room = $data['room'];
            $message = $data['message'];
            $recipient = $data['recipient'];
            $sender = $data['sender'];
            //$sender = $this->users[$from->resourceId];

            $time_of_creation = (new DateTime())->format('Y-m-d H:i:s');
            if ($this->roomExists($room) && $this->userInRoom($from, $room)) {
                $this->sendMessage($room, $sender, $message, $time_of_creation);
                $this->saveMessage($room, $sender, $recipient, $message, $sender_id, $recipient_id, $time_of_creation);
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

    protected function registerUser(ConnectionInterface $conn, $username, string $roomId, $sender_id, $recipient_id)
    {
        $this->users[$conn->resourceId] = $username;
        $this->userConnections[$username] = $conn;
        echo "User {$username} registered\n";
        $this->loadMessages($conn, $username, $roomId, $sender_id, $recipient_id); // Load messages for the user
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

    protected function createRoom(ConnectionInterface $conn, $recipient, $roomId)
    {
        $sender = $this->users[$conn->resourceId];
        $room = $roomId;

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

    protected function sendMessage($room, $sender, $message, $time_of_creation)
    {
        foreach ($this->rooms[$room] as $client) {
            if ($client !== $sender) {
                $client->send(json_encode([
                    'sender' => $sender,
                    'message' => $message,
                    'time' => $time_of_creation
                ]));
            }
        }
    }

    protected function saveMessage($room, $sender, $recipient, $message, $sender_id, $recipient_id, $time_of_creation)
    {
        $query = "INSERT INTO $room (sender_id, recipient_id, message, time_of_creation) VALUES (:sender_id, :recipient_id, :message, :time_of_creation)";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':sender_id', $sender_id);
        $statement->bindValue(':recipient_id', $recipient_id);
        $statement->bindValue(':message', $message);
        $statement->bindValue(':time_of_creation', $time_of_creation);

        $statement->execute();
    }

    protected function loadPreviousMessages(ConnectionInterface $connection, string $room)
    {
        $query = "SELECT u.username as sender, m.message, m.time_of_creation 
              FROM $room m
              INNER JOIN users u ON u.id = m.sender_id";
        $statement = $this->database->prepare($query);
        $statement->execute();
        $messages = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as $message) {
            $sender = $message['sender'];
            $content = $message['message'];
            $timeOfCreation = $message['time_of_creation'];

            if ($this->userInRoom($connection, $room)) {
                $connection->send(json_encode([
                    'sender' => $sender,
                    'message' => $content,
                    'time' => $timeOfCreation
                ]));
            }
        }
    }

    protected function loadMessages(ConnectionInterface $conn, $username, $room, $sender_id, $recipient_id)
    {
        $query = "SELECT sender_id, message FROM $room WHERE recipient_id = :recipient_id";
        $statement = $this->database->prepare($query);
        $statement->bindValue(':recipient_id', (int)$recipient_id);
        $statement->execute();
        $messages = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as $message) {
            $sender = $message['sender_id'];
            $content = $message['message'];

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