<?php

    require_once "./components/header.php";
    require_once "./lib/config/DBconnection.php";
    require_once "./lib/controllers/CommentsManager.php";
    require_once "./lib/classes/FormError.php";
    require_once "./lib/classes/Post.php";
    require_once "./lib/classes/User.php";

    $connection = DBConnection::connect();
    $userLoggedIn = $_SESSION['username'];
    $postID = $_REQUEST['id'];

    if(isset($_POST['submit_new_comment'])){
            $post = new CommentsManager($connection, $userLoggedIn);

            $post->createNewComment($_POST['new_comment_body'],$postID);
            //header("Location: /post.php?id=$postID"); //Disables post resubmition by refreshing page
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

                echo $postID;

                ?>

            </section>
            <section class="new_post_form_container">
                <form class="new_post_form" action="post.php?id=<?php echo $postID ?>" method="POST">
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
    <script>
        const userLoggedIn = '<?php echo $userLoggedIn; ?>';
        const postID = '<?php echo $postID; ?>';

        $(document).ready(function() {
            $('#loading').show();

            //Original ajax request for loading first posts
            $.ajax({
                url: "lib/Ajax_Comments.php",
                type: "POST",
                data: "page=1&userLoggedIn=" + userLoggedIn + "&postId=" + postID,
                cache:false,

                success: function(data) {
                    $('#loading').hide();
                    $('.posts').html(data);
                }
            });

            $(window).scroll(function() {
                var height = $('.posts').height(); //Div containing posts
                var scroll_top = $(this).scrollTop();
                var page = $('.posts').find('.nextPage').val();
                var noMorePosts = $('.posts').find('.noMorePosts').val();

                if((document.documentElement.scrollTop + window.innerHeight - document.body.scrollHeight >= 0) && noMorePosts == 'false'){
                    $('#loading').show();
                    var ajaxReq = $.ajax({
                        url:  "lib/Ajax_Comments.php",
                        type: "POST",
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&postId=" + postID,
                        cache:false,



                        success: function(response) {
                            $('.posts').find('.nextPage').remove(); //Removes current .nextpage
                            $('.posts').find('.noMorePosts').remove(); //Removes current .nextpage

                            $('#loading').hide();
                            $('.posts').append(response);
                        }
                    });

                }

                return false;

            });
        });
    </script>

</html>