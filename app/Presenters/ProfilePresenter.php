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
        $loggedInUser = $this->getUser()->getIdentity()->username;
        $this->template->loggedInUser = $loggedInUser;

        if (!$user) {
            throw new Nette\Application\BadRequestException('User not found');
        }

        $userId = $user->id;
        $this->paginator->setPage($page);
        $totalItemCount = null;

        $this->paginator->setItemCount($totalItemCount);
        if ($type === 'Posts') {
            $totalItemCount = $this->posts->getTotalCountByUser($username);
            $this->template->posts = $this->posts->getAllPaginatedByUser($userId,$this->paginator->getOffset(), $this->paginator->getLength());
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

    public function renderReport(string $username): void{
        $user = $this->userModel->getUser($username);
        $this->template->reportedUser = $user;
    }

    public function createComponentUserReportForm(): Form
    {
        $form = new Form();
        $form->addHidden('user_id');
        $form->addSelect('reason', '', [
            'harassment' => 'Harassment',
            'spam' => 'Spam',
            'inappropriate_content' => 'Inappropriate Content',
            'abusive_behavior' => 'Abusive Behavior'
        ]);
        $form->addTextArea('description');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'reportFormSucceed'];
        return $form;
    }

    public function reportFormSucceed(Form $form, \stdClass $values): void{
        $user_id = $values->user_id;
        $reporter_id = $this->getUser()->id;
        $report_description = $values->description;
        $report_type = $values->reason;
        $createdAt = new DateTime();

        $this->database->table('user_reports')->insert([
            'user_id' => $user_id,
            'reporter_id' => $reporter_id,
            'report_reason' => $report_type,
            'report_description' => $report_description,
            'report_time' => $createdAt
        ]);

        $this->redirect('Home:default');

    }


    /**
     * @throws AbortException
     */
    public function handlehandleUpload(): void
    {
        $userId = $this->getUser()->getId();
        $croppedImageData = $this->getParameter('croppedImageData');
        $imagePath = null;
        if ($croppedImageData) {
            $imagePath = $this->uploadImage($croppedImageData, $userId);
        }

        $this->userModel->updateProfilePicture($userId, $imagePath);
        $this->authenticator->wakeupIdentity($this->getUser()->identity);
    }

    private function uploadImage($croppedImageData, string $userId): string
    {
        $decodedData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $croppedImageData));
        $imageName = uniqid() . '-' . $userId . '.jpeg';
        $uploadDir = __DIR__ . '/../../www/images/uploads/users/' . $userId . '/'; // Replace with your upload directory

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imagePath = $uploadDir . $imageName;
        file_put_contents($imagePath, $decodedData);

        return "/images/uploads/users/" . $userId . "/" . $imageName;
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