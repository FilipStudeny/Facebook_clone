<aside class="user_profile_sidebar">
    <div class="user_profile_picture_container">
        <a href="<?php echo $userLoggedIn; ?>">
            <img src="<?php echo $user['profile_picture']?>" alt="Profile picture" width="100" height="100" >
        </a>

    </div>
    <h2 class="user_username_sidebar"><?php echo $user['username'] ?></h2>
    <nav class="user_details_links">
        <a class="user_detail_link" href="/">
            <i class="fa-solid fa-house"></i>
            <span>Home | Feed</span>
        </a>
        <a class="user_detail_link" href="#">
            <i class="fa-solid fa-address-card"></i>
            <span>Your profile</span>
        </a>
        <a class="user_detail_link" href="#">
            <i class="fa-solid fa-message"></i>
            <span>Chat | Messages</span>
        </a>
        <a class="user_detail_link" href="#">
            <i class="fa-solid fa-gear"></i>
            <span>Settings</span>
        </a>
        <a class="user_detail_link" href="/loggout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Loggout</span>
        </a>
    </nav>
</aside>