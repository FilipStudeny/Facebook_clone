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

        private function getPostID(): string{
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

        private function getCreatorID(): string{
            return $this->commentData['creator_id'];
        }


        public function render(array $data): void{
            echo $this->getHTML();
        }

        public function getHTML(): string
        {
            $userManager = new UserManager(DBConnection::connect());
            $creator = $userManager->getUser($this->getCreatorID());
            $creatorUsername = $creator->getUsername();
            $creatorProfilePicture = $creator->getProfilePicture();
            $creatorLikes = $creator->getLikes();

            $body = $this->getBody();
            $dateOfCreation = Time::getTimeSinceCreation($this->getDateOfCreation());
            $likeCount = $this->getLikeCount();
            $commentID = $this->getID();
            $commentLikes = $this->getLikes();

            $liked = str_contains($this->commentData['likes'], $creator->getID()) ? 'liked' : '';
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
                        
                        <button class="comment_likes_count $liked" data-likable-name="comment" data-likable-id="$commentID">
                             <i class="fa-solid fa-thumbs-up"></i>
                             <span>$likeCount</span>
                        </button>
                    </header>
                    <div class="comment_body">
                        $body
                        
                    </div>

                </article>
            HTML;

        }
    }

