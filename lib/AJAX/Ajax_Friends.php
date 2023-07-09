<?php

    require_once "../config/DBconnection.php";
    require_once "../controllers/UserManager.php";

    $loggedInUser = $_REQUEST['userLoggedIn'];
    $page = $_REQUEST['page'];
    $contentLimit = 10;

    $users = new UserManager(DBConnection::connect(), $loggedInUser);
    $users->loadFriends($page, $loggedInUser ,$contentLimit);





