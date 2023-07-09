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

        public function getCreatorID(): string{
            return $this->postData['creator_id'];
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

        public function getComments(): string{
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


        public function render(bool $isPostDetail, string $loggedInUser): void
        {
            echo $this->getHTML($isPostDetail, $loggedInUser);
        }

        function getHTML(bool $isPostDetail, string $loggedInUser): string
        {
            $userManager = new UserManager(DBConnection::connect(), $loggedInUser);
            $creator = $userManager->getUser($this->getCreatorUsername());
            $creatorCreatorProfilePicture = $creator->getProfilePicture();
            $creatorCreatorUsername = $creator->getUsername();

            $loggedUser = $userManager->getUser($loggedInUser);
            $loggedUserID = $loggedUser->getID();

            $postBody = $this->getPost();
            $postID = $this->getId();
            $postDate = Time::getTimeSinceCreation($this->getDateOfCreation());


            $commentsCount = $this->getCommentsCount();
            $likeCount = $this->getLikesCount();

            $postBodyHTML = $isPostDetail ? "<p class='post_detail'>$postBody</p>" :
                "<a class='post_detail_link' href='post.php?id=$postID'>
                    $postBody
                </a>";


            $isClickable = $isPostDetail ? "" : 'body_link';

            $liked = str_contains($this->postData['likes'], $loggedUserID) ? 'liked' : '';

            $isCreator = $creatorCreatorUsername == $loggedInUser ;

            $deleteButton = $isCreator ? '
                <button class="delete_button" data-post-id="'.$postID.'">
                    <i class="fa-solid fa-trash"></i>
                    Delete post
                </button>' : '';



            return <<<HTML
                <article class='post'>
                    <header class='post_header'>
                        <a class="post_profile_link" href="profile.php?user=$creatorCreatorUsername">
                            <div class="post_header_profile_picture" >
                                <img class='post_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50' alt="User profile picture">
                            </div>
                            <div class='post_header_user_info'>
                                <p>$creatorCreatorUsername</p>                   
                                <p class='post_time_of_creation'>$postDate</p>
                            </div>
                        </a>
                        

                        $deleteButton
                    </header>
                    <div class="post_body">
                        $postBodyHTML
                     </div>
                     
                    <footer class="post_footer">
                        <div class="post_footer_buttons">
                             <button class="comments_button">
                                <i class="fa-solid fa-comment post_comments"></i>
                                <span>$commentsCount</span>
                            </button>
                            <button class="like_button $liked " data-likable-name="post" data-likable-id=$postID >
                               <i class="fa-solid fa-thumbs-up"></i>
                               <span> $likeCount</span>
                            </button>
                        </div>
                    </footer>
                </article>
            HTML;
        }
    }
