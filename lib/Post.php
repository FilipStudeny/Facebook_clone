<?php

    interface IPost {
        public function getId(): string;
        public function getCreator(): string;
        public function getBody(): string;
        public function getForWho(): string;
        public function getDateOfCreation(): string;
        public function getUserClosed(): string;
        public function getDeleted(): string;
        public function getLikes(): string;
        public function getHTML(bool $isPostDetail): string;
        public function getPostTime(string $timeOfCreation);
    }

    class Post implements IPost{
        private mysqli $databaseConnection;
        private $postData;

        public function __construct(mysqli $databaseConnection, string $id)
        {
            $this->databaseConnection = $databaseConnection;

            $query = "SELECT * FROM posts WHERE id=?";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "s", $id);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $this->postData = mysqli_fetch_array($result);
        }

        public function getId(): string
        {
            return $this->postData['id'];
        }

        public function getBody(): string
        {        
            return $this->postData['body'];
        }

        public function getCreator(): string
        {
            return $this->postData['creator'];
        }

        function getUserClosed(): string
        {
            return $this->postData['user_closed'];
        }

        function getForWho(): string
        {
            return $this->postData['for_who'];
        }

        function getDateOfCreation(): string
        {
            return $this->postData['date_creation'];
        }

        public function getLikes(): string
        {
            return $this->postData['likes'];
        }

        function getDeleted(): string
        {
            return $this->postData['deleted'];
        }

        function getHTML(bool $isPostDetail): string
        {
            $creator = new User($this->databaseConnection, $this->getCreator());
            $creatorCreatorProfilePicture = $creator->getProfilePicture();
            $creatorCreatorUsername = $creator->getUsername();
            $postTo = $this->getForWho();
            $postBody = $this->getBody();
            $postID = $this->getId();
            $postDate = $this->getPostTime($this->getDateOfCreation());


            $html = 
            <<<HTML
                <article class='post'>
                    <header class='post_header'>
                        <div class='post_profile_pic_container'>
                            <img class='post_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='post_header_user_info'>
                            <nav class='post_header_user_links'>
                                <a href='$creatorCreatorUsername'>$creatorCreatorUsername</a>
                                <a href='$postTo'><span>to</span> asdad</a>
                            </nav>
                            <p class='post_time_of_creation'>$postDate</p>
                        </div>
                    </header>
                    <div class='post_body'>
                        <a class='post_body' href='post.php?id=$postID'>
                            $postBody
                        </a>
                    </div>
                </article>
            HTML;

            return $html;
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