
<?php
    //<img class="user_profile_picture" src="<?php echo $user['profile_picture']

?>


    <?php include("./components/header.php") ?>

    <body>
        <?php include("./components/navbar.php") ?>
            <div class="user_details">
                <div class="user_profile_picture_container">
                    <a href="#">
                        <img src="<?php echo $user['profile_picture']?>" alt="Profile picture" width="100" height="100" >
                    </a>

                </div>
                <h2><?php echo $user['username'] ?></h2>
                <div class="user_details_links">
                    <a class="user_detail_link" href="#"><i class="fa-solid fa-house"></i></a>
                    <a class="user_detail_link"  href="#"><i class="fa-solid fa-address-card"></i></a>
                    <a class="user_detail_link"  href="#"><i class="fa-solid fa-message"></i></a>
                    <a class="user_detail_link"  href="#"><i class="fa-solid fa-gear"></i></a>
                    <a class="user_detail_link"  href="#"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>

            </div>
        <main>
            
            <div class="feed">

            </div>
            <div class="">

            </div>

        </main>

        
    </body>

    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=', crossorigin='anonymous'></script>

</html>