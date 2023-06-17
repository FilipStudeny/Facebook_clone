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


        public function createNewComment(string $commentData, string $postID): void
        {
            $body = strip_tags($commentData);
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
                $userQuery = "UPDATE User SET comments = CONCAT(comments, '$returnedCommentID,') WHERE ID = $creatorID";
                $dbUserQuery = mysqli_query($this->databaseConnection, $userQuery);
            }
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


