<?php

    include_once("../config/DBconnection.php");
    include_once("../lib/User.php");
    include_once("../lib/Post.php");

    /** @var int $postLimit */
    $postLimit = 10;
    $userLoggedIn = $_REQUEST['userLoggedIn'];
    
    $posts = new Post($connection, $userLoggedIn);
    $posts->getPosts($_REQUEST, $postLimit);


?>