<?php

    require_once "./config/DBconnection.php";
    require_once "./lib/helpers.php";
    require_once "./lib/FormError.php";

    /** @var FormError[] $errors */
    $errors = array();
    if (isset($_POST['submit_btn'])) {
        $isEmpty = empty($_POST['log_email']) || empty($_POST['log_password']);
        if($isEmpty){
            $errors[] = new FormError("empty_input", "Make sure all inputs are filled.");
        }

        $email = sanitizeInput($_POST['log_email'], false);;
        $password = $_POST['log_password'];

        $isEmail = false;
        $isCorrectPasswordLenght = false;
        if(!$isEmpty){
            $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            if(!$isEmail){
                $errors[] = new FormError("invalid_email", "Make sure to enter valid email address.");
            }
    
            $isCorrectPasswordLenght = validateLength($password, 5, 50);
            if(!$isCorrectPasswordLenght){
                $errors[] = new FormError("password_size_error", "Password size must be betwean 5 and 50.");
            }
        }

        $userExists = false;
        if(!$isEmpty && $isEmail && $isCorrectPasswordLenght){
            $hashedPassword = md5($password);
            $dbResultQuery = mysqli_query($connection, "SELECT * FROM users WHERE email='$email' AND password='$hashedPassword'");
            $userExists = mysqli_num_rows($dbResultQuery) > 0;

            if(!$userExists){
                $errors[] = new FormError("no_user_found", "User account was not found.");
            }else{
                $userData = mysqli_fetch_array($dbResultQuery);
                $username = $userData['username'];
                
                $dbQuery = mysqli_query($connection, "SELECT * FROM users WHERE email='$email' AND closed='1'"); 
                $accountIsClosed = mysqli_num_rows($dbQuery) == 1;
    
                if($accountIsClosed){
                    mysqli_query($connection, "UPDATE users SET closed='0' WHERE email='$email'");
                }
    
                session_start();
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            }
        }
    }
?>


<?php include("./components/header.php") ?>
<body>

    <div class="form_background_box">
        <div class="form_box">
            <h2>Social App</h2>
            <form class="auth_form" action="login.php" method="POST">
                <div class="form_input_box">
                    <label>Email:</label>
                    <input type="text" name="log_email" placeholder="email@email.com">
                </div>
                <div class="form_input_box">
                    <label>Password:</label>
                    <input type="password" name="log_password" placeholder="password">
                </div>
                <button type="submit" name="submit_btn">Sign in to your account</button>
            </form>

            <div class="form_box_links">
                <a class="form_box_link" href="/register.php">
                    <i class="fa-solid fa-user"></i>
                    Create new profile
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