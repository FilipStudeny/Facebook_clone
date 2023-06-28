<?php

    require_once __DIR__ . '/../utils/Time.php';

    use App\lib\utils\Time;

    class Post {
        private array|null|false $postData;

        public function __construct(array $data)
        {
            $this->postData = $data;
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
            $userManager = new UserManager(DBConnection::connect());
            $creator = $userManager->getUser($this->getCreatorUsername());

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
