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

      $post->create($_POST['new_comment_body'],$postID);
      header("Location: /post.php?id=$postID"); //Disables post resubmition by refreshing page
    }
?>


<body>
        <?php include("./components/navbar.php") ?>
        <?php include("./components/sidebar.php") ?>

        <main>
            <section class="post_detail">

                <?php
                    $postManager = new PostManager($connection, "");
                    $postID = $_GET['id'];
                    $post = $postManager->getPost($postID);
                    $post->render(true, $userLoggedIn);

                ?>

            </section>
            <section class="section">
                <form class="form" action="post.php?id=<?php echo $postID ?>" method="POST">
                    <textarea id="NewPostTextArea" name="new_comment_body" placeholder="Say something..."></textarea>
                    <button class="form_btn" type="submit" name="submit_new_comment">
                        <i class="fa-solid fa-pen"></i>
                        Post comment
                    </button>
                </form>
            </section>

            <section class="comments">

            </section>
            
            <div id="loading" class="loading_icon">
                <h2>Loading ...</h2>
            </div>

        </main>

        
    </body>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=' crossorigin='anonymous'></script>
    <script src="./assets/scripts/index.js"></script>
    <script src="./assets/scripts/post.js"></script>

    <script>
        const userLoggedIn = '<?php echo $userLoggedIn; ?>';
        const postID = '<?php echo $postID; ?>';

        $(document).ready(function() {
            $('#loading').show();

            //Original ajax request for loading first posts
            $.ajax({
                url: "lib/AJAX/Ajax_Comments.php",
                type: "POST",
                data: "page=1&userLoggedIn=" + userLoggedIn + "&postId=" + postID,
                cache:false,

                success: function(data) {
                    $('#loading').hide();
                    $('.comments').html(data);

                    likeAction(userLoggedIn);
                }
            });

            // Click event handler for delete post button
            $('.comments').on('click', '.delete_button', function() {
                alert("asdasd");
                var commendID = $(this).data('comment-id');

                // Confirm deletion with the user (optional)
                if (confirm("Are you sure you want to delete this post?")) {
                    // Send AJAX request to delete the post
                    $.ajax({
                        url: "lib/AJAX/Ajax_DeleteAction.php",
                        type: "POST",
                        data: "action=comment&userLoggedIn=" + userLoggedIn + "&id=" + commendID,
                        cache: false,

                        success: function (data){
                            console.log(data)
                        },
                        error: function (err){
                            console.log(err);
                        }
                    });
                }
            });

            $(window).scroll(function() {
                var height = $('.comments').height(); //Div containing posts
                var scroll_top = $(this).scrollTop();
                var page = $('.comments').find('.nextPage').val();
                var noMorePosts = $('.comments').find('.noMorePosts').val();

                if((document.documentElement.scrollTop + window.innerHeight - document.body.scrollHeight >= 0) && noMorePosts === 'false'){
                    $('#loading').show();
                    var ajaxReq = $.ajax({
                        url:  "lib/AJAX/Ajax_Comments.php",
                        type: "POST",
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&postId=" + postID,
                        cache:false,



                        success: function(response) {
                            $('.comments').find('.nextPage').remove(); //Removes current .nextpage
                            $('.comments').find('.noMorePosts').remove(); //Removes current .nextpage

                            $('#loading').hide();
                            $('.comments').append(response);
                        }
                    });

                }

                return false;

            });
        });
    </script>
</html>

<?php
DBConnection::close();