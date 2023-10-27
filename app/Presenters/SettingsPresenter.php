<?php

namespace App\Presenters;

use App\CustomAuthenticator;
use App\Models\CommentsModel;
use App\Models\LikeModel;
use App\Models\PostModel;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;

final class SettingsPresenter extends Presenter
{
    public function __construct(private Explorer $database, public CustomAuthenticator $authenticator,
                                private PostModel $postModel, private CommentsModel $commentsModel, private LikeModel $likeModel)
    {}

    public function renderDefault(){

    }
    public function createComponentSignInForm(): Form
    {
        $form = new Form();

        $form->addText('username');
        $form->addText('email');
        $form->addPassword('password');
        $form->addPassword('repeat_password');
        $form->addText('firstname');
        $form->addText('lastname');
        $form->addSubmit('submit');
        $form->onSuccess[] = function (Form $form, array $data) {

            foreach ($data as $key => $value) {
                if ($value == "repeat_password") {
                    continue;
                }
                if (empty($value)) {
                    $form[$key]->addError("The $key field is required, make sure its not empty");
                }
            }

            foreach ($data as $value) {
                if (empty($value)) {
                    return;
                }
            }

            try {
                $this->authenticator->createNewUser($data);
            } catch (\Exception $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    public function handleLike(){

    }
}