<?php

    require_once __DIR__ . '/../classes/Post.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../controllers/UserManager.php';

    class PostManager{

        private mysqli $databaseConnection;
        private string $loggedInUser;
        private UserManager $userManager;

        public function __construct(mysqli $databaseConnection, string $loggedInUser)
        {
            $this->databaseConnection = $databaseConnection;
            $this->loggedInUser = $loggedInUser;
            $this->userManager = new UserManager($databaseConnection);
        }

        public function getPost(string $identifier): Post{
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
            mysqli_stmt_bind_param($statement, "s", $identifier);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $data = mysqli_fetch_array($result);
            return new Post($data);
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
            $creator = $this->userManager->getUser($this->loggedInUser);
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
            $post = $this->getPost($postID);
            $user = $this->userManager->getUser($username);
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

        public function getUserPosts(string $page, string $identifier, int $postLimit): void
        {

            if($page == 1){
                $start = 0;
            }else{
                $start = ((int)$page - 1) * $postLimit;
            }

            $posts = "";
            $query = "SELECT posts FROM `user` WHERE username = '$identifier' ORDER BY ID DESC";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $numIterations = 0; //Number of iterations check
            $resultsCount = 1;

            if (mysqli_num_rows($dbQuery) > 0) {
                while ($postData = mysqli_fetch_array($dbQuery)) {

                    $array = array_map('trim', explode(',',  $postData['posts']));
                    $postIDs = array_filter($array);

                    foreach ($postIDs as $postID) {
                        // Process each post ID here
                        $post = $this->getPost($postID);

                        if ($numIterations++ < $start) {
                            continue;
                        }

                        if ($resultsCount > $postLimit) {
                            break;
                        } else {
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

        public function getPostsFromFriends($data, int $postLimit): void
        {
            $loggedInUser = $this->userManager->getUser($this->loggedInUser);
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

                    $post = $this->getPost($postID);
                    $postCreator = $this->userManager->getUser($post->getCreatorUsername());

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