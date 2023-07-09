<?php

    require_once "./lib/config/DBconnection.php";

    if(isset($_SESSION['username'])){
        $userLoggedIn = $_SESSION['username'];
        $userDetails = mysqli_query(DBConnection::connect(), "SELECT * FROM user WHERE username='$userLoggedIn'");
        $user = mysqli_fetch_array($userDetails); //get all user data a array
    }
?>

<aside class="side_bar">
    <h1 class="page_title"><i class="fa-solid fa-kiwi-bird"></i>Facefook</h1>

    <a class="profile_picture_container" href="profile.php?user=<?php echo $userLoggedIn; ?>">
        <img src="<?php echo $user['profile_picture']?>" alt="Profile picture" width="100" height="100" >
    </a>

    <h2 class="side_bar_username"><?php echo $user['username'] ?></h2>
    <nav class="side_bar_nav_menu">
        <a class="menu_link" href="/">
            <i class="fa-solid fa-house"></i>
            <span>Home | Feed</span>
        </a>
        <a class="menu_link" href="profile.php?user=<?php echo $userLoggedIn; ?>">
            <i class="fa-solid fa-address-card"></i>
            <span>Your profile</span>
        </a>

        <a class="menu_link" href="notifications.php">
            <i class="fa-solid fa-bell"></i>
            <span>Notifications</span>
        </a>
        <a class="menu_link" href="messages.php">
            <i class="fa-solid fa-message"></i>
            <span>Chat | Messages</span>
        </a>

        <a class="menu_link" href="friends.php">
            <i class="fa-solid fa-users"></i>
            <span>Friends</span>
        </a>

        <a class="menu_link" href="settings.php">
            <i class="fa-solid fa-gear"></i>
            <span>Settings</span>
        </a>
        <a class="menu_link" href="/loggout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Loggout</span>
        </a>
    </nav>
</aside>