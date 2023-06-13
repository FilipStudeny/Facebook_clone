
<?php
    //<img class="user_profile_picture" src="<?php echo $user['profile_picture']
    include_once("./components/header.php");

    if(isset($_POST['submit_new_post'])){
        $post = new PostManager($connection, $userLoggedIn);
        $post->createNewPost($_POST['new_post_body'],'','none');
        header("Location: index.php"); //Disables post resubmition by refreshing page
    }

?>


    <body>
        <?php include("./components/navbar.php") ?>
        <?php include("./components/sidebar.php") ?>

        <main>
            <section class="new_post_form_container">
                <form class="new_post_form" action="index.php" method="POST">
                    <textarea id="NewPostTextArea" name="new_post_body" placeholder="Say something..."></textarea>
                    <div class="new_post_form_buttons">
                        <button class="new_post_form_btn" type="submit" name="submit_new_post">
                            <i class="fa-solid fa-pen"></i>
                            Create new post
                        </button>
                    </div>
                    
                </form>

            </section>
            <section class="posts">


            </section>
            
            <div id="loading" class="loading_icon">
                <h2>Loading ...</h2>
            </div>

            

        </main>

        
    </body>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=', crossorigin='anonymous'></script>
    <script src="./assets/scripts/index.js"></script>
 
    <script>
	var userLoggedIn = '<?php echo $userLoggedIn; ?>';

	$(document).ready(function() {
		$('#loading').show();

		//Original ajax request for loading first posts 
		$.ajax({
			url: "lib/AjaxCalls.php",
			type: "POST",
			data: "page=1&userLoggedIn=" + userLoggedIn,
			cache:false,

			success: function(data) {
				$('#loading').hide();
				$('.posts').html(data);
			}
		});

		$(window).scroll(function() {
			var height = $('.posts').height(); //Div containing posts
			var scroll_top = $(this).scrollTop();
			var page = $('.posts').find('.nextPage').val();
			var noMorePosts = $('.posts').find('.noMorePosts').val();

            if((document.documentElement.scrollTop + window.innerHeight - document.body.scrollHeight >= 0) && noMorePosts == 'false'){
                alert(userLoggedIn);
            	$('#loading').show();
				var ajaxReq = $.ajax({
					url: "lib/AjaxCalls.php",
					type: "POST",
					data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
					cache:false,

                    

					success: function(response) {
						$('.posts').find('.nextPage').remove(); //Removes current .nextpage 
						$('.posts').find('.noMorePosts').remove(); //Removes current .nextpage 

						$('#loading').hide();
						$('.posts').append(response);
					}
				});

			}

			return false;

		}); 
	});
	</script>

</html>