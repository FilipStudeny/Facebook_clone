<?php
    require_once "./config/DBconnection.php";

    if(isset($_SESSION['username'])){
        $userLoggedIn = $_SESSION['username'];
        $userDetails = mysqli_query($connection, "SELECT * FROM users WHERE username='$userLoggedIn'");
        $user = mysqli_fetch_array($userDetails); // get all user data as an array
    } else if (!strpos($_SERVER['REQUEST_URI'], 'login.php') && !strpos($_SERVER['REQUEST_URI'], 'register.php')) {
        header("Location: login.php");
        exit();
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social App</title>
    <script src="https://kit.fontawesome.com/a2c399c19b.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="../assets/styles/main.css"/>

</head>
