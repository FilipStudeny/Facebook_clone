<?php


    class PostManager{

        private mysqli $databaseConnection;

        public function __construct(mysqli $databaseConnection)
        {
            $this->databaseConnection = $databaseConnection;
        }


        public function createNewPost(string $postData, string $postCreator, string $postedToUser){
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
            $creator = new User($this->databaseConnection, $postCreator);

            $query = "INSERT INTO posts (body, creator, for_who, date_creation) VALUES ('$body', '$creator', '$postedToUser', '$postCreatedAt')";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $returnedPostID = mysqli_insert_id($this->databaseConnection);

            //Insert notification for post posted on user profile

            //Update post count for user
            $numberOfPostsForUser = $creator->getNumberOfPosts();
            $numberOfPostsForUser++;
            mysqli_query($this->databaseConnection, "UPDATE users SET num_posts='$numberOfPostsForUser' WHERE username='$creator'");
        }

        public function getPosts(){
            
        }

    }


?>