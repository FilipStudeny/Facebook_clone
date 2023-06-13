<?php

class Post{
    private $user;
    private mysqli $databaseConnection;

    public function __construct(mysqli $databaseConnection, string $username)
    {
        $this->user = new User($databaseConnection, $username);
        $this->databaseConnection = $databaseConnection;
    }

    public function submitPost($postBody, $postedToUser){
        $body = strip_tags($postBody);
        $body = mysqli_real_escape_string($this->databaseConnection, $body);

        //Allow for line breaks
        $body = str_replace('\r\n', "\n", $body);
        $body = nl2br($body);

        $checkEmptyPost = preg_replace('/\s+/','', $body); //Replaces empty spaces

        if($checkEmptyPost != ""){

            $dateOfcreation = date("Y-m-d H:i:s");
            $creator = $this->user->getUsername();

            if($postedToUser == $creator){
                $postedToUser = "none";
            }

            //Create new post
            $query = "INSERT INTO posts (body, creator, for_who, date_creation) VALUES ('$body', '$creator', '$postedToUser', '$dateOfcreation')";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $returnedPostID = mysqli_insert_id($this->databaseConnection);

            //Insert notification for post posted on user profile

            //Update post count for user
            $numberOfPostsForUser = $this->user->getNumberOfPosts();
            $numberOfPostsForUser++;
            mysqli_query($this->databaseConnection, "UPDATE users SET num_posts='$numberOfPostsForUser' WHERE username='$creator'");
        }
    }

    public function getPosts($data, int $postLimit) {
        $page = (int)$data['page'];
        $userLoggedIn = $this->user->getUsername();

        if($page == 1){
            $start = 0;
        }else{
            $start = ((int)$page - 1) * $postLimit;
        }

        $posts = "";
        $query = "SELECT * FROM posts WHERE deleted='0' ORDER BY id DESC";
        $dbQuery = mysqli_query($this->databaseConnection, $query);

        if(mysqli_num_rows($dbQuery) > 0){

            $numIterations = 0; //Number of iterations check
            $resultsCount = 1;

            while($postData = mysqli_fetch_array($dbQuery)){
                $postID = $postData['id'];
                $postBody = $postData['body'];
                $postCreator = $postData['creator'];
                $postedToUser = $postData['for_who'];
                $dateOfCreation = $postData['date_creation'];
                $likes = $postData['likes'];

                // Prepare user_to so it can be included even when posted not to a user
                $postedToUserLink = ($postedToUser == "none") ? "" : " to <a href='" . $postedToUser . "'>" . $postedToUser .  "</a>";

                //GET DATA ABOUT POST CREATOR
                //Check if user who posted, has their account closed - do not show their posts
                $postCreatorData = new User($this->databaseConnection, $postCreator);
                if($postCreatorData->isClosed()){
                    continue;
                }

                $loggedUser = new User($this->databaseConnection, $userLoggedIn);
                if($loggedUser->isFriendWith($postCreator)){

                    if($numIterations++ < $start)
                        continue;
                    

                    // Once 10 post were loaded break;
                    if($resultsCount > $postLimit){
                        break;
                    }else{
                        $resultsCount++;
                    }

                    $postCreatorProfilePicture = $postCreatorData->getProfilePicture();
                    $timeMessage = $this->getTimeMessage($dateOfCreation);

                    $postHTML = 
                    <<<HTML
                        <article class='post'>
                            <header class='post_header'>
                                <div class='post_profile_pic_container'>
                                    <img class='post_profile_picture' src='$postCreatorProfilePicture' width='50' height='50'>
                                </div>
                                <div class='post_header_user_info'>
                                    <nav class='post_header_user_links'>
                                        <a href='$postCreator'>$postCreator</a>
                                        
                                        <a href='$postedToUserLink'><span>to</span> asdad</a>
                                    </nav>
                                    <p class='post_time_of_creation'>$timeMessage</p>
                                </div>
                            </header>
                            <div class='post_body'>
                                <a class='post_body' href='post.php?id=$postID'>
                                    $postBody

                                </a>
                            </div>
                        </article>
                    HTML;

                    $posts .= $postHTML;
                }
            }

            if($resultsCount > $postLimit){
                $value = ((int)$page + 1);
                $posts .= 
                <<<HTML
                    <input type='hidden' class='nextPage' value="$value">
                    <input type='hidden' class='noMorePosts' value='false'>
                HTML;
            }else{
                $posts .=
                <<<HTML
                    <input type='hidden' class='noMorePosts' value="true">
                    <p class='noMorePosts_text'> No more posts to show! </p>
                HTML;
            }
        }
        
        echo $posts;
    }

    private function getTimeMessage(string $timeOfCreation)
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