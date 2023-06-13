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
    <?php include("./components/sidebar.php") ?>


        <main>
            <?php

                if(isset($_GET['id'])){
                    echo $_GET['id'];
                }
            ?>

            <article class='post'>
                <header class='post_header'>
                    <div class='post_profile_pic_container'>
                        <img class='post_profile_picture' src='$postCreatorProfilePicture' width='50' height='50'>
                    </div>
                    <div class='post_header_user_info'>
                        <nav class='post_header_user_links'>
                            <a href='$postCreator'>$postCreator</a>
                            <a href='$postedToUserLink'><span>to</span> asdad</a>
                        </nav>
                        <p class='post_time_of_creation'>$timeMessage</p>
                        </div>
                </header>
                <div class='post_body'>
                    $postBody
                </div>
            </article>
        </main>

        
    </body>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=', crossorigin='anonymous'></script>
    <script src="./assets/scripts/index.js"></script>


</html>