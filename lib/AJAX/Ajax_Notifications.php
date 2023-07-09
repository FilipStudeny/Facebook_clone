<?php

    require_once "../config/DBconnection.php";
    require_once "../controllers/NotificationsManager.php";

    $postLimit = 10;
    $userLoggedIn = $_REQUEST['userLoggedIn'];
    $page = $_REQUEST['page'];


    $notificationsManager = new NotificationsManager(DBConnection::connect(), $userLoggedIn);
    $notificationsManager->getAll($page, $userLoggedIn ,$postLimit);


