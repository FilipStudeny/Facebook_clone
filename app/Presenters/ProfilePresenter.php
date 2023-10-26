<?php

namespace App\Presenters;

use App\Models\CommentsModel;
use App\Models\LikeModel;
use App\Models\PostModel;
use App\Models\UserModel;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use App\CustomAuthenticator;
use Nette;
use Nette\Utils\DateTime;

class ProfilePresenter extends Presenter
{
    private PostModel $posts;
    private Nette\Utils\Paginator $paginator;

    public function __construct(private Nette\Database\Explorer $database, public CustomAuthenticator $authenticator,
                                private UserModel $userModel, PostModel $postModel,
                                private LikeModel $likeModel, private CommentsModel $commentsModel){
        $this->posts = $postModel;
        $this->paginator = new Nette\Utils\Paginator();
        $this->paginator->setItemsPerPage(10);
    }

    public function renderDefault(string $username, int $page = 1, string $type = 'Posts'): void{
        $user = $this->userModel->getUser($username);
        $this->template->type = $type;

        if (!$user) {
            throw new Nette\Application\BadRequestException('User not found');
        }

        $userId = $user->id;
        $this->paginator->setPage($page);
        $totalItemCount = null;

        $this->paginator->setItemCount($totalItemCount);
        if ($type === 'Posts') {
            $totalItemCount = $this->posts->getTotalCountByUser($username);
            $this->template->posts = $this->posts->getAllPaginated($userId,$this->paginator->getOffset(), $this->paginator->getLength());
            $this->template->likes = [];
            $this->template->comments = [];
        } elseif ($type === 'Comments') {
            $totalItemCount = $this->commentsModel->getTotalCountByUser($username);
            $this->template->comments = $this->commentsModel->getAllPaginated($userId, $this->paginator->getOffset(), $this->paginator->getLength());
            $this->template->posts = [];
            $this->template->likes = [];
        }else{
            $totalItemCount = $this->likeModel->getTotalCountByUser($userId);
            $this->template->posts = [];
            $this->template->comments = [];
            $this->template->likes = $this->likeModel->getAllPaginated($userId, $this->paginator->getOffset(), $this->paginator->getLength());
        }

        $this->paginator->setItemCount($totalItemCount);
        $this->template->paginator = $this->paginator;
        $this->template->profile = $user;
    }

    public function handleLike(): void {
        $userId = $this->getUser()->getId();
        $entityId = $this->getParameter('entityId');
        $type = $this->getParameter('entityType');

        $likeModelMethod = $type === 'post' ? 'hasUserLikedPost' : 'hasUserLikedComment';
        $existingLike = $this->likeModel->$likeModelMethod($userId, $entityId);

        if ($existingLike) {
            if ($type === 'post') {
                $this->likeModel->deleteLike($userId, 'post', $entityId);
            } elseif ($type === 'comment') {
                $this->likeModel->deleteLike($userId, 'comment', $entityId);
            }
            $liked = false;
        } else {
            $this->database->table('Likes')->insert([
                'user_id' => $userId,
                'type' => $type,
                'liked_entity_id' => $entityId,
                'created_at' => new \DateTime(),
            ]);
            $liked = true;
        }

        $this->sendJson(['liked' => $liked]);
    }
}