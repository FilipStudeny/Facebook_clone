<?php

    require_once "config/DBconnection.php";
    require_once "controllers/CommentsManager.php";


    $commentLimit = 10;
    $userLoggedIn = $_REQUEST['userLoggedIn'];
    $postID = $_REQUEST['postId'];


    $comments = new CommentsManager(DBConnection::connect(), $userLoggedIn);
    $comments->getComments($_REQUEST, $postID, $commentLimit);


