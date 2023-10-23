<?php

namespace App\Presenters;

use App\CustomAuthenticator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette;


class AuthPresenter extends Presenter
{
    public function __construct(private Nette\Database\Explorer $database, public CustomAuthenticator $authenticator)
    {}
//<div n:foreach="$flashes as $flash" n:class="flash, $flash->type">{$flash->message}</div>

    public function createComponentSignInForm(): Form
    {
        $form = new Form();

        $form->addText('username' );
        $form->addText('email');
        $form->addPassword('password');
        $form->addPassword('repeat_password');
        $form->addText('firstname');
        $form->addText('lastname');
        $form->addSelect('gender', '', ['male' => 'Male', 'female' => 'Female']);
        $form->addSubmit('submit');
        $form->onSuccess[] = function (Form $form, array $data) {

            foreach ($data as $key => $value) {
                if($value == "repeat_password"){
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

    public function createComponentLoginForm(): Form{
        $form = new Form();
        $form->addText('email');
        $form->addText('password');
        $form->addText('submit');
        $form->onSuccess[] = function (Form $form, \stdClass $data): void {
            foreach ($data as $key => $value) {
                if (empty($value)) {
                    $form[$key]->addError("The $key field is required, make sure its not empty");
                }
            }
            try {
                $this->getUser()->setExpiration('14 days'); // set the expiration time
                $this->getUser()->login($data->email, $data->password); // call the CustomAuthenticator
                $this->redirect('Home:');

            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }

    /**
     * @throws AbortException
     */
    public function actionOut(): void{
        $this->getUser()->logout(true);
        $this->redirect('Home:');
    }

}
