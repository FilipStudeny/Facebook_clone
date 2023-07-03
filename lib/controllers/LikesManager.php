<?php

    require_once __DIR__ . '/../classes/Post.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../classes/Comment.php';

    require_once __DIR__ . '/../controllers/PostManager.php';
    require_once __DIR__ . '/../controllers/CommentsManager.php';

    class LikesManager
    {

        private mysqli $databaseConnection;
        private string $loggedInUser;
        private PostManager $postManager;
        private CommentsManager $commentsManager;

        public function __construct(mysqli $databaseConnection, string $loggedInUser)
        {
            $this->loggedInUser = $loggedInUser;
            $this->databaseConnection = $databaseConnection;
            $this->postManager = new PostManager($databaseConnection, "");
            $this->commentsManager = new CommentsManager($databaseConnection, "");
        }


        public function getUserLikes(string $page, string $identifier, int $postLimit): void
        {

            if($page == 1){
                $start = 0;
            }else{
                $start = ((int)$page - 1) * $postLimit;
            }

            $html = "";
            $query = "SELECT likes FROM `user` WHERE username = '$identifier' ORDER BY ID DESC";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $numIterations = 0; //Number of iterations check
            $resultsCount = 1;

            if (mysqli_num_rows($dbQuery) > 0) {
                while ($postData = mysqli_fetch_array($dbQuery)) {

                    $array = array_map('trim', explode(',',  $postData['likes']));
                    $likesIDs = array_filter($array);

                    foreach ($likesIDs as $likeID) {

                        $isComment = str_contains($likeID, "@");

                        if ($isComment) {
                            $comment = $this->commentsManager->getComment(str_replace("@", "", $likeID));
                            $content = $comment->getHTML($this->loggedInUser);
                        } else {
                            $post = $this->postManager->getPost($likeID);
                            $content = $post->getHTML(false, $identifier);
                        }


                        // Process each post ID here

                        if ($numIterations++ < $start) {
                            continue;
                        }

                        if ($resultsCount > $postLimit) {
                            break;
                        } else {
                            $resultsCount++;
                        }

                        $html .= $content;
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