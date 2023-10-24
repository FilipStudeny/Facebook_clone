<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\LikeModel;
use App\Models\PostModel;
use JetBrains\PhpStorm\NoReturn;
use Nette;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;


final class HomePresenter extends Nette\Application\UI\Presenter
{

    private PostModel $posts;
    private Nette\Utils\Paginator $paginator;
    public function __construct(private Nette\Database\Explorer $database, PostModel $posts,  private LikeModel $likeModel) {
        $this->posts = $posts;
        $this->paginator = new Nette\Utils\Paginator();
        $this->paginator->setItemsPerPage(10);
    }

    public function renderDefault(int $page = 1, $tag = null)
    {
        $userId = $this->getUser()->isLoggedIn() ? $this->getUser()->id : 0;

        $this->paginator->setPage($page);
        $totalItemCount = $tag ? $this->posts->getTotalCountByTag($tag) : $this->posts->getTotalCount();
        $this->paginator->setItemCount($totalItemCount);

        $this->template->tags = $this->posts->getTags(); // Fetch all tags from the Tags table
        $this->template->selectedTag = $tag; // Pass the selected tag to the template

        if ($tag) {
            $this->template->posts = $this->posts->getByTagPaginated($userId, $tag, $this->paginator->getOffset(), $this->paginator->getLength()); // Filter posts by the selected tag and paginate
        } else {
            $this->template->posts = $this->posts->getAllPaginated($userId, $this->paginator->getOffset(), $this->paginator->getLength());
        }

        $this->template->paginator = $this->paginator;
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
        $existing_like = $this->likeModel->$likeModelMethod($userId, (int)$targetId);

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
