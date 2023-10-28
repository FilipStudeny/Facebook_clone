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
        $this->template->chats = $chats;
    }

    public function renderChat(string $user1, string $user2): void
    {
        $user1_id = $this->userModel->getUserIdByUsername($user1);
        $user2_id = $this->userModel->getUserIdByUsername($user2);
        $roomId = $this->generateRoomId($user1, $user2);

        $roomExists = $this->messagesModel->chatRoomExists($roomId);
        if(!$roomExists){
            $this->messagesModel->createRoom($user1_id, $user2_id, $roomId);
        }
        $messagesTableExists = $this->messagesModel->messageTableExists($roomId);
        if(!$messagesTableExists){
            $this->messagesModel->createMessagesTable($roomId);
        }
    }

    protected function generateRoomId(string $userId1,string $userId2)
    {
        $userIds = [$userId1, $userId2];
        sort($userIds);
        return implode('_', $userIds);
    }
    public function createChatWithUser(){}
    public function handleLike(): void {}
}