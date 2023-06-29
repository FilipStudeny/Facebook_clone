<?php

    require_once "./lib/config/DBconnection.php";

    if(isset($_SESSION['username'])){
        $userLoggedIn = $_SESSION['username'];
        $userDetails = mysqli_query(DBConnection::connect(), "SELECT * FROM user WHERE username='$userLoggedIn'");
        $user = mysqli_fetch_array($userDetails); //get all user data a array
    }
?>

<header class="page_header">
    <div class="logo_container">
        <h1 class="header_title"><i class="fa-solid fa-kiwi-bird"></i>Facefook</h1>
    </div>
    <section class="searchbar_container">

        <i class="fa-solid fa-binoculars search_icon"></i>
        <input class="header_search_bar" name="header_search_bar">

    </section>
    <nav class="buttons_container">
        <?php if ($userLoggedIn): ?>
            <a class="header_link" href="profile.php?user=<?php echo $userLoggedIn; ?>">
                <i class="fa-solid fa-address-card"></i>
            </a>
            <a class="header_link" href="#">
                <i class="fa-solid fa-gear"></i>
            </a>

            <a class="header_link" href="notifications.php">
                <i class="fa-solid fa-bell"></i>
            </a>
            <a class="header_link" href="/loggout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        <?php else: ?>
            <a class="header_link" href="/login.php">
                <i class="fa-solid fa-right-to-bracket"></i>
            </a>
        <?php endif; ?>
    </nav>
</header>

