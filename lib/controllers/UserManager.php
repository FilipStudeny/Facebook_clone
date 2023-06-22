<?php

    require_once "./lib/classes/User.php";
    require_once "./lib/interfaces/IManager.php";

    class UserManager{

        private mysqli $databaseConnection;
        public function __construct(mysqli $databaseConnection){
            $this->databaseConnection = $databaseConnection;

        }

        public function getByID(string $id)
        {
            // TODO: Implement getByID() method.
        }

        public function createNew(array $data): void
        {
            $username = $data['username'];
            $password = $data['password'];
            $hashedPassword = md5($password);
            $email = $data['email'];
            $profilePicture = $data['profile_picture'];
            $firstname = $data['firstname'];
            $surname = $data['surname'];
            $registerDate = date("Y-m-d");

            $query = "INSERT INTO user (username, password, profile_picture, email, firstname, surname, likes, posts, friends, register_date, account_is_closed) 
              VALUES ('$username', '$hashedPassword', '$profilePicture', '$email', '$firstname', '$surname', '', '', '', '$registerDate', false)";

            if (mysqli_query($this->databaseConnection, $query)) {
                echo "User created successfully!";
            } else {
                echo "Error creating user: " . mysqli_error($this->databaseConnection);
            }
        }

        public function getUser(string $identifier): ?User {
            return new User($this->databaseConnection, $identifier);
        }

        public function userExists(string $email, string $password): bool
        {
            $hashedPassword = md5($password);
            $email = mysqli_real_escape_string($this->databaseConnection, $email);
            $query = "SELECT * FROM User WHERE email='$email' AND password='$hashedPassword'";
            $result = mysqli_query($this->databaseConnection, $query);
            return mysqli_num_rows($result) > 0;
        }

        public function usernameInUse(string $username): bool
        {
            $username = mysqli_real_escape_string($this->databaseConnection, $username);
            $query = "SELECT * FROM User WHERE username='$username'";
            $result = mysqli_query($this->databaseConnection, $query);
            return mysqli_num_rows($result) > 0;
        }

        public function emailInUse(string $email): bool
        {
            $email = mysqli_real_escape_string($this->databaseConnection, $email);
            $query = "SELECT * FROM User WHERE email='$email'";
            $result = mysqli_query($this->databaseConnection, $query);
            return mysqli_num_rows($result) > 0;
        }

        public function delete(string $id)
        {
            // TODO: Implement delete() method.
        }

        public function updateData(string $data)
        {
            // TODO: Implement updateData() method.
        }

        public function checkIfAlreadyInUse(string $email, string $username): array
        {
            $errors = [];

            $email = mysqli_real_escape_string($this->databaseConnection, $email);
            $username = mysqli_real_escape_string($this->databaseConnection, $username);

            if ($this->emailInUse($email)) {
                $errors[] = new FormError("email_in_use", "Email address already being used.");
            }

            if ($this->usernameInUse($username)) {
                $errors[] = new FormError("username_is_used", "Username already being used.");
            }

            return $errors;
        }
    }


