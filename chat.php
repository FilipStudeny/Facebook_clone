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
    $profilePicture = $user->getProfilePicture();

    $friend = $userManager->getUser($_GET['with_user']);
    $friendProfilePicture = $friend->getProfilePicture();

?>



    <body>
    <?php include_once ("./components/navbar.php");?>
    <?php include("./components/sidebar.php"); ?>

    <main>
        <section class="chat_section">
            <h2 class="chat_title">Chat with <?php echo $_GET['with_user']; ?></h2>
            <ul class="chat_messages" id="messages">

            </ul>
            <form class="chat_form" id="chatForm">
                <div class="form_input_box">
                    <input type="text" id="messageInput" placeholder="Type your message" autocomplete="off" required>
                    <button class="form_submit" type="submit">Send</button>
                </div>
            </form>

        </section>

    </main>


    </body>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=' crossorigin='anonymous'></script>
    <script>
        $(function() {
            var conn = new WebSocket('ws://localhost:8080');
            var recipient = '<?php echo $_GET['with_user']; ?>'; // Update with recipient's username
            var username = '<?php echo $userLoggedIn; ?>'; // Update with logged-in user's username
            var roomId = generateRoomId(username, recipient);



            function generateRoomId(user1, user2) {
                var sortedUsers = [user1, user2].sort();
                return sortedUsers.join('_');
            }

            conn.onopen = function() {
                console.log('Connected to the chat server');
                var registerData = {
                    action: 'registerUser',
                    username: username
                };
                conn.send(JSON.stringify(registerData));

                var createRoomData = {
                    action: 'createRoom',
                    recipient: recipient,
                    room: roomId
                };
                conn.send(JSON.stringify(createRoomData));
            };

            conn.onmessage = function(e) {
                var msg = JSON.parse(e.data);

                if (msg.hasOwnProperty('sender')) {
                    var container = $('<div class="message_container">');
                    var message = $('<div class="message ' + (msg.sender !== username ? '' : 'my_message') + '">');
                    var profilePicture = $('<div class="message_profile_picture">').append('<img src="' + (msg.sender === username ? "<?php echo $profilePicture ?>" : "<?php echo $friendProfilePicture ?>") + '" width="50" height="50" alt="user profile picture">');
                    var messageText = $('<p class="message_text">').text(msg.message);

                    message.append(profilePicture);
                    message.append(messageText);
                    container.append(message)

                    $('#messages').append(container);
                    $('html, body').animate({ scrollTop: $(document).height() });

                } else {
                    var infoMessage = $('<div class="message">');
                    var infoText = $('<p class="message_text">').text("[INFO]: " + msg);

                    infoMessage.append(infoText);

                    $('#messages').append(infoMessage);
                }
            };

            $('#chatForm').submit(function(e) {
                e.preventDefault();
                var message = $('#messageInput').val();
                var data = {
                    action: 'sendMessage',
                    room: roomId,
                    message: message,
                    recipient: recipient
                };
                conn.send(JSON.stringify(data));
                $('#messageInput').val('');
            });
        });
    </script>
    </html>

<?php
DBConnection::close();
