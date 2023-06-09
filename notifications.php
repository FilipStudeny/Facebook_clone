
<?php
require_once "./components/header.php";
require_once "./lib/config/DBconnection.php";
require_once "./lib/controllers/PostManager.php";
require_once "./lib/controllers/UserManager.php";

require_once "./lib/classes/FormError.php";
require_once "./lib/classes/User.php";
require_once "./lib/classes/Notification.php";


$connection = DBConnection::connect();
$userLoggedIn = $_SESSION['username'];

?>


<body>
<?php include_once ("./components/navbar.php");?>
<?php include("./components/sidebar.php"); ?>

<main>


    <section class="profile_user_content">

        <section class="notifications">


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
            url: "lib/Ajax_Notifications.php",
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn,
            cache:false,

            success: function(data) {
                $('#loading').hide();
                $('.notifications').html(data);

            }
        });

        // Click event handler for delete post button
        $('.notifications').on('click', '.accept', function() {
            const notificationID = $(this).data('notification-id');
            alert(notificationID);

            $.ajax({
                url: "lib/Ajax_FriendRequest.php",
                type: "POST",
                data: "&id=" + notificationID + "&action=accept" + "&userLoggedIn=" + userLoggedIn,

                success: function(data) {
                    console.log(data);
                },
                error: function(xhr, status, error) {
                    // Handle errors if the request fails
                    console.error(error);
                }
            });
        });

        $('.notifications').on('click', '.decline', function() {
            const notificationID = $(this).data('notification-id');


        });

        $(window).scroll(function() {
            var height = $('.notifications').height(); //Div containing posts
            var scroll_top = $(this).scrollTop();
            var page = $('.notifications').find('.nextPage').val();
            var noMorePosts = $('.notifications').find('.noMorePosts').val();

            if((document.documentElement.scrollTop + window.innerHeight - document.body.scrollHeight >= 0) && noMorePosts === 'false'){
                $('#loading').show();
                var ajaxReq = $.ajax({
                    url: "lib/Ajax_Notifications.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache:false,

                    success: function(response) {
                        $('.notifications').find('.nextPage').remove(); //Removes current .nextpage
                        $('.notifications').find('.noMorePosts').remove(); //Removes current .nextpage

                        $('#loading').hide();
                        $('.notifications').append(response);


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