<?php
    require_once "./config/DBconnection.php";

    if(isset($_SESSION['username'])){
        $userLoggedIn = $_SESSION['username'];
        $userDetails = mysqli_query($connection, "SELECT * FROM users WHERE username='$userLoggedIn'");
        $user = mysqli_fetch_array($userDetails); //get all user data a array
    }



?>

<header>
    <h1>Social App</h1>
    <input>
    <h2><?php echo $userLoggedIn ?></h2>
    <div class="header_buttons">
        <a href="/login.php">Login</a>
        <a href="/register.php">Register</a>
    </div>

</header>