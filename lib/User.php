<?php

    class User{
        private string $username;
        private mysqli $databaseConnection;
        private array $userData;

        public function __construct(mysqli $databaseConnection, string $username)
        {
            $this->username = $username;
            $this->databaseConnection = $databaseConnection;

            $query = "SELECT * FROM users WHERE username=?";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "s", $username);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $this->userData = mysqli_fetch_array($result);
        }

        public function getFriends(): array{
            return $this->userData['friends'];
        }

        public function getUsername(): string{
            return $this->username;
        }

        public function getEmail(): string{
            return $this->userData['email'];
        }

        public function getFirstname(): string{
            return $this->userData['firstname'];
        }

        public function getSurname(): string{
            return $this->userData['surname'];
        }

        public function getFullName(): string{
            return $this->getFirstname() . " " . $this->getSurname();
        }

        public function getNumberOfPosts(): int{
            return $this->userData['num_posts'];
        }

        public function getProfilePicture(): string {
            return $this->userData['profile_picture'];
        }

        public function isClosed(): bool{
            return $this->userData['closed'];
        }

        public function isFriendWith(string $username): bool{
            $nameToCheck = "," . $username . ",";
            if((strstr($this->userData['friends'], $nameToCheck)) || $nameToCheck == $this->getUsername()){
                return true;
            }else{
                return false;
            }
        }
    }
?>
