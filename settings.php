<?php
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

    $userManager = new UserManager($connection, $userLoggedIn);
    $user = $userManager->getUser($userLoggedIn);

    $username = $user->getUsername();
    $fullname = $user->getFullName();
    $email = $user->getEmail();
    $firstname = $user->getFirstname();
    $surname = $user->getSurname();
    $profilePicture = $user->getProfilePicture();
    $userID = $user->getID();

?>

    <body>
    <?php include_once ("./components/navbar.php");?>
    <?php include("./components/sidebar.php"); ?>

    <main>
        <section class="modal_background">
            <section class="modal">
                <h2>Crop your image</h2>
                <input type="file" id="imageInput" accept="image/*">
                <label for="imageInput">
                    <div class="upload_image_icon">
                        <i class="fa-solid fa-upload"></i>
                    </div>
                </label>

                <div id="cropContainer">
                    <img id="imagePreview" src="" alt="Image Preview">
                </div>
                <button id="cropButton">Crop and Save</button>

            </section>
        </section>

        <section class="settings_container">
            <button class="change_profile_picture_button">
                <i class="fa-sharp fa-solid fa-image"></i>
                <div class="change_profile_picture_button_icon_background"></div>
                <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
            </button>



        </section>
        <section class="settings_container">
            <form class="settings_form" action="#" method="#">
                <div class="settings_form_input_box">
                    <label for="fUsername">Username:</label>
                    <input id="fUsername" type="text" value=<?php echo $username ?>>
                </div>
                <div class="settings_form_input_box">
                    <label for="fEmail">Email:</label>
                    <input id="fEmail" type="text" value=<?php echo $email ?>>
                </div>
                <div class="settings_form_input_box">
                    <label for="fEmail">New password:</label>
                    <input id="fEmail" type="text" placeholder="Enter new password">
                </div>
                <div class="settings_form_input_box">
                    <label for="fEmail">New password again:</label>
                    <input id="fEmail" type="text" placeholder="Enter new password">
                </div>
                <div class="settings_form_input_box">
                    <label for="fFirstname">Firstname:</label>
                    <input id="fFirstname" type="text" value=<?php echo $firstname ?>>
                </div>
                <div class="settings_form_input_box">
                    <label for="fSurname">Surname:</label>
                    <input id="fSurname" type="text" value=<?php echo $surname ?>>
                </div>
                <button type="submit" class="settings_form_button">Update profile data</button>
            </form>


        </section>
        <section class="settings_container">
            <div class="setting_options_container">
                <h2>Delete profile ?</h2>
                <button><i class="fa-solid fa-trash"></i>Delete my profile</button>
            </div>
        </section>
    </main>


    </body>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=' crossorigin='anonymous'></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.12/dist/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.12/dist/cropper.min.css">

    <script>
        $(document).ready(function() {
            var $imageInput = $('#imageInput');
            var $label = $('label[for="imageInput"]');
            var $imagePreview = $('#imagePreview');
            var $cropContainer = $('#cropContainer');
            var cropper;

            $(".change_profile_picture_button").click(function() {
                $("body").addClass("modal_open");
                $(".modal_background").show();
                $(".modal").show();
            });

            $(".modal_background").click(function(event) {
                // Check if the clicked element is the modal background
                if ($(event.target).is(".modal_background")) {
                    location.reload(); // Reload the page
                }
            });


            $imageInput.on('change', function(e) {
                var file = e.target.files[0];
                var reader = new FileReader();

                reader.onload = function(event) {
                    $imagePreview.attr('src', event.target.result);
                    $imagePreview.on('load', function() {
                        var windowWidth = $(window).width();
                        var windowHeight = $(window).height();
                        var imageWidth = $imagePreview.width();
                        var imageHeight = $imagePreview.height();

                        var scale = Math.min(windowWidth / imageWidth, windowHeight / imageHeight);

                        $imagePreview.css({
                            width: imageWidth * scale + 'px',
                            height: imageHeight * scale + 'px'
                        });

                        // Remove the previous cropped image
                        $cropContainer.empty();

                        // Create a new image element for cropping
                        var cropImage = document.createElement('img');
                        cropImage.src = $imagePreview.attr('src');
                        cropImage.onload = function() {
                            cropper = new Cropper(cropImage, {
                                aspectRatio: 1,
                                viewMode: 1
                            });
                        };

                        $cropContainer.append(cropImage);

                        $label.hide();
                    });
                };

                reader.readAsDataURL(file);
            });

            $('#cropButton').on('click', function() {
                var canvas = cropper.getCroppedCanvas();
                var croppedImageData = canvas.toDataURL('image/jpeg');
                var username = "<?php echo $userLoggedIn; ?>";

                // Create a FormData object to send the cropped image data via AJAX
                var formData = new FormData();
                formData.append('image', croppedImageData);
                formData.append('username', username);

                // Send the cropped image data to the server using AJAX
                $.ajax({
                    url: 'lib/Ajax_UploadImage.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        // Handle the response from the server
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        // Handle the error case
                        console.log(error);
                    }
                });

            });
        });
    </script>





    </html>

<?php
DBConnection::close();
