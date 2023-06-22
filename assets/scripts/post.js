
function likePost(postId, userLoggedIn) {
    $.ajax({
        url: "/lib/Ajax_LikePost.php",
        type: "POST",
        data: "post_id=" + postId + "&userLoggedIn=" + userLoggedIn,
        cache: false,

        success: function(response) {
            console.log(response);
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}
