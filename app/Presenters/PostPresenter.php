<?php

namespace App\Presenters;

use App\CustomAuthenticator;
use App\Models\CommentsModel;
use App\Models\LikeModel;
use App\Models\PostModel;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;

class PostPresenter extends Presenter
{
    public function __construct(private Nette\Database\Explorer $database, public CustomAuthenticator $authenticator,
                                private PostModel $postModel, private CommentsModel $commentsModel, private LikeModel $likeModel)
    {
    }

    public function renderPost(int $postId): void
    {
        $userId = $this->getUser()->getId();
        $this->template->comments = $this->commentsModel->getCommentsByPostId($postId, $userId);
        $this->template->post = $this->postModel->get($postId);

        $liked = $this->likeModel->hasUserLikedPost($userId, $postId);
        $this->template->liked = $liked;
    }

    public function renderCreate(): void
    {
        $tags = $this->database->table('Tags')->fetchAll();

        $this->template->tags = $tags;
    }

    public function createComponentPostForm(): Form
    {
       $form = new Form();

       $form->addTextArea('content');
       $form->addUpload('image')
            ->addRule(Form::MAX_FILE_SIZE, 'The uploaded file is too large.', 10 * 1024 * 1024); // 10 MB

       $tags = $this->database->table('Tags')->fetchAll();
       foreach ($tags as $tag) {
            $form->addCheckbox('tag' . $tag->id);
       }

       $form->addSubmit('submit');
       $form->onSuccess[] = [$this, 'postFormSucceeded'];
       return $form;
    }

    /**
     * @throws AbortException
     */
    public function postFormSucceeded(Form $form, \stdClass $values): void
    {
        $imagePath = null;
        $image = $values->image;
        if ($image && $image->isOk() && $image->isImage()){
            $imagePath = $this->uploadImage($values->image);
        }
        $selectedTags = [];

        foreach ($values as $key => $value) {
            if (str_starts_with($key, 'tag') && $value) {
                $tagId = substr($key, 3);
                $selectedTags[] = $tagId;
            }
        }

        if (empty($values->content) && empty($imagePath) && empty($selectedTags)) {
            $form['content']->addError("You can't create an empty post. Please upload an image, write a description, or select at least one tag.");
        }

        if (empty($values->content) && empty($imagePath)) {
            $form['content']->addError("You can't create an empty post. Please upload an image, write a description, or select at least one tag.");
            return;
        }

        if (count($selectedTags) === 0) {
            $form["tag1"]->addError("Please select at least one tag.");
            return;
        }

        $tagFields = array_slice($selectedTags, 0, 3);
        while (count($tagFields) < 3) {
            $tagFields[] = null; // Filling remaining tags with null
        }
        $createdAt = new DateTime();

        $this->database->table('posts')->insert([
            'content' => $values->content,
            'created_at' => $createdAt,
            'creator' => $this->getUser()->id,
            'tag1' => $tagFields[0],
            'tag2' => $tagFields[1],
            'tag3' => $tagFields[2],
            'image' => $imagePath,
        ]);

        $this->redirect('Home:');
    }

    private function uploadImage($image): string
    {
        if ($image instanceof Nette\Http\FileUpload) {
            $uploadDir = __DIR__ . '/../../www/images/uploads/posts/'; // Replace with your upload directory
            $imageName = uniqid() . '-' . $image->getName();
            $image->move($uploadDir . $imageName);
            return "/images/uploads/posts/" . $imageName;
        }
        return '';
    }

    public function createComponentCommentForm(): Form{

        $form = new Form();

        $form->addTextArea('content');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'commentFormSucceded'];return $form;
    }

    /**
     * @throws AbortException
     */
    public function commentFormSucceded(Form $form, \stdClass $values): void{

        if (empty($values->content)) {
            $form['content']->addError("You can't post an empty comment.");
            return;
        }

        $createdAt = new DateTime();
        $content = $values->content;
        $postId = $this->getParameter('postId');
        $this->commentsModel->create($content, $this->user->id, $createdAt, $postId);

        $this->redirect('this');
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
    #[NoReturn] public function likeFormSucceeded(Form $form, \stdClass $values, string $type): void
    {
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