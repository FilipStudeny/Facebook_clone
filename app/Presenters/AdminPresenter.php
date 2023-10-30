<?php

namespace App\Presenters;

use App\CustomAuthenticator;
use App\Models\CommentsModel;
use App\Models\LikeModel;
use App\Models\MessagesModel;
use App\Models\PostModel;
use App\Models\UserModel;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
use Nette;

final class AdminPresenter extends Presenter
{
    private Nette\Utils\Paginator $paginator;

    public function __construct(private Explorer $database, public CustomAuthenticator $authenticator,
                                private UserModel $userModel, private PostModel $postModel,
                                private LikeModel $likeModel, private CommentsModel $commentsModel,
                                private MessagesModel $messagesModel){
        $this->paginator = new Nette\Utils\Paginator();
        $this->paginator->setItemsPerPage(10);
    }

    public function renderUsers(int $page = 1): void{
        $allUsers = $this->userModel->getAllUsers();
        $this->template->users = $allUsers;

        $this->paginator->setPage($page);
        $totalItemCount = $this->userModel->getTotalCount();
        $this->paginator->setItemCount($totalItemCount);
        $this->template->paginator = $this->paginator;
    }

    public function renderUser(string $username,int $page = 1): void{
        $user = $this->userModel->getUser($username);
        $user_id = $user['id'];
        $totalPostsCount = $this->postModel->getCountByUserId($user_id);
        $totalCommentCount = $this->commentsModel->getCountByUserId($user_id);
        $offset = $this->paginator->getOffset();
        $limit = $this->paginator->getLength();

        $postsCount = $this->database->table('posts')
            ->where('creator', $user_id)
            ->count();
        $commentsCount = $this->database->table('comments')
            ->where('user_id', $user_id)
            ->count();
        $totalItemCount = $postsCount + $commentsCount;

        $data = $this->database->query("
            SELECT id, content, created_at, NULL as post_id, 'post' as type 
            FROM posts WHERE creator = ? 
            UNION SELECT id, content, created_at, post_id, 'comment' as type FROM comments WHERE user_id = ?
            LIMIT ?, ?;
        ", $user_id, $user_id, $offset, $limit);

        $this->paginator->setPage($page);
        $this->paginator->setItemCount($totalItemCount);
        $this->template->paginator = $this->paginator;

        $this->template->selectedUser = $user;
        $this->template->post_count = $totalPostsCount;
        $this->template->comment_count = $totalCommentCount;
        $this->template->user_content = $data;

    }

        public function createComponentRoleForm(): Form
    {
        $form = new Form();
        $form->addHidden('username');
        $form->addSelect('roles', '', ['admin' => 'Admin', 'user' => 'User']);


        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'rolesFormSucceed'];
        return $form;
    }

    public function rolesFormSucceed(Form $form, \stdClass $values): void{
        $this->userModel->setUserRole($values->username, $values->roles);
    }

    public function handleLike(): void {}
}