<?php

namespace App;

use App\Models\UserModel;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;

use Nette;
use Nette\Security\Passwords;
use Nette\Database\Explorer;

final class CustomAuthenticator implements Authenticator, Nette\Security\IdentityHandler
{
    public function __construct(private readonly Explorer $database, private readonly Passwords $passwords, private readonly UserModel $userModel){}
    function authenticate(string $username, string $password): IIdentity
    {
        $userData = $this->database->table('users')
            ->where('email = ? OR username = ?', $username, $username)
            ->fetch();

        if(!$userData){
            throw new Nette\Security\AuthenticationException("User doesn't exist", self::IDENTITY_NOT_FOUND);
        }else if(!$this->passwords->verify($password, $userData['password'])){
            throw new Nette\Security\AuthenticationException("Invalid password/username, try again", self::INVALID_CREDENTIAL);
        }else if($this->passwords->needsRehash($userData['password'])){
            //REHASH PASSWORD IF NEEDE
            $userData->update(['password' => $this->passwords->hash($password)]);
        }

        $userData->update(['last_login_time' => date('Y-m-d H:i:s')]);
        $user = $userData->toArray();
        unset($user['password']);

        return new Nette\Security\SimpleIdentity($user['id'], 'admin', [
            'username' => $user['username'] ,
            'profile_picture' => $user['profile_picture'],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function createNewUser(array $data): void
    {
        $existingUser = $this->database->table('users')
            ->where('username = ? OR email = ?', $data['username'], $data['email'])
            ->fetch();

        if ($existingUser) {
            throw new \Exception('User with the provided username or email already exists.');
        }

        $newUserData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $this->passwords->hash($data['password']),
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'gender' => $data['gender'],
            'registration_date' => date('Y-m-d H:i:s'),
            'profile_picture' => "/images/user.png",
            'role' => 'user',
            'banned' => 0
        ];

        $this->database->table('users')->insert($newUserData);
    }

    public function wakeupIdentity(IIdentity $identity): ?Nette\Security\SimpleIdentity{
        $userId = $identity->getId();
        $user = $this->userModel->getUserById($userId);
        if(!$user){
            return null;
        }

        return new Nette\Security\SimpleIdentity($user['id'], $user['role'], [
            'username' => $user['username'] ,
            'profile_picture' => $user['profile_picture'],
        ]);
    }

    function sleepIdentity(IIdentity $identity): IIdentity
    {
        // TODO: Implement sleepIdentity() method.
        return $identity;
    }
}