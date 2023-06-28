<?php
    require_once "config/DBconnection.php";
    require_once "controllers/UserManager.php";

    $userLoggedIn = $_REQUEST['userLoggedIn'];
    $id = $_REQUEST['id'];
    $action = $_REQUEST['action'];


    if($action == "friend"){
        $userManager = new UserManager(DBConnection::connect());
        $userManager->sendFriendRequest($userLoggedIn, $id);
    }





