<?php
session_start(); // Start a session

$connection = mysqli_connect("localhost", "root", "", "SocialApp");
if (mysqli_connect_errno()) {
    echo "ERROR CONNECTING TO DB" . mysqli_connect_errno();
}

$errors = [];

$errorMessages = [
    'reg_name' => "Firstname must be between 2 and 25 characters",
    'reg_surname' => "Surname must be between 2 and 25 characters",
    'reg_username' => "Username must be between 2 and 25 characters",
    'reg_email' => "Invalid Email format",
    'email_in_use' => "Email already in use",
    'password_mismatch' => "Passwords do not match",
    'password_length' => "Your password must be between 5 and 30 characters"
];

if (isset($_POST['reg_button'])) {
    
    // Register form values
    $firstName = sanitizeInput($_POST['reg_name']);
    $surname = sanitizeInput($_POST['reg_surname']);
    $email = sanitizeInput($_POST['reg_email']);
    $username = sanitizeInput($_POST['reg_username']);
    $password = sanitizeInput($_POST['reg_password']);
    $password2 = sanitizeInput($_POST['reg_password_repeat']);

    $_SESSION['reg_name'] = $firstName;
    $_SESSION['reg_surname'] = $surname;
    $_SESSION['reg_username'] = $username;
    $_SESSION['reg_password'] = $password;
    $_SESSION['reg_password_repeat'] = $password2;

    $date = date("Y-m-d");

    if ($password != $password2) {
        $errors[] = 'password_mismatch';
    }

    if (!validateLength($firstName, 2, 25)) {
        $errors[] = 'reg_name';
    }

    if (!validateLength($surname, 2, 25)) {
        $errors[] = 'reg_surname';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'reg_email';
    } else {
        $emailCheck = mysqli_query($connection, "SELECT email FROM users WHERE email='$email'");
        $numOfRows = mysqli_num_rows($emailCheck);
        if ($numOfRows > 0) {
            $errors[] = 'email_in_use';
        }
    }

    if (!validateLength($password, 5, 30)) {
        $errors[] = 'password_length';
    }
}

// Helper function to sanitize input
function sanitizeInput($input)
{
    $input = strip_tags($input);
    $input = str_replace(' ', '', $input);
    return ucfirst(strtolower($input));
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
    <title>Social App | Register</title>
    <style>
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <form action="register.php" method="POST">
        <input type="text" name="reg_name" placeholder="Firstname" value="<?= $_SESSION['reg_name'] ?? '' ?>">
        <?php
        if (in_array('reg_name', $errors)) {
            displayError('reg_name', $errorMessages);
        }
        ?><br>

        <input type="text" name="reg_surname" placeholder="Surname" value="<?= $_SESSION['reg_surname'] ?? '' ?>">
        <?php
        if (in_array('reg_surname', $errors)) {
            displayError('reg_surname', $errorMessages);
        }
        ?><br>

        <input type="text" name="reg_username" placeholder="Username" value="<?= $_SESSION['reg_username'] ?? '' ?>">
        <?php
        if (in_array('reg_username', $errors)) {
            displayError('reg_username', $errorMessages);
        }
        ?><br><br>

        <input type="email" name="reg_email" placeholder="email@example.com" value="<?= $_SESSION['reg_email'] ?? '' ?>">
        <?php
        if (in_array('reg_email', $errors)) {
            displayError('reg_email', $errorMessages);
        } elseif (in_array('email_in_use', $errors)) {
            displayError('email_in_use', $errorMessages);
        }
        ?><br>

        <input type="password" name="reg_password" placeholder="Password"><br>
        <input type="password" name="reg_password_repeat" placeholder="Confirm password"><br><br><br>

        <input type="submit" name="reg_button" value="Register">
    </form>
</body>
</html>
