
<?php
    require_once "./components/header.php";
    require_once "./lib/config/DBconnection.php";
    require_once "./lib/controllers/PostManager.php";
    require_once "./lib/controllers/UserManager.php";

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
    $userManager = new UserManager($connection, $userLoggedIn);
    $user = $userManager->getUser($useridentifier);

    $username = $user->getUsername();
    $fullname = $user->getFullName();
    $email = $user->getEmail();
    $profilePicture = $user->getProfilePicture();
    $userID = $user->getID();

    $myProfile = $username === $userLoggedIn;

    $friendRequestAlreadySend = $userManager->friendRequestAlreadySent($userID);
    $isFriend = $user->isFriendWith($userManager->getUser($userLoggedIn)->getID());

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
                        <?php
                        echo $isFriend;
                        ?>
                    </div>
                </div>
                <div class="profile_user_data_actions">

                    <?php if (!$myProfile): ?>
                        <?php if (!$isFriend): ?>
                            <?php if(!$friendRequestAlreadySend): ?>
                                <button id="addFriendButton" class="addFriendButton" data-user-id="<?php echo $userID; ?>" data-user-action="friend"><i class="fa-solid fa-user-plus"></i>Add friend</button>
                            <?php else: ?>
                                <button id="addFriendButton" class="removeFriendButton" data-user-id="<?php echo $userID; ?>" data-user-action="friend"><i class="fa-solid fa-user-plus"></i>Remove friend request</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button id="addFriendButton" class="removeFriendButton" data-user-id="<?php echo $userID; ?>" data-user-action="remove_friend"><i class="fa-solid fa-user-plus"></i>Remove friend</button>

                        <?php endif; ?>

                        <button class="sendMessageButton"><i class="fa-solid fa-message"></i>Send a message</button>
                    <?php endif; ?>

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
            <div id="loading" class="loading_icon">
                <h2>Loading ...</h2>
            </div>

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

        // Click event handler for delete post button
        $('.profile_content').on('click', '.post_header_delete_post_button', function() {
            var postID = $(this).data('post-id');

            // Confirm deletion with the user (optional)
            if (confirm("Are you sure you want to delete this post?")) {
                // Send AJAX request to delete the post
                $.ajax({
                    url: "lib/Ajax_DeleteAction.php",
                    type: "POST",
                    data: "action=post&userLoggedIn=" + userLoggedIn + "&id=" + postID,
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

        // Click event handler for delete post button
        $('.profile_content').on('click', '.delete_button', function() {
            alert("asdasd");
            var commendID = $(this).data('comment-id');

            // Confirm deletion with the user (optional)
            if (confirm("Are you sure you want to delete this post?")) {
                // Send AJAX request to delete the post
                $.ajax({
                    url: "lib/Ajax_DeleteAction.php",
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
                data: "page=" + page + "&id=" + userName + "&content_type=" + content + "&loggedInUser=" + userLoggedIn,
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

        // Add click event handler for the add/remove friend button
        $('.addFriendButton').click(function() {
            var button = $(this);
            var isFriendRequestSent = button.hasClass('removeFriendButton');

            // Update button appearance based on friend request status
            if (isFriendRequestSent) {
                // Friend request already sent, change button to "Add friend"
                button.removeClass('removeFriendButton');
                button.html('<i class="fa-solid fa-user-plus"></i>Add friend');
            } else {
                // Friend request not sent, change button to "Remove friend request"
                button.addClass('removeFriendButton');
                button.html('<i class="fa-solid fa-user-plus"></i>Remove friend request');
            }
        });

        // Add click event handler for the send message button
        $('.sendMessageButton').click(function() {
            var button = $(this);

            // Update button appearance
            button.html('<i class="fa-solid fa-message"></i>Message sent');
            button.attr('disabled', true);
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