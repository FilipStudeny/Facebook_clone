$(document).ready(function() {
    $('.profile_content_button').click(function() {
        var buttonText = $(this).text().trim().toLowerCase();

        // Remove active class from all buttons
        $('.profile_content_button').removeClass('selected');

        // Add active class to the clicked button
        $(this).addClass('selected');

        // Clear the existing content
        $('.profile_content').empty();

        // Load the corresponding content based on the button clicked
        if (buttonText === 'posts') {
            // Load posts content
            $('.profile_content').append('<h3>Posts</h3>');
            // Make an AJAX request or use any other method to load the user's posts
            // and append the posts to the '.profile_content' div.
        } else if (buttonText === 'comment') {
            // Load comments content
            $('.profile_content').append('<h3>Comments</h3>');
            // Make an AJAX request or use any other method to load the user's comments
            // and append the comments to the '.profile_content' div.
        } else if (buttonText === 'likes') {
            // Load likes content
            $('.profile_content').append('<h3>Likes</h3>');
            // Make an AJAX request or use any other method to load the user's liked posts/comments
            // and append the liked content to the '.profile_content' div.
        }
    });
});
