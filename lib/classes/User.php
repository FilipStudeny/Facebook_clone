<?php


    class User{
        private array $userData;

        public function __construct(array $data)
        {
            $this->userData = $data;
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
