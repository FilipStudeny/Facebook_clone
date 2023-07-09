<?php
    require_once "./components/header.php";

    require_once "./lib/helpers.php";
    require_once "./lib/config/DBconnection.php";
    require_once "./lib/controllers/UserManager.php";
    require_once "./lib/classes/FormError.php";

    $connection = DBConnection::connect();


    $errors = array();

    if (isset($_POST['submit_btn'])) {
        $isEmpty = empty($_POST['log_email']) || empty($_POST['log_password']);

        if ($isEmpty) {
            $errors[] = new FormError("empty_input", "Make sure all inputs are filled.");
        } else {
            $email = sanitizeInput($_POST['log_email'], false);
            $password = $_POST['log_password'];

            $userManager = new UserManager($connection, "");

            $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (!$isEmail) {
                $errors[] = new FormError("invalid_email", "Make sure to enter a valid email address.");
            }

            if (!$userManager->userExists($email, $password)) {
                $errors[] = new FormError("no_user_found", "User account was not found.");
            } else {
                if($isEmail){
                    $user = $userManager->getUser($email); // Fetch user data here
                    $username = $user->getUsername();

                    $dbQuery = mysqli_query($connection, "SELECT * FROM user WHERE email='$email' AND account_is_closed='1'");
                    $accountIsClosed = mysqli_num_rows($dbQuery) == 1;

                    if ($accountIsClosed) {
                        mysqli_query($connection, "UPDATE user SET closed='0' WHERE email='$email'");
                    }

                    session_start();
                    $_SESSION['username'] = $username;
                    header("Location: index.php");
                    exit();
                }

            }
        }
    }
?>


<body>
    <main class="width_70">
        <section class="section">
            <div class="form_container">
                <h2>Social App</h2>
                <form class="form" action="login.php" method="POST">
                    <div class="form_input_box">
                        <label>Email:</label>
                        <input type="text" name="log_email" placeholder="email@email.com">
                    </div>
                    <div class="form_input_box">
                        <label>Password:</label>
                        <input type="password" name="log_password" placeholder="password">
                    </div>
                    <button class="form_submit" type="submit" name="submit_btn">Sign in to your account</button>
                </form>

                <div class="form_box_links">
                    <a class="form_box_link" href="/register.php">
                        <i class="fa-solid fa-user"></i>
                        Create new profile
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

<?php
DBConnection::close();