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
        <section class="chat">
            <section class="chat_messages">

                <div class="message">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>

                <div class="message">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>

                <div class="message">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>

                <div class="message myMessage">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>

                <div class="message myMessage">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message</p>
                    </div>
                </div>

                <div class="message">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>
                <div class="message">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>

                <div class="message">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>

                <div class="message">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>

                <div class="message">
                    <div class="message_profile_picture">
                        <img src="<?php echo $profilePicture?>" alt="Profile picture" width="100" height="100" >
                    </div>
                    <div class="message_content">
                        <p class="message_time">Yesterday</p>
                        <p class="message_text">message meesagaemessage meesagaemessage meesagaemessage meesagae</p>
                    </div>
                </div>




            </section>
            <form class="chat_form" action="#" method="#">
                <input id="messageInput">
                <button id="sendButton">Send</button>
            </form>

        </section>

    </main>


    </body>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=' crossorigin='anonymous'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.4.1/socket.io.js"></script>
    <script>
        $(document).ready(function() {
            let socket = io("http://localhost:8888");

            socket.emit("setup", {"admin": "Asdsa"})

        });
    </script>
    </html>

<?php
DBConnection::close();
