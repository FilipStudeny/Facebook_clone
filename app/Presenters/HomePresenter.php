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

    public function handleLike(): void {
        $userId = $this->getUser()->getId();
        $entityId = $this->getParameter('entityId');
        $type = $this->getParameter('entityType');

        $likeModelMethod = $type === 'post' ? 'hasUserLikedPost' : 'hasUserLikedComment';
        $existingLike = $this->likeModel->$likeModelMethod($userId, $entityId);

        if ($existingLike) {
            if ($type === 'post') {
                $this->likeModel->deleteLike((int)$userId, 'post', (int)$entityId);
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
