<?php
    require_once "./config/DBconnection.php";
    require_once "./lib/helpers.php";
    require_once "./lib/FormError.php";

    /** @var FormError[] $errors */
    $errors = array();
    if (isset($_POST['submit_btn'])) {

        $isEmpty = empty($_POST['reg_username']) || empty($_POST['reg_email']) || empty($_POST['reg_firstname']) || empty($_POST['reg_surname']) || empty($_POST['reg_password']) || empty($_POST['reg_confirm_password']);
        if($isEmpty){
            $errors[] = new FormError("empty_input", "Make sure all inputs are filled.");
        }
        
        // sanitize input
        $username = sanitizeInput($_POST['reg_username'], false);
        $email = sanitizeInput($_POST['reg_email'], false);
        $firstName = sanitizeInput($_POST['reg_firstname']);
        $surname = sanitizeInput($_POST['reg_surname']);
        $password = sanitizeInput($_POST['reg_password'], false);
        $confirmPassword = sanitizeInput($_POST['reg_confirm_password'], false);
        $registerDate = date("Y-m-d");

        $passwordMatch = $password == $confirmPassword;
        $isEmail = false;
        $isCorrectPasswordLenght = false;
        $isCorrectUsernameLenght = false;

        if(!$isEmpty){
            if(!$passwordMatch){
                $errors[] = new FormError("passwords_mismatch", "Passwords do not match.");
            }

            $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            if(!$isEmail){
                $errors[] = new FormError("invalid_email", "Make sure to enter valid email address.");
            }
    
            $isCorrectPasswordLenght = validateLength($password, 5, 50);
            if(!$isCorrectPasswordLenght){
                $errors[] = new FormError("password_size_error", "Password size must be betwean 5 and 50.");
            }

            $isCorrectUsernameLenght = validateLength($username, 5, 25);
            if(!$isCorrectUsernameLenght){
                $errors[] = new FormError("username_size_error", "Username size must be betwean 5 and 25.");
            }
        }

        $emailAlreadyUsed = false;
        $usernameAlreadyUsed = false;
        if(!$isEmpty && $passwordMatch && $isEmail && $isCorrectPasswordLenght && $isCorrectUsernameLenght){

            $dbResultQuery = mysqli_query($connection, "SELECT email FROM users WHERE email='$email'");
            $emailAlreadyUsed = mysqli_num_rows($dbResultQuery) > 0;

            if($emailAlreadyUsed){
                $errors[] = new FormError("email_in_use", "Email address already being used.");
            }

            $dbResultQuery = mysqli_query($connection, "SELECT username FROM users WHERE username='$username'");
            $usernameAlreadyUsed =  mysqli_num_rows($dbResultQuery) > 0;

            if($usernameAlreadyUsed){
                $errors[] = new FormError("username_is_used", "Username already being used.");
            }

        }

        if(!$isEmpty && $passwordMatch && $isEmail && $isCorrectPasswordLenght && $isCorrectUsernameLenght && !$emailAlreadyUsed && !$usernameAlreadyUsed){

            $hashedPassword = md5($password);
            $profilePicture = getRandomProfilePicture();

            mysqli_query($connection, "INSERT INTO users (username,email,password,profile_picture,num_likes,num_posts,friends,register_date,closed,firstname,surname) 
            VALUES ('$username', '$email', '$hashedPassword', '$profilePicture','0','0',',', '$registerDate', '0','$firstName', '$surname')");

            header("Location: login.php");
        }
    }

    function getRandomProfilePicture(): string{
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

        return $profilePicture;
    }
?>

<?php include("./components/header.php") ?>

<body>
    <div class="form_background_box">
        <div class="form_box">
            <h2>Social App</h2>
            <form class="auth_form" action="register.php" method="POST">
                <div class="form_input_box">
                    <label>Username:</label>
                    <input type="text" name="reg_username" placeholder="username">
                </div>
                <div class="form_input_box">
                    <label>Email:</label>
                    <input type="text" name="reg_email" placeholder="email@email.com">
                </div>
                <div class="form_input_box">
                    <label>Firstname:</label>
                    <input type="text" name="reg_firstname" placeholder="Your name">
                </div>
                <div class="form_input_box">
                    <label>Surname:</label>
                    <input type="text" name="reg_surname" placeholder="Your surname">
                </div>

                <div class="form_input_box">
                    <label>Password:</label>
                    <input type="password" name="reg_password" placeholder="password">
                </div>
                <div class="form_input_box">
                    <label>Confirm password:</label>
                    <input type="password" name="reg_confirm_password" placeholder="password">
                </div>
                <button type="submit" name="submit_btn">Create new account</button>
            </form>

            <div class="form_box_links">
                <a class="form_box_link" href="/login.php">
                    <i class="fa-solid fa-user"></i>
                    Sign into your account
                </a>
            </div>

            <?php if(!empty($errors)): ?>
                <div class="form_errors_container">
                    <ul class="form_errors_list">
                        <?php
                            foreach($errors as $error){
                                echo displayFormError($error->getMessage());
                            }
                        ?>
                    </ul>
                </div>
            <?php endif ?>

        </div>
    </div>

</body>

</html>
