<?php


    class User{
        private mysqli $databaseConnection;
        private array $userData;

        public function __construct(mysqli $databaseConnection, string $identifier)
        {
            $this->databaseConnection = $databaseConnection;

            $query = "SELECT * FROM user WHERE email=? OR username=? OR id=?";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "sss", $identifier, $identifier, $identifier);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $this->userData = mysqli_fetch_array($result);
        }

        public function getID(): int{
            return $this->userData['ID'];
        }
        public function getFriends(): array {
            $friends = $this->userData['friends'];
            return !empty($friends) ? explode(',', $friends) : [];
        }

        public function getUsername(): string{
            return $this->userData['username'];
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

        public function getLikes(): string{
            return $this->userData['likes'];
        }
        public function getPosts(): string{
            return $this->userData['posts'];
        }

        public function getComments(): string{
            return $this->userData['comments'];
        }

        public function getNumberOfPosts(): int {
            return count(explode(',', $this->userData['posts']));
        }

        public function getProfilePicture(): string {
            return $this->userData['profile_picture'];
        }

        public function isClosed(): bool {
            return $this->userData['account_is_closed'];
        }

        public function isFriendWith(string $username): bool {
            $friends = $this->getFriends();
            return in_array($username, $friends) || $username === $this->getUsername();
        }
    }
