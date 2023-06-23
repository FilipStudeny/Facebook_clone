<?php


    require_once __DIR__ . '/../utils/Time.php';

    use App\lib\utils\Time;

    class Comment{
        private array $commentData;
        private mysqli $databaseConnection;

        public function __construct(mysqli $databaseConnection, string $commentID)
        {
            $this->databaseConnection = $databaseConnection;

            $query = "SELECT comment.*, (LENGTH(comment.likes) - LENGTH(REPLACE(comment.likes, ',', ''))) AS like_count, user.ID AS creator_id FROM comment JOIN user ON comment.creator_id = user.ID WHERE comment.ID = ?";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "s", $commentID);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $this->commentData = mysqli_fetch_array($result);
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
            $creator = new User($this->databaseConnection, $this->getCreatorID());
            $creatorUsername = $creator->getUsername();
            $creatorProfilePicture = $creator->getProfilePicture();
            $creatorLikes = $creator->getLikes();

            $body = $this->getBody();
            $dateOfCreation = Time::getTimeSinceCreation($this->getDateOfCreation());
            $likeCount = $this->getLikeCount();
            $commentID = $this->getID();

            $liked = str_contains($creatorLikes, $this->commentData['ID']) ? 'liked' : '';

            return <<<HTML
                <article class='comment'>
                    <header class='comment_header'>
                        <div class='comment_profile_pic_container'>
                            <img class='comment_profile_picture' src='$creatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='comment_header_user_info'>
                            <nav class='comment_header_user_links'>
                                <a href='$creatorUsername'>$creatorUsername</a>
                            </nav>
                            <p class='comment_time_of_creation'>$dateOfCreation</p>
                        </div>
                    </header>
                    <div class='comment_body'>
                        $body
                    </div>
                    <footer class="comment_footer">
                        <div class="comment_footer_data">
                            <button class="likes_count $liked" data-likable-name="comment" data-likable-id="$commentID">
                                <i class="fa-solid fa-thumbs-up"></i>
                                <span>$likeCount</span>
                            </button>
                        </div>
                    </footer>
                </article>
            HTML;
        }
    }

