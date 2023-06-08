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
        'email_or_password_inccorect' => "Email or Password is incorrect",
        'input_fields_empty' => "Make sure to fill out all input fields"
    ];
    $isEmpty = empty($_POST['log_email']) || empty($_POST['log_password']);

    if($isEmpty){
        $errors[] = 'input_fields_empty';
    }

    if (isset($_POST['log_button']) && !$isEmpty) {

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

            session_start();
            $_SESSION['username'] = $username;
            header("Location: index.php"); //redirect
            exit();
        }else{
            $errors[] = 'email_or_password_inccorect';
        }
    }

?>


<?php include("./components/header.php") ?>
<body>

    <div class="wrapper">
        
        <div class="box">
            <div class="box_header">
                <h3>Social app</h3>
            </div>
            <form action="login.php" method="POST">
                <input type="text" name="log_email" placeholder="email@email.com"><br>
                <input type="password" name="log_password" placeholder="password"><br>
                <br><br>
                
                <input type="submit" name="log_button" value="Sign in">

                <?php if(!empty($errors)): ?>
                    <div class="form_errors">
                        <?php
                            if (in_array('input_fields_empty', $errors)) {
                                displayError('input_fields_empty', $errorMessages);
                            }
                        ?>
                        <?php
                            if (in_array('email_or_password_inccorect', $errors)) {
                                displayError('email_or_password_inccorect', $errorMessages);
                            }
                        ?>
                    </div>
                <?php endif ?>

                <div class="box_links">
                    <a href="/register.php">Create a profile</a>
                    <a href="/">Home page</a>
                </div>
            </form>

        </div>
    </div>
    
</body>
</html>