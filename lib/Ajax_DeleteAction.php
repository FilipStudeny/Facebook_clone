<?php
    require_once "config/DBconnection.php";
    require_once "controllers/PostManager.php";
    require_once "controllers/CommentsManager.php";

    $userLoggedIn = $_REQUEST['userLoggedIn'];
    $id = $_REQUEST['id'];
    $action = $_REQUEST['action'];

    if($action == "post"){
        $post = new PostManager(DBConnection::connect(), $userLoggedIn);
        $post->deletePost($id);
    }

    if($action == "comment"){
        $comment = new CommentsManager(DBConnection::connect(), $userLoggedIn);
        $comment->delete($id);
    }




