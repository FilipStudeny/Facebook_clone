<?php
    require_once "./config/DBconnection.php";

    if(isset($_SESSION['username'])){
        $userLoggedIn = $_SESSION['username'];
        $userDetails = mysqli_query($connection, "SELECT * FROM users WHERE username='$userLoggedIn'");
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
        <a class="header_link" href="<?php echo $user['username']; ?>">
            <i class="fa-solid fa-address-card"></i>
        </a>
        <a class="header_link" href="#">
            <i class="fa-solid fa-gear"></i>
        </a>

        <?php if ($userLoggedIn): ?>
            <a class="header_link" href="/logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        <?php else: ?>
            <a class="header_link" href="/login.php">
                <i class="fa-solid fa-right-to-bracket"></i>
            </a>
        <?php endif; ?>
    </nav>
</header>
