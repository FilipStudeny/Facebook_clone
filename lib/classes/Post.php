<?php

    require_once __DIR__ . '/../utils/Time.php';

    use App\lib\utils\Time;

    class Post {
        private mysqli $databaseConnection;
        private array|null|false $postData;

        public function __construct(mysqli $databaseConnection, string $id)
        {
            $this->databaseConnection = $databaseConnection;

           // $query = "SELECT post.*, user.username FROM post JOIN user ON post.creator_id = user.ID WHERE post.ID = ?; ";
            $query = "
            SELECT post.*, user.username, COUNT(comment.ID) AS comment_count, 
                   (LENGTH(post.likes) - LENGTH(REPLACE(post.likes, ',', ''))) AS likes_count
            FROM post
            JOIN user ON post.creator_id = user.ID
            LEFT JOIN comment ON post.ID = comment.post_id
            WHERE post.ID = ?
            GROUP BY post.ID, user.username;";

            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "s", $id);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $this->postData = mysqli_fetch_array($result);

        }

        private function getID(): int {
            return $this->postData['ID'];
        }

        private function getPost(): string{
            return $this->postData['postBody'];
        }

        private function getDateOfCreation(): string{
            return $this->postData['date_of_creation'];
        }

        public function getCreatorUsername(): string{
            return $this->postData['username'];
        }

        private function getCreatedForWho(): string{
            return $this->postData['created_for_who'];
        }

        private function getComments(): string{
            return $this->postData['comments'];
        }

        private function getCommentsCount(): int{
            return $this->postData['comment_count'];
        }

        public function getLikes(): string{
            return $this->postData['likes'];
        }

        private function getLikesCount(): int{
            return $this->postData['likes_count'];
        }


        public function render(bool $isPostDetail): void
        {
            echo $this->getHTML($isPostDetail);
        }

        function getHTML(bool $isPostDetail): string
        {
            $creator = new User($this->databaseConnection, $this->getCreatorUsername());

            $creatorCreatorProfilePicture = $creator->getProfilePicture();
            $creatorCreatorUsername = $creator->getUsername();
            $postTo = $this->getCreatedForWho();
            $postBody = $this->getPost();
            $postID = $this->getId();
            $postDate = Time::getTimeSinceCreation($this->getDateOfCreation());


            $commentsCount = $this->getCommentsCount();
            $likeCount = $this->getLikesCount();

            $postBodyHTML = $isPostDetail ? $postBody :
                "<a class='post_detail_link' href='post.php?id=$postID'>
                    $postBody
                </a>";

            $forUser = ($postTo === "none") ? "" : "<a href='$postTo'><span>to</span></a>";

            $isClickable = $isPostDetail ? "" : 'body_link';

            $liked = str_contains($this->postData['likes'], $creator->getID()) ? 'likes_count liked' : 'likes_count';

            return <<<HTML
                <article class='post'>
                    <header class='post_header'>
                        <div class='post_profile_pic_container'>
                            <a href="profile.php?user=$creatorCreatorUsername">
                                <img class='post_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50' alt="User profile picture">
                            </a>
                        </div>
                        <div class='post_header_user_info'>
                            <nav class='post_header_user_links'>
                                <a href='profile.php?user=$creatorCreatorUsername'>$creatorCreatorUsername</a>
                                $forUser
               
                            </nav>
                            <p class='post_time_of_creation'>$postDate</p>
                        </div>
                    </header>
                    <div class='post_body $isClickable'>
                        $postBodyHTML
                    </div>
                    <footer class="post_footer">
                        <div class="post_footer_data">
                            
                             <button class="post_comments_count">
                                <i class="fa-solid fa-comment post_comments"></i>
                                <span>$commentsCount</span>
                            </button>
                            <button class='$liked' data-likable-name="post" data-likable-id="$postID">
                                <i class="fa-solid fa-thumbs-up"></i>
                                <span>$likeCount</span>
                            </button>
                        </div>
                    </footer>
                </article>
            HTML;
        }
    }
