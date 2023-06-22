<?php
    require_once "config/DBconnection.php";
    require_once "controllers/PostManager.php";

    $userLoggedIn = $_REQUEST['userLoggedIn'];
    $postID = $_REQUEST['post_id'];

    $post = new PostManager(DBConnection::connect(), $userLoggedIn);
    $post->like($postID, $userLoggedIn);


