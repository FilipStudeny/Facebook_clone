<?php
    //<img class="user_profile_picture" src="<?php echo $user['profile_picture']
    include_once("../config/DBconnection.php");
    include_once("./lib/User.php");
    include_once("./lib/Post.php");

    if(isset($_SESSION['username'])){
        $userLoggedIn = $_SESSION['username'];
        $userDetails = mysqli_query($connection, "SELECT * FROM users WHERE username='$userLoggedIn'");
        $user = mysqli_fetch_array($userDetails); // get all user data as an array
    } else if (!strpos($_SERVER['REQUEST_URI'], 'login.php') && !strpos($_SERVER['REQUEST_URI'], 'register.php')) {
        header("Location: login.php");
        exit();
    }
?>

<script>
    function toggel(){
        let element = document.getElementById("comments");
        if(element.style.display == "block"){
            element.style.display = "none";
        }else{
            element.style.display = "block";
        }
    }
</script>

<?php

    //get ID of post
    if(isset($_GET['post_id'])){
        $postID = $_GET['post_id'];
    }

    $userQuery = mysqli_query($connection, "SELECT creator, for_who FROM posts WHERE id='$postID'");
    $row = mysqli_fetch_array($userQuery);

    $postedTo = $row['creator'];

    if(isset($_POST['comment' . $postID])){
        $postBody = $_POST['comment_body'];
    }
?>


<form action="CommentFrame.php?post_id=<?php echo $postID; ?>" id="comment_form" name="comment<?php echo $postID; ?>" method="POST">
    
</form>
