
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
            <button>Posts</button>
            <button>Comment</button>
            <button>Likes</button>
        </div>

        <div class="profile_content">

        </div>

    </section>



</main>


</body>
<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=', crossorigin='anonymous'></script>

</html>