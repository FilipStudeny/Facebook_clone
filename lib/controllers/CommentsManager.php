<?php

    require_once __DIR__ . '/../classes/Post.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../classes/Comment.php';
    require_once __DIR__ . '/../controllers/UserManager.php';
    require_once __DIR__ . '/../controllers/PostManager.php';

    class CommentsManager{


        private mysqli $databaseConnection;
        private string $loggedInUser;
        private UserManager $userManager;
        private PostManager $postManager;

        public function __construct(mysqli $databaseConnection, string $loggedInUser)
        {
            $this->databaseConnection = $databaseConnection;
            $this->loggedInUser = $loggedInUser;

            $this->userManager = new UserManager($databaseConnection);
            $this->postManager = new PostManager($databaseConnection, $loggedInUser);
        }

        public function __destruct(){
            mysqli_close($this->databaseConnection);
        }
        public function getComment(string $identifier): Comment{

            $query = "SELECT comment.*, (LENGTH(comment.likes) - LENGTH(REPLACE(comment.likes, ',', ''))) 
                    AS like_count, user.ID AS creator_id FROM comment JOIN user ON comment.creator_id = user.ID WHERE comment.ID = ?";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "s", $identifier);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $data = mysqli_fetch_array($result);

            mysqli_free_result($result);
            mysqli_stmt_free_result($statement);

            return new Comment($data);
        }

        public function create(string $data, string $postID): void
        {
            $body = strip_tags($data);
            $body = mysqli_real_escape_string($this->databaseConnection, $body);

            //Allow for line breaks
            $body = str_replace('\r\n', "\n", $body);
            $body = nl2br($body);
            $postIsEmpty = preg_replace('/\s+/','', $body); //Replaces empty spaces

            if($postIsEmpty == ""){
                header("Location: post.php?id=$postID");
                exit();
            }

            $createdAt = date("Y-m-d H:i:s");
            $creator = $this->userManager->getUser($this->loggedInUser);
            $creatorID = $creator->getID();

            $query = "INSERT INTO comment (comment, creator_id, post_id, date_of_creation) 
                    VALUES ('$body', '$creatorID', '$postID', '$createdAt')";

            mysqli_query($this->databaseConnection, $query);
            $returnedCommentID = mysqli_insert_id($this->databaseConnection);

            if ($returnedCommentID) {
                $userQuery = "UPDATE user SET comments = CONCAT(comments, '$returnedCommentID,') WHERE ID = $creatorID";
                mysqli_query($this->databaseConnection, $userQuery);

                // Update the comment count in the post table
                $updatePostQuery = "UPDATE post SET comments = CONCAT(comments, '$returnedCommentID,') WHERE ID = $postID";
                mysqli_query($this->databaseConnection, $updatePostQuery);
            }


            $post = $this->postManager->getPost($postID);
            $postCreator = $post->getCreatorUsername();
            if($postCreator == $this->loggedInUser){
                return;
            }

            $type = "comment";
            $date = date("Y-m-d H:i:s");
            $content = "User <a href='/profile.php?user={$creator->getUsername()}'>{$creator->getUsername()}</a> commented on your <a href='/post.php?id={$postID}'> post</a>. ";
            $post = $this->postManager->getPost($postID);
            $postCreator = $post->getCreatorID();

            // Prepare the SQL statement
            $query = "INSERT INTO notifications (type, for_user_id, content, date_of_creation, opened) 
                VALUES (?, ?, ?, ?, '0')";
            $statement = mysqli_prepare($this->databaseConnection, $query);

            // Bind the parameters
            mysqli_stmt_bind_param($statement, "ssss", $type, $postCreator, $content, $date);

            // Execute the prepared statement
            mysqli_stmt_execute($statement);

            // Check if the insertion was successful
            if (mysqli_stmt_affected_rows($statement) > 0) {
                $returnedID = mysqli_insert_id($this->databaseConnection);

                $userQuery = "UPDATE user SET notifications = CONCAT(notifications, '{$returnedID},') WHERE ID = ?";
                $userStatement = mysqli_prepare($this->databaseConnection, $userQuery);
                mysqli_stmt_bind_param($userStatement, "s", $postCreator);
                mysqli_stmt_execute($userStatement);
            }

        }

        public function like(string $commentID, string $username): void
        {

            // Check if the user has already liked the comment
            $comment = $this->getComment($commentID);
            $user = $this->userManager->getUser($username);
            $userID = $user->getID();

            $likes = explode(",", $comment->getLikes());
            if (in_array($userID, $likes)) {
                // Remove the user ID from the likes column in the comment table
                $updatedLikes = implode(",", array_diff($likes, [$userID]));
                $updateQuery = "UPDATE comment SET likes = ? WHERE ID = ?";
                $updateStatement = mysqli_prepare($this->databaseConnection, $updateQuery);
                mysqli_stmt_bind_param($updateStatement, "ss", $updatedLikes, $commentID);
                mysqli_stmt_execute($updateStatement);

                // Remove the comment ID from the likes column in the user table
                $userLikes = explode(",", $user->getLikes());
                $updatedUserLikes = implode(",", array_diff($userLikes, ["@" . $commentID]));
                $updateQuery = "UPDATE user SET likes = ? WHERE ID = ?";
                $updateStatement = mysqli_prepare($this->databaseConnection, $updateQuery);
                mysqli_stmt_bind_param($updateStatement, "ss", $updatedUserLikes, $userID);

            } else {
                // Add the user ID to the likes column in the comment table
                $updateQuery = "UPDATE comment SET likes = CONCAT(likes, ?, ',') WHERE ID = ?";
                $updateStatement = mysqli_prepare($this->databaseConnection, $updateQuery);
                mysqli_stmt_bind_param($updateStatement, "ss", $userID, $commentID);
                mysqli_stmt_execute($updateStatement);

                // Add the comment ID to the likes column in the user table
                $updateQuery = "UPDATE user SET likes = CONCAT(likes, ?, ',') WHERE ID = ?";
                $updateStatement = mysqli_prepare($this->databaseConnection,  $updateQuery);
                $newCommentLike = "@" . $commentID;
                mysqli_stmt_bind_param($updateStatement, "ss", $newCommentLike, $userID);

                $type = "comment_like";
                $date = date("Y-m-d H:i:s");
                $content = "User <a href='/profile.php?user={$user->getUsername()}'>{$user->getUsername()}</a> liked your <a href='/post.php?id={$comment->getPostID()}'>comment</a>. ";
                $post = $this->postManager->getPost($comment->getPostID());
                $commentCreatorID = $comment->getCreatorID();

                // Prepare the SQL statement
                $query = "INSERT INTO notifications (type, for_user_id, content, date_of_creation, opened) 
                VALUES (?, ?, ?, ?, '0')";
                $statement = mysqli_prepare($this->databaseConnection, $query);

                // Bind the parameters
                mysqli_stmt_bind_param($statement, "ssss", $type, $commentCreatorID, $content, $date);

                // Execute the prepared statement
                mysqli_stmt_execute($statement);

                // Check if the insertion was successful
                if (mysqli_stmt_affected_rows($statement) > 0) {
                    $returnedID = mysqli_insert_id($this->databaseConnection);

                    $userQuery = "UPDATE user SET notifications = CONCAT(notifications, '{$returnedID},') WHERE ID = ?";
                    $userStatement = mysqli_prepare($this->databaseConnection, $userQuery);
                    mysqli_stmt_bind_param($userStatement, "s", $commentCreatorID);
                    mysqli_stmt_execute($userStatement);
                }
            }
            mysqli_stmt_execute($updateStatement);


        }

        public function delete(string $commentID): void
        {
            $comment = $this->getComment($commentID);
            $commentPostID = $comment->getPostID();
            $commentCreatorID = $comment->getCreatorID();

            $user = $this->userManager->getUser($commentCreatorID);
            $post = $this->postManager->getPost($commentPostID);

            // Delete the comment from the comment table
            $deleteCommentQuery = "DELETE FROM comment WHERE ID = ?";
            $deleteCommentStatement = mysqli_prepare($this->databaseConnection, $deleteCommentQuery);
            mysqli_stmt_bind_param($deleteCommentStatement, "s", $commentID);
            mysqli_stmt_execute($deleteCommentStatement);

            // Remove the comment from the post's comments column
            $postComments = $post->getComments();
            $updatedPostComments = str_replace($commentID . ',', '', $postComments);
            $updatePostCommentsQuery = "UPDATE post SET comments = ? WHERE ID = ?";
            $updatePostCommentsStatement = mysqli_prepare($this->databaseConnection, $updatePostCommentsQuery);
            mysqli_stmt_bind_param($updatePostCommentsStatement, "ss", $updatedPostComments, $commentPostID);
            mysqli_stmt_execute($updatePostCommentsStatement);

            // Remove the comment from the user's comments column
            $userComments = $user->getComments();
            $updatedUserComments = str_replace($commentID . ',', '', $userComments);
            $updatedUserComments = str_replace($commentID, '', $updatedUserComments);
            $updateUserCommentsQuery = "UPDATE user SET comments = ? WHERE ID = ?";
            $updateUserCommentsStatement = mysqli_prepare($this->databaseConnection, $updateUserCommentsQuery);
            mysqli_stmt_bind_param($updateUserCommentsStatement, "ss", $updatedUserComments, $commentCreatorID);
            mysqli_stmt_execute($updateUserCommentsStatement);

            // Remove the comment from the user's likes column
            $commentLikesPrefix = '@' . $commentID . ',';
            $updatedUserLikes = str_replace($commentLikesPrefix, '', $user->getLikes());
            $updateUserLikesQuery = "UPDATE user SET likes = ? WHERE ID = ?";
            $updateUserLikesStatement = mysqli_prepare($this->databaseConnection, $updateUserLikesQuery);
            mysqli_stmt_bind_param($updateUserLikesStatement, "ss", $updatedUserLikes, $commentCreatorID);
            mysqli_stmt_execute($updateUserLikesStatement);

            // Delete the comment's likes from other users' likes columns
            $deleteCommentLikesQuery = "UPDATE user SET likes = REPLACE(likes, CONCAT('@', ?, ','), '') WHERE likes LIKE CONCAT('%@', ?, ',%')";
            $deleteCommentLikesStatement = mysqli_prepare($this->databaseConnection, $deleteCommentLikesQuery);
            mysqli_stmt_bind_param($deleteCommentLikesStatement, "ss", $commentID, $commentID);
            mysqli_stmt_execute($deleteCommentLikesStatement);

        }

        public function getUserComments(string $page, string $identifier, int $postLimit): void
        {
            if($page == 1){
                $start = 0;
            }else{
                $start = ((int)$page - 1) * $postLimit;
            }

            $posts = "";
            $query = "SELECT comments FROM `user` WHERE username = '$identifier' ORDER BY ID DESC";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $numIterations = 0; //Number of iterations check
            $resultsCount = 1;

            if (mysqli_num_rows($dbQuery) > 0) {
                while ($commentData = mysqli_fetch_array($dbQuery)) {

                    $array = array_map('trim', explode(',',  $commentData['comments']));
                    $commentIDs = array_filter($array);

                    foreach ($commentIDs as $commentID) {
                        // Process each post ID here
                        $comment = $this->getComment($commentID);

                        if ($numIterations++ < $start) {
                            continue;
                        }

                        if ($resultsCount > $postLimit) {
                            break;
                        } else {
                            $resultsCount++;
                        }

                        $posts .= $comment->getHTML($this->loggedInUser);
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
                            <p class='noMorePosts_text'> No more comments to show! </p>
                        HTML;
            }

            echo $posts;

        }


        public function getComments($data, string $postID, int $commentLimit): void
        {

            $page = (int)$data['page'];

            if($page == 1){
                $start = 0;
            }else{
                $start = ((int)$page - 1) * $commentLimit;
            }

            $comments = "";
            $query = "SELECT ID FROM comment WHERE post_id='$postID' ORDER BY ID DESC";
            $dbQuery = mysqli_query($this->databaseConnection, $query);

            $numIterations = 0; //Number of iterations check
            $resultsCount = 1;

            if(mysqli_num_rows($dbQuery) > 0){
                while($data = mysqli_fetch_array($dbQuery)){
                    $commentID = $data['ID'];

                    $comment = $this->getComment($commentID);

                    if($numIterations++ < $start) { continue; }
                    if($resultsCount > $commentLimit){
                        break;
                    }else{
                        $resultsCount++;
                    }

                    $comments .= $comment->getHTML($this->loggedInUser);
                }
            }

            if($resultsCount > $commentLimit){
                $value = ((int)$page + 1);
                $comments .=
                    <<<HTML
                            <input type='hidden' class='nextPage' value="$value">
                            <input type='hidden' class='noMorePosts' value='false'>
                        HTML;
            }else{
                $comments .=
                    <<<HTML
                            <input type='hidden' class='noMorePosts' value="true">
                            <p class='noMorePosts_text'> No more comments to show! </p>
                        HTML;
            }

            echo $comments;

        }

    }


