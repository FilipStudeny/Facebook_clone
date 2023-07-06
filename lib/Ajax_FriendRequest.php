<?php
    require_once "config/DBconnection.php";
    require_once "controllers/UserManager.php";

    $userLoggedIn = $_REQUEST['userLoggedIn'];
    $id = $_REQUEST['id'];
    $action = $_REQUEST['action'];

    $userManager = new UserManager(DBConnection::connect(), $userLoggedIn);

    if($action == "friend"){
        $userManager->sendFriendRequest($userLoggedIn, $id);
    }

    if($action == "remove_friend"){
        $userManager->removeFriend($id, $userLoggedIn);
    }

    if ($action == "accept"){
        $userManager->acceptFriendRequest($id);
    }





