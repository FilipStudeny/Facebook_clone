<?php
    require_once "./config/DBconnection.php";
    require_once "./lib/helpers.php";

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
        'input_fields_empty' => "Make sure to fill out all input fields"
    ];
    $isEmpty = empty($_POST['reg_name']) || empty($_POST['reg_surname']) || empty($_POST['reg_username']) || empty($_POST['reg_email']) || empty($_POST['reg_password']) || empty($_POST['reg_password_repeat']);

    if($isEmpty){
        $errors[] = 'input_fields_empty';
    }

    if (isset($_POST['reg_button']) && !$isEmpty) {
        
        // Register form values
        $firstName = sanitizeInput($_POST['reg_name']);
        $surname = sanitizeInput($_POST['reg_surname']);
        $email = sanitizeInput($_POST['reg_email'], false);
        $username = sanitizeInput($_POST['reg_username'], false);
        $password = sanitizeInput($_POST['reg_password'], false);
        $password2 = sanitizeInput($_POST['reg_password_repeat'], false);

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

        $checkUsername = mysqli_query($connection, "SELECT username FROM users WHERE username='$username'");
        $numOfRows = mysqli_num_rows($checkUsername);
        if ($numOfRows > 0) {
            $errors[] = 'username_in_use';
        }

            $password = md5($password); 
            $random = rand(1,5);
            $profilePicture = "";

            switch ($random) {
                case 1:
                    $profilePicture = "./assets/defaults/user_icon.png";
                    break;
                case 2:
                    $profilePicture = "./assets/defaults/user_icon_02.png";
                    break;
                case 3:
                    $profilePicture = "./assets/defaults/user_icon_03.png";
                    break;
                case 4:
                    $profilePicture = "./assets/defaults/user_icon_04.png";
                    break;
                
                default:
                    $profilePicture = "./assets/defaults/user_icon.png";
                    break;
            }

        $query = mysqli_query($connection, "INSERT INTO users (username,firstname, surname, email, password, register_date, profile_picture, posts, likes, profile_closed, friends) 
        VALUES ('$username','$firstName', '$surname', '$email', '$password', '$date', '$profilePicture', '0', '0', 'no', ',')");
        
        $_SESSION['reg_name'] = "";
        $_SESSION['reg_surname'] = "";
        $_SESSION['reg_username'] = "";
        $_SESSION['reg_password'] = "";
        $_SESSION['reg_password_repeat'] = "";    

        session_destroy();
        header("Location: login.php");

    }


?>

<?php include("./components/header.php") ?>

<body>

    <div class="wrapper">
            
        <div class="box">
            <div class="box_header">
                <h3>Social app</h3>
            </div>
            <form action="register.php" method="POST">
                <input type="text" name="reg_name" placeholder="Firstname" value="<?= $_SESSION['reg_name'] ?? '' ?>">
                <input type="text" name="reg_surname" placeholder="Surname" value="<?= $_SESSION['reg_surname'] ?? '' ?>">
                <input type="text" name="reg_username" placeholder="Username" value="<?= $_SESSION['reg_username'] ?? '' ?>">
                <input type="email" name="reg_email" placeholder="email@example.com" value="<?= $_SESSION['reg_email'] ?? '' ?>">
                <input type="password" name="reg_password" placeholder="Password"><br>
                <input type="password" name="reg_password_repeat" placeholder="Confirm password"><br><br><br>

                <input type="submit" name="reg_button" value="Sign up">

                <?php if(!empty($errors)): ?>
                    <div class="form_errors">
                        <?php
                            if (in_array('input_fields_empty', $errors)) {
                                displayError('input_fields_empty', $errorMessages);
                            }
                        ?>
                        <?php
                            if (in_array('reg_name', $errors)) {
                                displayError('reg_name', $errorMessages);
                            }
                        ?>
                        <?php
                            if (in_array('reg_surname', $errors)) {
                                displayError('reg_surname', $errorMessages);
                            }
                        ?>
                        <?php
                            if (in_array('reg_username', $errors)) {
                                displayError('reg_username', $errorMessages);
                            }

                            if (in_array('reg_username', $errors)) {
                                displayError('reg_username', $errorMessages);
                            }
                        ?>
                        <?php
                            if (in_array('reg_email', $errors)) {
                                displayError('reg_email', $errorMessages);
                            } elseif (in_array('email_in_use', $errors)) {
                                displayError('email_in_use', $errorMessages);
                            }
                        ?>
                    </div>
                <?php endif ?>
                <div class="box_links">
                    <a href="/login.php">Sign into your profile</a>
                    <a href="/">Home page</a>
                </div>
            </form>

        </div>
    </div>
</body>

</html>
