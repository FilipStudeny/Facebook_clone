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

        public function isFriendWith(string $id): bool {
            $friends = $this->getFriends();
            return in_array($id, $friends) || $id === $this->getUsername();
        }

        function getHTML(): string
        {
            $username = $this->getUsername();
            $userID = $this->getID();
            $userProfilePicture = $this->getProfilePicture();

            return <<<HTML
                <section class="user_card">
                   <div class="user_card_profile_picture_container">
                        <img class="user_card_profile_picture" src=$userProfilePicture>
                   </div>
                   <a href="/profile.php?user=$username" class="user_card_link">
                        <span>$username</span>
                   </a>
                   <button class="removeFriendButton" data-user-id=$userID data-user-action="remove_friend"><i class="fa-solid fa-user-plus"></i>Remove friend</button>
                </section>
            HTML;
        }

    }
