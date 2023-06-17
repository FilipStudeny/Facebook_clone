<?php

    include_once("./components/header.php");
    include_once("./lib/Post.php");
    include_once("./lib/User.php");
    include_once("./lib/CommentsManager.php");



    
    if(isset($_POST['submit_new_comment'])){
        $post = new CommentsManager($connection, $userLoggedIn);
        $post->createNewComment($_POST['new_post_body'],'none');
        echo "post created";
        header("Location: index.php"); //Disables post resubmition by refreshing page
    }

?>


<body>
        <?php include("./components/navbar.php") ?>
        <?php include("./components/sidebar.php") ?>

        <main>
            <section class="post_detail">

                <?php
                    $postID = $_GET['id'];
                    $post = new Post($connection, $postID);
                    echo $post->render(true);
                ?>

            


            </section>
            <section class="new_post_form_container">
                <form class="new_post_form" action="post.php" method="POST">
                    <textarea id="NewPostTextArea" name="new_comment_body" placeholder="Say something..."></textarea>
                    <div class="new_post_form_buttons">
                        <button class="new_post_form_btn" type="submit" name="submit_new_comment">
                            <i class="fa-solid fa-pen"></i>
                            Post comment
                        </button>
                    </div>
                    
                </form>

            </section>

            <seciton class="posts">
                <article class='comment'>
                    <header class='comment_header'>
                        <div class='comment_profile_pic_container'>
                            <img class='comment_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='comment_header_user_info'>
                            <nav class='comment_header_user_links'>
                                <a href='$creatorCreatorUsername'>$creatorCreatorUsername</a>
                            </nav>
                            <p class='comment_time_of_creation'>$postDate</p>
                        </div>
                    </header>
                    <div class='comment_body'>
                        $postBodyHTML
                    </div>
                </article>

                <article class='comment'>
                    <header class='comment_header'>
                        <div class='comment_profile_pic_container'>
                            <img class='comment_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='comment_header_user_info'>
                            <nav class='comment_header_user_links'>
                                <a href='$creatorCreatorUsername'>$creatorCreatorUsername</a>
                            </nav>
                            <p class='comment_time_of_creation'>$postDate</p>
                        </div>
                    </header>
                    <div class='comment_body'>
                        $postBodyHTML
                    </div>
                </article>

                <article class='comment'>
                    <header class='comment_header'>
                        <div class='comment_profile_pic_container'>
                            <img class='comment_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='comment_header_user_info'>
                            <nav class='comment_header_user_links'>
                                <a href='$creatorCreatorUsername'>$creatorCreatorUsername</a>
                            </nav>
                            <p class='comment_time_of_creation'>$postDate</p>
                        </div>
                    </header>
                    <div class='comment_body'>
                        $postBodyHTML
                    </div>
                </article>

                <article class='comment'>
                    <header class='comment_header'>
                        <div class='comment_profile_pic_container'>
                            <img class='comment_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='comment_header_user_info'>
                            <nav class='comment_header_user_links'>
                                <a href='$creatorCreatorUsername'>$creatorCreatorUsername</a>
                            </nav>
                            <p class='comment_time_of_creation'>$postDate</p>
                        </div>
                    </header>
                    <div class='comment_body'>
                        $postBodyHTML
                    </div>
                </article>

                <article class='comment'>
                    <header class='comment_header'>
                        <div class='comment_profile_pic_container'>
                            <img class='comment_profile_picture' src='$creatorCreatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='comment_header_user_info'>
                            <nav class='comment_header_user_links'>
                                <a href='$creatorCreatorUsername'>$creatorCreatorUsername</a>
                            </nav>
                            <p class='comment_time_of_creation'>$postDate</p>
                        </div>
                    </header>
                    <div class='comment_body'>
                        $postBodyHTML
                    </div>
                </article>

            </seciton>
            
            <div id="loading" class="loading_icon">
                <h2>Loading ...</h2>
            </div>

        </main>

        
    </body>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=', crossorigin='anonymous'></script>
    <script src="./assets/scripts/index.js"></script>

</html>