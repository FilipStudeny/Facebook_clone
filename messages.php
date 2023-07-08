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


        <section class="settings_container">
            <div class="setting_options_container">
                <h2>Delete profile ?</h2>
                <button><i class="fa-solid fa-trash"></i>Delete my profile</button>
            </div>
        </section>
    </main>


    </body>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=' crossorigin='anonymous'></script>




    </html>

<?php
DBConnection::close();
