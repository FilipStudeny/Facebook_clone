<?php


    class Post {
        private mysqli $databaseConnection;
        private array|null|false $postData;

        public function __construct(mysqli $databaseConnection, string $id)
        {
            $this->databaseConnection = $databaseConnection;

            $query = "SELECT post.*, user.username FROM post JOIN user ON post.creator_id = user.ID WHERE post.ID = ?; ";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "s", $id);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $this->postData = mysqli_fetch_array($result);

        }

        public function getID(): int {
            return $this->postData['ID'];
        }

        public function getPost(): string{
            return $this->postData['postBody'];
        }

        public function getDateOfCreation(): string{
            return $this->postData['date_of_creation'];
        }

        public function getCreatorUsername(): string{
            return $this->postData['username'];
        }

        public function getCreatedForWho(): string{
            return $this->postData['created_for_who'];
        }

        public function getLikes(): string{
            return $this->postData['likes'];
        }

        public function getComments(): string{
            return $this->postData['comments'];
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
            $postDate = $this->getPostTime($this->getDateOfCreation());


            $postBodyHTML = $isPostDetail ? $postBody :
                "<a class='post_detail_link' href='post.php?id=$postID'>
                    $postBody
                </a>";


            return <<<HTML
                <article class='post'>
                    <header class='post_header'>
                        <div class='post_profile_pic_container'>
                            <img class='post_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='post_header_user_info'>
                            <nav class='post_header_user_links'>
                                <a href='$creatorCreatorUsername'>$creatorCreatorUsername</a>
                                <a href='$postTo'><span>to</span></a>
                            </nav>
                            <p class='post_time_of_creation'>$postDate</p>
                        </div>
                    </header>
                    <div class='post_body'>
                        $postBodyHTML
                    </div>
                </article>
            HTML;
        }

        public function getPostTime(string $timeOfCreation): string
        {
            // Time frame
            $dateNow = date("Y-m-d H:i:s");
            $startDate = new DateTime($timeOfCreation); // Time of post
            $endDate = new DateTime($dateNow); // Current time
            $interval = $startDate->diff($endDate); // Difference
    
            if ($interval->y >= 1) {
                $timeMessage = $interval->y . ($interval->y == 1 ? " year ago." : " years ago.");
            } else if ($interval->m >= 1) {
                $days = $interval->d == 0 ? " ago." : ($interval->d == 1 ? " day ago." : " days ago.");
                $timeMessage = $interval->m . ($interval->m == 1 ? " month" : " months") . $days;
            } else if ($interval->d >= 1) {
                $timeMessage = $interval->d == 1 ? "Yesterday." : $interval->d . " days ago.";
            } else if ($interval->h >= 1) {
                $timeMessage = $interval->h . ($interval->d == 1 ? " hour ago." : " hours ago.");
            } else if ($interval->i >= 1) {
                $timeMessage = $interval->i . ($interval->i == 1 ? " minute ago." : " minutes ago.");
            } else {
                $timeMessage = $interval->s <= 30 ? "Just now." : $interval->s . " seconds ago.";
            }
    
            return $timeMessage;
        }

    }
?>