<?php

    require_once "./lib/helpers.php";
    require_once "./lib/config/DBconnection.php";
    require_once "./lib/classes/FormError.php";
    require_once "./lib/controllers/UserManager.php";

    $connection = DBConnection::connect();
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

        $passwordMatch = $password == $confirmPassword;
        $isEmail = false;
        $isCorrectPasswordLength = false;
        $isCorrectUsernameLength = false;

        if (!$isEmpty) {
            if (!$passwordMatch) {
                $errors[] = new FormError("passwords_mismatch", "Passwords do not match.");
            }

            $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (!$isEmail) {
                $errors[] = new FormError("invalid_email", "Make sure to enter a valid email address.");
            }

            $isCorrectPasswordLength = validateLength($password, 5, 50);
            if (!$isCorrectPasswordLength) {
                $errors[] = new FormError("password_size_error", "Password size must be between 5 and 50.");
            }

            $isCorrectUsernameLength = validateLength($username, 3, 25);
            if (!$isCorrectUsernameLength) {
                $errors[] = new FormError("username_size_error", "Username size must be between 3 and 25.");
            }
        }

        if (!$isEmpty && $passwordMatch && $isEmail && $isCorrectPasswordLength && $isCorrectUsernameLength) {
            $userManager = new UserManager($connection);
            $validationErrors = $userManager->checkIfAlreadyInUse($email, $username);

            if (empty($validationErrors)) {
                $profilePicture = getRandomProfilePicture();

                $userManager->createNew([
                    'username' => $username,
                    'password' => $password,
                    'email' => $email,
                    'profile_picture' => $profilePicture,
                    'firstname' => $firstName,
                    'surname' => $surname
                ]);

                header("Location: login.php");
                exit();
            } else {
                $errors = array_merge($errors, $validationErrors);
            }
        }
    }

    function getRandomProfilePicture(): string{
        $random = rand(1,5);

        return match ($random) {
            2 => "./assets/defaults/user_icon_02.png",
            3 => "./assets/defaults/user_icon_03.png",
            4 => "./assets/defaults/user_icon_04.png",
            default => "./assets/defaults/user_icon.png",
        };
    }
?>

<?php include("./components/header.php") ?>

<body>
    <main class="width_70">
        <section class="section">
            <div class="form_container">
                <h2>Social App</h2>
                <form class="form" action="register.php" method="POST">
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
                    <button class="form_submit" type="submit" name="submit_btn">Create new account</button>
                </form>

                <div class="form_box_links">
                    <a class="form_box_link" href="/login.php">
                        <i class="fa-solid fa-user"></i>
                        Sign into your account
                    </a>
                </div>
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
        </section>
    </main>
</body>

</html>
