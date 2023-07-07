<?php
    require_once "config/DBconnection.php";
    require_once "controllers/UserManager.php";

    // Check if the request contains the image data
    if(isset($_POST['image'])) {
        $imageData = $_POST['image'];
        $username = $_POST['username'];

        // Remove the base64 data prefix
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);

        // Decode the base64 image data
        $decodedImageData = base64_decode($imageData);

        // Generate a unique filename for the uploaded image
        $filename = $username . '_profile_picture.jpg';

        // Define the destination directory for the uploaded image
        $uploadDirectory = '../assets/uploads/images/users/';

        // Create the directory if it doesn't exist
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        // Set the path to save the uploaded image
        $filePath = $uploadDirectory . $filename;

        // Save the image file
        if (file_put_contents($filePath, $decodedImageData)) {
            // Image upload success
            echo "Image uploaded successfully.";
            echo $uploadDirectory;
            $userManager = new UserManager(DBConnection::connect(), $username);
            $userManager->updateProfilePicture($username, $uploadDirectory . $filename);
        } else {
            // Image upload failed
            echo "Error uploading image.";
        }
    } else {
        // No image data found
        echo "No image data received.";
    }

