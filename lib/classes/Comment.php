<?php


    require_once __DIR__ . '/../utils/Time.php';

    use App\lib\utils\Time;

    class Comment{
        private array $commentData;

        public function __construct(array $data)
        {
            $this->commentData = $data;
        }

        public function getID(): int {
            return $this->commentData['ID'];
        }

        public function getBody(): string{
            return $this->commentData['comment'];
        }

        public function getPostID(): string{
            return $this->commentData['post_id'];
        }

        private function getDateOfCreation(): string{
            return $this->commentData['date_of_creation'];
        }

        public function getLikes(): string {
            return $this->commentData['likes'];
        }


        private function getLikeCount(): int{
            return $this->commentData['like_count'];
        }

        public function getCreatorID(): string{
            return $this->commentData['creator_id'];
        }


        public function getHTML(string $loggedInUser): string
        {
            $userManager = new UserManager(DBConnection::connect(), $loggedInUser);
            $creator = $userManager->getUser($this->getCreatorID());
            $creatorUsername = $creator->getUsername();
            $creatorProfilePicture = $creator->getProfilePicture();

            $loggedUser = $userManager->getUser($loggedInUser);
            $loggedUserUsername = $loggedUser->getUsername();
            $loggedUserID = $loggedUser->getID();

            $body = $this->getBody();
            $dateOfCreation = Time::getTimeSinceCreation($this->getDateOfCreation());
            $likeCount = $this->getLikeCount();
            $commentID = $this->getID();

            $liked = str_contains($this->commentData['likes'], $loggedUserID) ? 'liked' : '';

            $isCreator = $creatorUsername == $loggedUserUsername ;

            $deleteButton = $isCreator ? '
                <button class="delete_button" data-comment-id="'. $commentID. '">
                    <i class="fa-solid fa-trash"></i>
                    Delete comment
                </button>' : '';

            return <<<HTML
                <article class='comment'>
                    <header class="comment_header">
                        <a class="comment_user_link" href="profile.php?user=$creatorUsername">
                            <div class='comment_profile_picture_container'>
                                <img class='comment_profile_picture' src='$creatorProfilePicture' width='50' height='50' alt="User profile picture">
                            </div>
                            
                            <div class="comment_header_info">
                                <h2>$creatorUsername</h2>
                                <h3>$dateOfCreation</h3>
                            </div>
                        </a>
                        <div>
                            $deleteButton
                            <button class="like_button $liked " data-likable-name="comment" data-likable-id=$commentID >
                                   <i class="fa-solid fa-thumbs-up"></i>
                                   <span> $likeCount</span>
                            </button>
                        </div>
                        
                        
                    </header>
                    <div class="comment_body">
                        $body
                        
                    </div>

                </article>
            HTML;

        }
    }

