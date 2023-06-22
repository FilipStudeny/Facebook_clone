<?php

    require_once __DIR__ . '/../classes/Post.php';
    require_once __DIR__ . '/../classes/User.php';

    class PostManager{

        private mysqli $databaseConnection;
        private string $loggedInUser;

        public function __construct(mysqli $databaseConnection, string $loggedInUser)
        {
            $this->databaseConnection = $databaseConnection;
            $this->loggedInUser = $loggedInUser;
        }

        public function createNewPost(string $postData, string $postedToUser): void
        {
            $body = strip_tags($postData);
            $body = mysqli_real_escape_string($this->databaseConnection, $body);
    
            //Allow for line breaks
            $body = str_replace('\r\n', "\n", $body);
            $body = nl2br($body);
            $postIsEmpty = preg_replace('/\s+/','', $body); //Replaces empty spaces

            if($postIsEmpty == ""){
                header("Location: index.php");
                exit();
            }

            $postCreatedAt = date("Y-m-d H:i:s");
            $creator = new User($this->databaseConnection, $this->loggedInUser);
            $creatorID = $creator->getID();

            $query = "INSERT INTO post (postBody, creator_id, created_for_who, date_of_creation) VALUES ('$body', '$creatorID', '$postedToUser', '$postCreatedAt')";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $returnedPostID = mysqli_insert_id($this->databaseConnection);

            if ($returnedPostID) {
                $userQuery = "UPDATE User SET posts = CONCAT(posts, '$returnedPostID,') WHERE ID = $creatorID";
                $dbUserQuery = mysqli_query($this->databaseConnection, $userQuery);
            }
            //Insert notification for post posted on user profile
        }

        public function like(string $postID, string $username): void
        {
            // Check if the user has already liked the post
            $post = new Post($this->databaseConnection, $postID);
            $user = new User($this->databaseConnection, $username);
            $userID = $user->getID();

            $likes = explode(",", $post->getLikes());
            if (in_array($userID, $likes)) {
                // Remove the user ID from the likes column in the post table
                $updatedLikes = implode(",", array_diff($likes, [$userID]));
                $updateQuery = "UPDATE post SET likes = ? WHERE ID = ?";
                $updateStatement = mysqli_prepare($this->databaseConnection, $updateQuery);
                mysqli_stmt_bind_param($updateStatement, "ss", $updatedLikes, $postID);
                mysqli_stmt_execute($updateStatement);

                // Remove the post ID from the likes column in the user table
                $userLikes = explode(",", $user->getLikes());
                $updatedUserLikes = implode(",", array_diff($userLikes, [$postID]));
                $updateQuery = "UPDATE user SET likes = ? WHERE ID = ?";
                $updateStatement = mysqli_prepare($this->databaseConnection, $updateQuery);
                mysqli_stmt_bind_param($updateStatement, "ss", $updatedUserLikes, $userID);
            } else {
                // Add the user ID to the likes column in the post table
                $updateQuery = "UPDATE post SET likes = CONCAT(likes, ?, ',') WHERE ID = ?";
                $updateStatement = mysqli_prepare($this->databaseConnection, $updateQuery);
                mysqli_stmt_bind_param($updateStatement, "ss", $userID, $postID);
                mysqli_stmt_execute($updateStatement);

                // Add the post ID to the likes column in the user table
                $updateQuery = "UPDATE user SET likes = CONCAT(likes, ?, ',') WHERE ID = ?";
                $updateStatement = mysqli_prepare($this->databaseConnection, $updateQuery);
                mysqli_stmt_bind_param($updateStatement, "ss", $postID, $userID);
            }
            mysqli_stmt_execute($updateStatement);
        }


        public function getPostsFromFriends($data, int $postLimit): void
        {
            $loggedInUser = new User($this->databaseConnection, $this->loggedInUser);
            $page = (int)$data['page'];
    
            if($page == 1){
                $start = 0;
            }else{
                $start = ((int)$page - 1) * $postLimit;
            }
    
            $posts = "";
            $query = "SELECT ID FROM post ORDER BY ID DESC";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $numIterations = 0; //Number of iterations check
            $resultsCount = 1;

            if(mysqli_num_rows($dbQuery) > 0){
                while($postData = mysqli_fetch_array($dbQuery)){
                    $postID = $postData['ID'];

                    $post = new Post($this->databaseConnection, $postID);
                    $postCreator = new User($this->databaseConnection, $post->getCreatorUsername());

                    if($loggedInUser->isFriendWith($postCreator->getUsername())){

                        if($numIterations++ < $start) { continue; }
                        if($resultsCount > $postLimit){
                            break;
                        }else{
                            $resultsCount++;
                        }

                        $posts .= $post->getHTML(false);
                    }
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

            echo $posts;
        }
    }
?>