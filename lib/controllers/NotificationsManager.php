<?php

    require_once __DIR__ . '/../classes/Post.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../classes/Notification.php';

    require_once __DIR__ . '/../controllers/UserManager.php';

    class NotificationsManager
    {
        private mysqli $databaseConnection;
        private string $loggedInUser;
        private UserManager $userManager;

        public function __construct(mysqli $databaseConnection, string $loggedInUser)
        {
            $this->databaseConnection = $databaseConnection;
            $this->loggedInUser = $loggedInUser;
            $this->userManager = new UserManager($databaseConnection);
        }

        public function getNotification(string $identifier): Notification{
            $query = "SELECT * FROM notifications WHERE ID = ?;";

            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "s", $identifier);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $data = mysqli_fetch_array($result);
            return new Notification($data);
        }

        public function getAll(int $page, string $identifier, int $postLimit): void
        {
            if($page == 1){
                $start = 0;
            }else{
                $start = ((int)$page - 1) * $postLimit;
            }

            $html = "";
            $query = "SELECT notifications FROM `user` WHERE username = '$identifier' ORDER BY ID DESC";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $numIterations = 0; //Number of iterations check
            $resultsCount = 1;

            if (mysqli_num_rows($dbQuery) > 0) {
                while ($data = mysqli_fetch_array($dbQuery)) {

                    $array = array_map('trim', explode(',',  $data['notifications']));
                    $notificationsIDs = array_filter($array);

                    foreach ($notificationsIDs as $notificationID) {
                        // Process each post ID here
                        $notification = $this->getNotification($notificationID);

                        if ($numIterations++ < $start) {
                            continue;
                        }

                        if ($resultsCount > $postLimit) {
                            break;
                        } else {
                            $resultsCount++;
                        }

                        $html .= $notification->getHTML();
                    }
                }
            }

            if($resultsCount > $postLimit){
                $value = ((int)$page + 1);
                $html .=
                    <<<HTML
                            <input type='hidden' class='nextPage' value="$value">
                            <input type='hidden' class='noMorePosts' value='false'>
                    HTML;
            }else{
                $html .=
                    <<<HTML
                            <input type='hidden' class='noMorePosts' value="true">
                            <p class='noMorePosts_text'> No more posts to show! </p>
                    HTML;
            }

            echo $html;
        }
    }

