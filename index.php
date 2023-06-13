
<?php
    //<img class="user_profile_picture" src="<?php echo $user['profile_picture']
    include_once("./components/header.php");
    include_once("./lib/User.php");
    include_once("./lib/Post.php");


    if(isset($_POST['submit_new_post'])){
        $post = new Post($connection, $userLoggedIn);
        $post->submitPost($_POST['new_post_body'],'none');
        header("Location: index.php"); //Disables post resubmition by refreshing page
    }

?>


    <body>
        <?php include("./components/navbar.php") ?>

            <section class="user_details">
                <div class="user_profile_picture_container">
                    <a href="<?php echo $userLoggedIn; ?>">
                        <img src="<?php echo $user['profile_picture']?>" alt="Profile picture" width="100" height="100" >
                    </a>

                </div>
                <h2><?php echo $user['username'] ?></h2>
                <nav class="user_details_links">
                    <?php
                        $user = new User($connection, $userLoggedIn);
                        echo $user->getFullName();
                    ?>
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
            </section>


        <main>
            <section class="new_post_form_container">
                <form class="new_post_form" action="index.php" method="POST">
                    <textarea id="NewPostTextArea" name="new_post_body" placeholder="Say something..."></textarea>
                    <div class="new_post_form_buttons">
                        <button class="new_post_form_btn" type="submit" name="submit_new_post">
                            <i class="fa-solid fa-pen"></i>
                            Create new post
                        </button>
                    </div>
                    
                </form>

            </section>
            <section class="posts">

            <?php
                $post = new Post($connection, $userLoggedIn);
                $post->getPosts();
            ?>

                

            </section>
            
        </main>

        
    </body>

    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=', crossorigin='anonymous'></script>
    <script src="./assets/scripts/index.js"></script>
</html>