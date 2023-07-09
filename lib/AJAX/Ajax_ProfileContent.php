<?php
    require_once "../config/DBconnection.php";
    require_once "../controllers/PostManager.php";
    require_once "../controllers/CommentsManager.php";
    require_once "../controllers/LikesManager.php";

    $user_identifier = $_REQUEST['id'];
    $loggedInUser = $_REQUEST['loggedInUser'];
    $content = $_REQUEST['content_type'];
    $page = $_REQUEST['page'];
    $contentLimit = 10;

    if($content == "post"){
        $post = new PostManager(DBConnection::connect(), $loggedInUser);
        $post->getUserPosts($page, $user_identifier, $contentLimit);
    }else if($content == "comment"){
        $comment = new CommentsManager(DBConnection::connect(), $loggedInUser);
        $comment->getUserComments($page, $user_identifier, $contentLimit);
    }else if($content == "like"){
        $likes = new LikesManager(DBConnection::connect(), $loggedInUser);
        $likes->getUserLikes($page, $user_identifier, $contentLimit);
    }




