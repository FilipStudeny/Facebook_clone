<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\PostModel;
use Nette;


final class HomePresenter extends Nette\Application\UI\Presenter
{

    private PostModel $posts;
    private Nette\Utils\Paginator $paginator;
    public function __construct(PostModel $posts) {
        $this->posts = $posts;
        $this->paginator = new Nette\Utils\Paginator();
        $this->paginator->setItemsPerPage(10);
    }

    public function renderDefault(int $page = 1, $tag = null)
    {
        $this->paginator->setPage($page);
        $totalItemCount = $tag ? $this->posts->getTotalCountByTag($tag) : $this->posts->getTotalCount();
        $this->paginator->setItemCount($totalItemCount);

        $this->template->tags = $this->posts->getTags(); // Fetch all tags from the Tags table
        $this->template->selectedTag = $tag; // Pass the selected tag to the template

        if ($tag) {
            $this->template->posts = $this->posts->getByTagPaginated($tag, $this->paginator->getOffset(), $this->paginator->getLength()); // Filter posts by the selected tag and paginate
        } else {
            $this->template->posts = $this->posts->getAllPaginated($this->paginator->getOffset(), $this->paginator->getLength());
        }

        $this->template->paginator = $this->paginator;
    }
}
