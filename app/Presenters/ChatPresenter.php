<?php

namespace App\Presenters;

use App\CustomAuthenticator;
use App\Models\CommentsModel;
use App\Models\LikeModel;
use App\Models\MessagesModel;
use App\Models\PostModel;
use App\Models\UserModel;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
class ChatPresenter extends Presenter
{
    public function __construct(private Explorer $database, public CustomAuthenticator $authenticator,
                                private UserModel $userModel, PostModel $postModel,
                                private LikeModel $likeModel, private CommentsModel $commentsModel,
                                private MessagesModel $messagesModel){

    }

    public function renderMessages(): void{
        $userId = $this->getUser()->id;

        $chats = $this->messagesModel->getChatRooms($userId);
        $loggedInUser = $this->getUser()->getIdentity()->username;
        $this->template->chats = $chats;
        $this->template->loggedInUser = $loggedInUser;
    }

    public function renderChat(string $user1, string $user2): void
    {
        $user1_data = $this->getUser();
        $user1_id = $user1_data->id;

        $user2_data = $this->userModel->getUser($user2);
        $user2_id = $user2_data['id'];

        $roomId = $this->generateRoomId($user1, $user2);

        $this->template->sender = $user1;
        $this->template->sender_id = $user1_id;
        $this->template->recipient = $user2;
        $this->template->recipient_id = $user2_id;
        $this->template->recipient_profile_picture = $user2_data['profile_picture'];

        $roomExists = $this->messagesModel->chatRoomExists($roomId);
        if(!$roomExists){
            $this->messagesModel->createRoom($user1, $user2, $roomId);
        }
        $messagesTableExists = $this->messagesModel->messageTableExists($roomId);
        if(!$messagesTableExists){
            $this->messagesModel->createMessagesTable($roomId);
        }
    }

    protected function generateRoomId(string $userId1, string $userId2)
    {
        $userIds = [$userId1, $userId2];
        sort($userIds);
        $userIds = array_map('strtolower', $userIds);
        return implode('_', $userIds);
    }
    public function createChatWithUser(){}
    public function handleLike(): void {}
}