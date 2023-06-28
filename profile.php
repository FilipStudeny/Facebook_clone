
<?php
    require_once "./components/header.php";
    require_once "./lib/config/DBconnection.php";
    require_once "./lib/controllers/PostManager.php";
    require_once "./lib/classes/FormError.php";
    require_once "./lib/classes/User.php";


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


    $useridentifier = $_GET['user'];
    $user = new User($connection, $useridentifier);

    $username = $user->getUsername();
    $fullname = $user->getFullName();
    $email = $user->getEmail();
    $profilePicture = $user->getProfilePicture();
    $userID = $user->getID();

?>


<body>
<?php include_once ("./components/navbar.php");?>
<?php include("./components/sidebar.php"); ?>

<main>

    <section class="profile_user">
        <div class="profile_user_picture_container">
            <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
        </div>

        <div class="profile_user_data">
            <div class="profile_user_data_header">
                <div>
                    <h2 class="profile_user_data_username"><?php echo $username;  ?></h2>
                    <div class="profile_user_name_container">
                        <p><?php echo $fullname;  ?></p>
                    </div>
                </div>
                <div class="profile_user_data_actions">
                    <button id="addFriendButton" data-user-id="<?php echo $userID; ?>" data-user-action="friend"><i class="fa-solid fa-user-plus"></i>Add friend</button>
                    <button><i class="fa-solid fa-message"></i>Send a message</button>
                </div>
            </div>

            <section class="profile_user_description">
                asdawd asdn awjdna jsnd oawnd jsadj najwnd jsdnajsnd jandd
            </section>

        </div>

    </section>
    <section class="profile_user_content">
        <div class="profile_content_switch">
            <button class="profile_content_button selected" value="posts">Posts</button>
            <button class="profile_content_button" value="comments">Comments</button>
            <button class="profile_content_button" value="likes">Likes</button>
        </div>

        <section class="profile_content">


        </section>

    </section>



</main>


</body>
<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=' crossorigin='anonymous'></script>
<script src="./assets/scripts/profile.js"></script>
<script src="./assets/scripts/post.js"></script>
<script>
    $(document).ready(function() {
        const userName = '<?php echo $_GET['user']; ?>';
        const userLoggedIn = '<?php echo $userLoggedIn; ?>';
        var page = 1; // Initial page number
        let selectedContent = "post";
        var isLoading = false; // Flag to indicate if a request is in progress



        $('#addFriendButton').click(function() {
            const ID = $(this).data("user-id");
            const action = $(this).data("user-action");


            $.ajax({
                url: "lib/Ajax_FriendRequest.php",
                type: "POST",
                data: "&id=" + ID + "&action=" + action + "&userLoggedIn=" + userLoggedIn,

                success: function(data) {
                    console.log(data);
                },
                error: function(xhr, status, error) {
                    // Handle errors if the request fails
                    console.error(error);
                }
            });
        });

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

                    likeAction(userLoggedIn);
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
                selectedContent = "post"
                page = 1;
                loadPosts(selectedContent);
            }
            if (buttonText === 'comments') {
                // Load comments content
                selectedContent = "comment"
                page = 1;
                loadPosts(selectedContent);
                // Make an AJAX request or use any other method to load the user's comments
                // and append the comments to the '.profile_content' div.
            }
            if (buttonText === 'likes') {
                // Load likes content
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