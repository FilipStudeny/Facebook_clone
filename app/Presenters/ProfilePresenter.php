<?php

namespace App\Presenters;

use App\Models\UserModel;
use Nette\Application\UI\Presenter;
use App\CustomAuthenticator;
use Nette;
use Nette\Utils\DateTime;

class ProfilePresenter extends Presenter
{
    public function __construct(private Nette\Database\Explorer $database, public CustomAuthenticator $authenticator, private UserModel $userModel){}

    public function renderDefault(string $username): void{
        $user = $this->userModel->getUser($username);

        if (!$user) {
            throw new Nette\Application\BadRequestException('User not found');
        }

        $this->template->profile = $user;
    }


}