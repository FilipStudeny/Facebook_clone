<?php
    require_once "./components/header.php";
    require_once "./lib/config/DBconnection.php";
    require_once "./lib/controllers/PostManager.php";
    require_once "./lib/controllers/UserManager.php";



    $connection = DBConnection::connect();
    $userLoggedIn = $_SESSION['username'];

    if (!isset($userLoggedIn)) {
        header("Location: login.php");
        exit();
    }


?>


<body>
<?php include_once ("./components/navbar.php");?>
<?php include("./components/sidebar.php"); ?>

<main>

    <section class="profile_user_content">


        <section class="friends">


            <div id="loading" class="loading_icon">
                <h2>Loading ...</h2>
            </div>

        </section>

    </section>

</main>


</body>
<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=' crossorigin='anonymous'></script>
<script>
    var userLoggedIn = '<?php echo $userLoggedIn; ?>';

    $(document).ready(function() {
        $('#loading').show();

        //Original ajax request for loading first posts
        $.ajax({
            url: "lib/Ajax_Friends.php",
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn,
            cache:false,

            success: function(data) {
                $('#loading').hide();
                $('.friends').html(data);

            }
        });

        // Click event handler for delete post button
        $('.friends').on('click', '.removeFriendButton', function() {
            const ID = $(this).data("user-id");
            const action = $(this).data("user-action");

            alert(ID)
            alert(action)

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


        $(window).scroll(function() {
            var height = $('.friends').height(); //Div containing posts
            var scroll_top = $(this).scrollTop();
            var page = $('.friends').find('.nextPage').val();
            var noMorePosts = $('.friends').find('.noMorePosts').val();

            if((document.documentElement.scrollTop + window.innerHeight - document.body.scrollHeight >= 0) && noMorePosts === 'false'){
                $('#loading').show();
                var ajaxReq = $.ajax({
                    url: "lib/Ajax_Friends.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache:false,

                    success: function(response) {
                        $('.friends').find('.nextPage').remove(); //Removes current .nextpage
                        $('.friends').find('.noMorePosts').remove(); //Removes current .nextpage

                        $('#loading').hide();
                        $('.friends').append(response);


                    }
                });

            }

            return false;
        });
    });


</script>

</html>