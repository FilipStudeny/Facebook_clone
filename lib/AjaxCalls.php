<?php


    require_once "config/DBconnection.php";
    require_once "controllers/PostManager.php";


    $postLimit = 10;
    $userLoggedIn = $_REQUEST['userLoggedIn'];



    $posts = new PostManager(DBConnection::connect(), $userLoggedIn);
    $posts->getPostsFromFriends($_REQUEST, $postLimit);


