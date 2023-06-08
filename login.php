<?php
    require './config/DBconnection.php';

    $errors = [];
    $errorMessages = [
        'reg_name' => "Firstname must be between 2 and 25 characters",
        'reg_surname' => "Surname must be between 2 and 25 characters",
        'reg_username' => "Username must be between 2 and 25 characters",
        'reg_email' => "Invalid Email format",
        'email_in_use' => "Email already in use",
        'password_mismatch' => "Passwords do not match",
        'password_length' => "Your password must be between 5 and 30 characters",
        'username_in_use' => "Username is already being used",
        'success' => "Registration successful. Sign in!",
        'email_or_password_inccorect' => "Email or Password is incorrect"
    ];

    if (isset($_POST['log_button'])) {

        $email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL);
        $password = md5($_POST['log_password']);

        $chekDB = mysqli_query($connection, "SELECT * FROM users WHERE email='$email' AND password='$password'");
        $checkLogin = mysqli_num_rows($chekDB);

        if($checkLogin == 1){
            $row = mysqli_fetch_array($chekDB);
            $username = $row['username'];

            $userAccountClosed = mysqli_query($connection, "SELECT * FROM users WHERE email='$email' AND profile_closed='yes'");
            if(mysqli_num_rows($userAccountClosed) == 1){
                $reopeAccount = mysqli_query($connection, "UPDATE users SET profile_closed='no' WHERE email='$email'");
            }

            $_SESSION['username'] = $username;
            header("Location: index.php"); //redirect
            exit();
        }else{
            $errors[] = 'email_or_password_inccorect';
        }
    }
    // Helper function to sanitize input
    function sanitizeInput($input, $firstLetterUP=true)
    {
        $input = strip_tags($input);
        $input = str_replace(' ', '', $input);

        if($firstLetterUP){
            return ucfirst(strtolower($input));
        }else{
            return $input;
        }
    }

    // Helper function to validate length
    function validateLength($input, $minLength, $maxLength)
    {
        $length = strlen($input);
        return ($length >= $minLength && $length <= $maxLength);
    }

    // Helper function to display error messages
    function displayError($errorCode, $errorMessages)
    {
        if (isset($errorMessages[$errorCode])) {
            echo "<span class='error'>{$errorMessages[$errorCode]}</span><br>";
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social App | Login</title>
</head>
<body>

    <form action="login.php" method="POST">
        <input type="text" name="log_email" placeholder="email@email.com"><br>
        <input type="password" name="log_password" placeholder="password"><br>
        <br><br>
        <?php
        if (in_array('email_or_password_inccorect', $errors)) {
            displayError('email_or_password_inccorect', $errorMessages);
        }
        ?>
        <input type="submit" name="log_button" value="Sign in">
    </form>
    
</body>
</html>