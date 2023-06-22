<?php


    require_once __DIR__ . '/../utils/Time.php';

    use App\lib\utils\Time;

    class Comment{
        private array $commentData;
        private mysqli $databaseConnection;

        public function __construct(mysqli $databaseConnection, string $commentID)
        {
            $this->databaseConnection = $databaseConnection;

            $query = "SELECT comment.*, user.username, user.profile_picture FROM comment JOIN user ON comment.creator_id = user.ID WHERE comment.ID = ?; ";
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

        public function getPostID(): int {
            return $this->commentData['post_id'];
        }

        public function getLikes(): string{
            return $this->commentData['likes'];
        }

        public function getDateOfCreation(): string{
            return $this->commentData['date_of_creation'];
        }

        public function getCreatorUsername(): string{
            return $this->commentData['username'];
        }

        public function getCreatorProfilePicture(): string{
            return $this->commentData['profile_picture'];
        }
        public function render(array $data): void{
            echo $this->getHTML();
        }

        public function getHTML(): string
        {
            $creator = $this->getCreatorUsername();
            $creatorProfilePicture = $this->getCreatorProfilePicture();
            $body = $this->getBody();
            $dateOfCreation = Time::getTimeSinceCreation($this->getDateOfCreation());

            return <<<HTML
                <article class='comment'>
                    <header class='comment_header'>
                        <div class='comment_profile_pic_container'>
                            <img class='comment_profile_picture' src='$creatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='comment_header_user_info'>
                            <nav class='comment_header_user_links'>
                                <a href='$creator'>$creator</a>
                            </nav>
                            <p class='comment_time_of_creation'>$dateOfCreation</p>
                        </div>
                    </header>
                    <div class='comment_body'>
                        $body
                    </div>
                </article>
            HTML;
        }

        public function getCommentTime(string $timeOfCreation): string
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