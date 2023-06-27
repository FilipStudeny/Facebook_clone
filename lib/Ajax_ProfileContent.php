<?php
    require_once "config/DBconnection.php";
    require_once "controllers/PostManager.php";
    require_once "controllers/CommentsManager.php";
    require_once "controllers/LikesManager.php";

    $user_identifier = $_REQUEST['id'];
    $content = $_REQUEST['content_type'];
    $page = $_REQUEST['page'];
    $contentLimit = 10;

    if($content == "post"){
        $post = new PostManager(DBConnection::connect(), "");
        $post->getUserPosts($page, $user_identifier, $contentLimit);
    }else if($content == "comment"){
        $comment = new CommentsManager(DBConnection::connect(), "");
        $comment->getUserComments($page, $user_identifier, $contentLimit);
    }else if($content == "like"){
        $likes = new LikesManager(DBConnection::connect());
        $likes->getUserLikes($page, $user_identifier, $contentLimit);
    }




