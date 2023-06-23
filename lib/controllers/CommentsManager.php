<?php

    require_once __DIR__ . '/../classes/Post.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../classes/Comment.php';

    class CommentsManager{


        private mysqli $databaseConnection;
        private string $loggedInUser;

        public function __construct(mysqli $databaseConnection, string $loggedInUser)
        {
            $this->databaseConnection = $databaseConnection;
            $this->loggedInUser = $loggedInUser;
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
            $creator = new User($this->databaseConnection, $this->loggedInUser);
            $creatorID = $creator->getID();

            $query = "INSERT INTO comment (comment, creator_id, post_id, date_of_creation) 
                    VALUES ('$body', '$creatorID', '$postID', '$createdAt')";

            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $returnedCommentID = mysqli_insert_id($this->databaseConnection);

            if ($returnedCommentID) {
                $userQuery = "UPDATE user SET comments = CONCAT(comments, '$returnedCommentID,') WHERE ID = $creatorID";
                $dbUserQuery = mysqli_query($this->databaseConnection, $userQuery);

                // Update the comment count in the post table
                $updatePostQuery = "UPDATE post SET comments = CONCAT(comments, '$returnedCommentID,') WHERE ID = $postID";
                $dbUpdatePostQuery = mysqli_query($this->databaseConnection, $updatePostQuery);

            }
        }

        public function like(string $commentID, string $username): void
        {
            // Check if the user has already liked the comment
            $comment = new Comment($this->databaseConnection, $commentID);
            $user = new User($this->databaseConnection, $username);
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
                $updatedUserLikes = implode(",", array_diff($userLikes, [$commentID]));
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
                $updateStatement = mysqli_prepare($this->databaseConnection, $updateQuery);
                mysqli_stmt_bind_param($updateStatement, "ss", $commentID, $userID);
            }
            mysqli_stmt_execute($updateStatement);
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

                    $comment = new Comment($this->databaseConnection, $commentID);

                    if($numIterations++ < $start) { continue; }
                    if($resultsCount > $commentLimit){
                        break;
                    }else{
                        $resultsCount++;
                    }

                    $comments .= $comment->getHTML();
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


