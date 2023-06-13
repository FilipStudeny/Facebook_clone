<?php

    include_once("../config/DBconnection.php");
    include_once("../lib/PostManager.php");


    /** @var int $postLimit */
    $postLimit = 10;
    $userLoggedIn = $_REQUEST['userLoggedIn'];
    
    $posts = new PostManager($connection, $userLoggedIn);
    $posts->getPostsFromFriends($_REQUEST, $postLimit);


?>