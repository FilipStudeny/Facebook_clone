
<?php
//<img class="user_profile_picture" src="<?php echo $user['profile_picture']
require_once "./components/header.php";
require_once "./lib/config/DBconnection.php";
require_once "./lib/controllers/PostManager.php";
require_once "./lib/classes/FormError.php";


$connection = DBConnection::connect();
$userLoggedIn = $_SESSION['username'];

if (!isset($userLoggedIn)) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['submit_new_post'])){
    $post = new PostManager($connection, $userLoggedIn);
    $post->createNewPost($_POST['new_post_body'],'none');
    header("Location: index.php"); //Disables post resubmition by refreshing page
}

?>


<body>
<?php include_once ("./components/navbar.php");?>
<?php include("./components/sidebar.php"); ?>

<main>

    <section class="profile_user">
        <div class="profile_user_picture_container">
            <img src="<?php echo $user['profile_picture']?>" alt="Profile picture" width="100" height="100" >
        </div>

        <div class="profile_user_data">
            <div class="profile_user_data_header">
                <div>
                    <h2 class="profile_user_data_username">username</h2>
                    <div class="profile_user_name_container">
                        <p>firstname</p><p>surname</p>
                    </div>
                </div>
                <div class="profile_user_data_actions">
                    <button><i class="fa-solid fa-user-plus"></i>Add friend</button>
                    <button><i class="fa-solid fa-message"></i>Send a message</button>
                </div>
            </div>

            <p>email@gmail.com</p>
            <section class="profile_user_description">
                asdawd asdn awjdna jsnd oawnd jsadj najwnd jsdnajsnd jandd
            </section>

        </div>

    </section>
    <section class="profile_user_content">
        <div class="profile_content_switch">
            <button class="profile_content_button" value="posts">Posts</button>
            <button class="profile_content_button" value="comments">Comments</button>
            <button class="profile_content_button" value="likes">Likes</button>
        </div>

        <section class="profile_content">


        </section>

    </section>



</main>


</body>
<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=', crossorigin='anonymous'></script>
<script src="./assets/scripts/profile.js"></script>

<script>
    $(document).ready(function() {
        const userName = '<?php echo $_GET['user']; ?>';
        var page = 1; // Initial page number
        let selectedContent = "post";
        var isLoading = false; // Flag to indicate if a request is in progress

        loadPosts(selectedContent);

        // Function to load posts
        function loadPosts(content) {
            if (isLoading) {
                return; // Don't proceed if a request is already in progress
            }

            $('#loading').show();
            isLoading = true; // Set the flag to true

            $.ajax({
                url: "lib/Ajax_ProfileContent.php",
                type: "POST",
                data: "page=" + page + "&id=" + userName + "&content_type=" + content,
                cache: false,

                success: function(data) {
                    $('#loading').hide();
                    $('.profile_content').append(data); // Append the new content
                    isLoading = false; // Set the flag back to false after the request is complete
                }
            });
        }


        // Event listener for content buttons
        $('.profile_content_button').click(function() {
            var buttonText = $(this).text().trim().toLowerCase();

            // Remove selected class from all buttons
            $('.profile_content_button').removeClass('selected');

            // Add selected class to the clicked button
            $(this).addClass('selected');

            // Clear the existing content
            $('.profile_content').empty();

            // Load the corresponding content based on the button clicked
            if (buttonText === 'posts') {
                // Load posts content
                $('.profile_content').removeClass('comments likes').addClass('posts');
                selectedContent = "post"
                page = 1;
                loadPosts(selectedContent);
            }
            if (buttonText === 'comments') {
                // Load comments content
                $('.profile_content').removeClass('posts likes').addClass('comments');
                selectedContent = "comment"
                page = 1;
                loadPosts(selectedContent);
                // Make an AJAX request or use any other method to load the user's comments
                // and append the comments to the '.profile_content' div.
            }
            if (buttonText === 'likes') {
                // Load likes content
                $('.profile_content').removeClass('posts comments').addClass('likes');
                selectedContent = "like"
                page = 1;
                loadPosts(selectedContent);

                // Make an AJAX request or use any other method to load the user's liked posts/comments
                // and append the liked content to the '.profile_content' div.
            }
        });

        // Infinite scrolling
        $(window).scroll(function() {
            var height = $('.profile_content').height(); //Div containing posts
            var scroll_top = $(this).scrollTop();
            var page = $('.profile_content').find('.nextPage').val();
            var noMorePosts = $('.profile_content').find('.noMorePosts').val();

            if((document.documentElement.scrollTop + window.innerHeight - document.body.scrollHeight >= 0) && noMorePosts === 'false'){

                $.ajax({
                    url: "lib/Ajax_ProfileContent.php",
                    type: "POST",
                    data: "page=" + page + "&id=" + userName + "&content_type=" + selectedContent,
                    cache: false,

                    success: function(response) {
                        $('.profile_content').find('.nextPage').remove(); //Removes current .nextpage
                        $('.profile_content').find('.noMorePosts').remove(); //Removes current .nextpage

                        $('#loading').hide();
                        $('.profile_content').append(response);
                        isLoading = false; // Set the flag back to false after the request is complete

                    }
                });

            }

            return false;
        });
    });
</script>

</html>