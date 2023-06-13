<?php

    include_once("../lib/User.php");
    include_once("../lib/Post.php");


    class PostManager{

        private mysqli $databaseConnection;
        private string $loggedInUser;

        public function __construct(mysqli $databaseConnection, string $loggedInUser)
        {
            $this->databaseConnection = $databaseConnection;
            $this->loggedInUser = $loggedInUser;
        }


        public function createNewPost(string $postData, string $postedToUser){
            $body = strip_tags($postData);
            $body = mysqli_real_escape_string($this->databaseConnection, $body);
    
            //Allow for line breaks
            $body = str_replace('\r\n', "\n", $body);
            $body = nl2br($body);
            $postIsEmpty = preg_replace('/\s+/','', $body); //Replaces empty spaces

            if($postIsEmpty){
                exit();
            }

            $postCreatedAt = date("Y-m-d H:i:s");
            $creator = new User($this->databaseConnection, $this->loggedInUser);

            $query = "INSERT INTO posts (body, creator, for_who, date_creation) VALUES ('$body', '$creator', '$postedToUser', '$postCreatedAt')";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $returnedPostID = mysqli_insert_id($this->databaseConnection);

            //Insert notification for post posted on user profile

            //Update post count for user
            $numberOfPostsForUser = $creator->getNumberOfPosts();
            $numberOfPostsForUser++;
            mysqli_query($this->databaseConnection, "UPDATE users SET num_posts='$numberOfPostsForUser' WHERE username='$creator'");
        }

        public function getPostsFromFriends($data, int $postLimit) {
            $loggedInUser = new User($this->databaseConnection, $this->loggedInUser);
            $page = (int)$data['page'];
            
    
            if($page == 1){
                $start = 0;
            }else{
                $start = ((int)$page - 1) * $postLimit;
            }
    
            $posts = "";
            $query = "SELECT id FROM posts WHERE deleted='0' ORDER BY id DESC";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
    
            if(mysqli_num_rows($dbQuery) > 0){
    
                $numIterations = 0; //Number of iterations check
                $resultsCount = 1;
    
                while($postData = mysqli_fetch_array($dbQuery)){
                    $postID = $postData['id'];

                    $post = new Post($this->databaseConnection, $postID);
                    $postCreator = new User($this->databaseConnection, $post->getCreator());

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
    }
?>