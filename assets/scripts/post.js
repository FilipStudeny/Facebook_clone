

const likeAction = (userLoggedIn) => {

    $(".like_button").click(function() {
        const ID = $(this).data("likable-id");
        const action = $(this).data("likable-name");

        const likeCountElement = $(this).find("span");
        const likeCount = parseInt(likeCountElement.text());

        if ($(this).hasClass("liked")) {
            // Decrease the likeCount by 1
            const updatedCount = likeCount - 1;
            likeCountElement.text(updatedCount);
            $(this).removeClass("liked");
        } else {
            // Increase the likeCount by 1
            const updatedCount = likeCount + 1;
            likeCountElement.text(updatedCount);
            $(this).addClass("liked");
        }

        likeContent(ID, userLoggedIn, action);
    });

};


function likeContent(postId, userLoggedIn, action) {
    $.ajax({
        url: "/lib/AJAX/Ajax_LikeAction.php",
        type: "POST",
        data: "id=" + postId + "&userLoggedIn=" + userLoggedIn + "&action=" + action,
        cache: false,

        success: function(response) {
            console.log(response);
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}
