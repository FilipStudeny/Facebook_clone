<?php

namespace App\Presenters;

use App\Models\CommentsModel;
use App\Models\LikeModel;
use App\Models\PostModel;
use App\Models\UserModel;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
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

    protected function createLikeForm(string $type): Form
    {
        $form = new Form();
        $form->addHidden('entityId');
        $form->addSubmit('like', 'Like');
        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($type) {
            $this->likeFormSucceeded($form, $values, $type);
        };
        return $form;
    }

    protected function createComponentLikeForm(): Form
    {
        return $this->createLikeForm('post');
    }

    protected function createComponentCommentLikeForm(): Form
    {
        return $this->createLikeForm('comment');
    }

    /**
     * @throws AbortException
     */
    #[NoReturn] public function likeFormSucceeded(Form $form, \stdClass $values, string $type): void{
        $userId = $this->getUser()->getId();
        $targetId = $values->entityId;
        $createdAt = new \DateTime();

        $likeModelMethod = $type === 'post' ? 'hasUserLikedPost' : 'hasUserLikedComment';
        $existing_like = $this->likeModel->$likeModelMethod($userId, $targetId);

        if ($existing_like) {
            $this->likeModel->deleteLike($userId, $type, $targetId);
        } else {
            $this->database->table('Likes')->insert([
                'user_id' => $userId,
                'type' => $type,
                'liked_entity_id' => $targetId,
                'created_at' => $createdAt
            ]);
        }

        $this->redirect('this');
    }

}