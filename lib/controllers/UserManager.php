<?php

    require_once __DIR__ . '/../classes/User.php';

    class UserManager{

        private mysqli $databaseConnection;
        private string $loggedUser;

        public function __construct(mysqli $databaseConnection, string $loggedUser = ""){
            $this->databaseConnection = $databaseConnection;
            $this->loggedUser = $loggedUser;

        }
        public function createNew(array $data): void
        {
            $username = $data['username'];
            $password = $data['password'];
            $hashedPassword = md5($password);
            $email = $data['email'];
            $profilePicture = $data['profile_picture'];
            $firstname = $data['firstname'];
            $surname = $data['surname'];
            $registerDate = date("Y-m-d");

            $query = "INSERT INTO user (username, password, profile_picture, email, firstname, surname, likes, posts, friends, register_date, account_is_closed) 
              VALUES ('$username', '$hashedPassword', '$profilePicture', '$email', '$firstname', '$surname', '', '', '', '$registerDate', false)";

            if (mysqli_query($this->databaseConnection, $query)) {
                echo "User created successfully!";
            } else {
                echo "Error creating user: " . mysqli_error($this->databaseConnection);
            }

        }
        public function getUser(string $identifier): ?User {

            $query = "SELECT * FROM user WHERE email=? OR username=? OR id=?";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "sss", $identifier, $identifier, $identifier);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $userData = mysqli_fetch_array($result);

            return new User($userData);
        }

        public function updateProfilePicture(string $username, string $profilePicture): void
        {
            $username = mysqli_real_escape_string($this->databaseConnection, $username);
            $profilePicture = mysqli_real_escape_string($this->databaseConnection, $profilePicture);

            $query = "UPDATE user SET profile_picture = '$profilePicture' WHERE username = '$username'";

            if (mysqli_query($this->databaseConnection, $query)) {
                echo "Profile picture updated successfully!";
            } else {
                echo "Error updating profile picture: " . mysqli_error($this->databaseConnection);
            }
        }

        public function loadFriends(string $page, string $identifier, int $postLimit): void{

            if($page == 1){
                $start = 0;
            }else{
                $start = ((int)$page - 1) * $postLimit;
            }

            $users = "";
            $query = "SELECT friends FROM `user` WHERE username = '$identifier' ORDER BY ID DESC";
            $dbQuery = mysqli_query($this->databaseConnection, $query);
            $numIterations = 0; //Number of iterations check
            $resultsCount = 1;

            if (mysqli_num_rows($dbQuery) > 0) {
                while ($postData = mysqli_fetch_array($dbQuery)) {

                    $array = array_map('trim', explode(',',  $postData['friends']));
                    $usersIDs = array_filter($array);

                    foreach ($usersIDs as $userID) {
                        // Process each post ID here
                        $user = $this->getUser($userID);

                        if ($numIterations++ < $start) {
                            continue;
                        }

                        if ($resultsCount > $postLimit) {
                            break;
                        } else {
                            $resultsCount++;
                        }

                        $users .= $user->getHTML();
                    }
                }
            }

            if($resultsCount > $postLimit){
                $value = ((int)$page + 1);
                $users .=
                    <<<HTML
                            <input type='hidden' class='nextPage' value="$value">
                            <input type='hidden' class='noMorePosts' value='false'>
                        HTML;
            }else{
                $users .=
                    <<<HTML
                            <input type='hidden' class='noMorePosts' value="true">
                            <p class='noMorePosts_text'> No more friends. </p>
                        HTML;
            }

            echo $users;
        }
        public function sendFriendRequest(string $fromID, string $toID): void
        {
            $newFriend = $this->getUser($toID);
            $newFriendID = $newFriend->getID();

            $user = $this->getUser($fromID);
            $userID = $user->getID();

            if ($newFriend->isFriendWith($fromID)) {
                // Users are already friends, remove the user from the friends column
                $userQuery = "UPDATE user SET friends = REPLACE(friends, '{$fromID},', '') WHERE ID = ?";
                $userStatement = mysqli_prepare($this->databaseConnection, $userQuery);
                mysqli_stmt_bind_param($userStatement, "s", $newFriendID);
                mysqli_stmt_execute($userStatement);
            } else {
                $type = "friend_request";
                $date = date("Y-m-d H:i:s");
                $content = "User <a href='/profile.php?user={$user->getUsername()}'>{$user->getUsername()}</a> wants to be your friend ";

                // Check if the friend request already exists
                $existingNotificationID = $this->friendRequestAlreadySent($newFriendID);

                if ($existingNotificationID !== false) {
                    // Friend request already exists, delete it from the notifications table
                    $deleteQuery = "DELETE FROM notifications WHERE ID = ?";
                    $deleteStatement = mysqli_prepare($this->databaseConnection, $deleteQuery);
                    mysqli_stmt_bind_param($deleteStatement, "s", $existingNotificationID);
                    mysqli_stmt_execute($deleteStatement);

                    // Remove the notification ID from the user's notifications column
                    $userQuery = "UPDATE user SET notifications = REPLACE(notifications, '{$existingNotificationID},', '') WHERE ID = ?";
                    $userStatement = mysqli_prepare($this->databaseConnection, $userQuery);
                    mysqli_stmt_bind_param($userStatement, "s", $newFriendID);
                    mysqli_stmt_execute($userStatement);
                }else{

                    // Prepare the SQL statement
                    $query = "INSERT INTO notifications (type, creator ,for_user_id, content, date_of_creation, opened) VALUES (?, ?, ?, ?, ?, '0')";
                    $statement = mysqli_prepare($this->databaseConnection, $query);

                    // Bind the parameters
                    mysqli_stmt_bind_param($statement, "sssss", $type, $userID ,$newFriendID, $content, $date);
                    // Execute the prepared statement
                    mysqli_stmt_execute($statement);

                    // Check if the insertion was successful
                    if (mysqli_stmt_affected_rows($statement) > 0) {
                        $returnedID = mysqli_insert_id($this->databaseConnection);

                        $userQuery = "UPDATE user SET notifications = CONCAT(notifications, '{$returnedID},') WHERE ID = ?";
                        $userStatement = mysqli_prepare($this->databaseConnection, $userQuery);
                        mysqli_stmt_bind_param($userStatement, "s", $newFriendID);
                        mysqli_stmt_execute($userStatement);
                    }
                }
            }
        }
        public function getFriendRequest(string $friendRequestID): array
        {
            $query = "SELECT * FROM notifications WHERE ID = ?";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            mysqli_stmt_bind_param($statement, "s", $friendRequestID);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $data = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            mysqli_stmt_close($statement);

            return $data;
        }
        public function acceptFriendRequest(string $friendRequestID): void
        {
            // Get the friend request from the notifications table
            $friendRequest = $this->getFriendRequest($friendRequestID);
            $user = $friendRequest['for_user_id'];
            $newFriend = $friendRequest['creator'];

            // Update the friends column for the current user
            $updateCurrentUserQuery = "UPDATE user SET friends = ? WHERE ID = ?";
            $updateCurrentUserStatement = mysqli_prepare($this->databaseConnection, $updateCurrentUserQuery);
            mysqli_stmt_bind_param($updateCurrentUserStatement, "ss", $newFriend, $user);
            mysqli_stmt_execute($updateCurrentUserStatement);

            $updateCurrentUserQuery = "UPDATE user SET friends = ? WHERE ID = ?";
            $updateCurrentUserStatement = mysqli_prepare($this->databaseConnection, $updateCurrentUserQuery);
            mysqli_stmt_bind_param($updateCurrentUserStatement, "ss", $user, $newFriend);
            mysqli_stmt_execute($updateCurrentUserStatement);

            // Remove the friend request notification from the notifications table
            $deleteFriendRequestQuery = "DELETE FROM notifications WHERE ID = ?";
            $deleteFriendRequestStatement = mysqli_prepare($this->databaseConnection, $deleteFriendRequestQuery);
            mysqli_stmt_bind_param($deleteFriendRequestStatement, "s", $friendRequestID);
            mysqli_stmt_execute($deleteFriendRequestStatement);

            // Update the notifications column in the user table
            $updateUserNotificationsQuery = "UPDATE user SET notifications = REPLACE(notifications, ?, '') WHERE ID = ?";
            $updateUserNotificationsStatement = mysqli_prepare($this->databaseConnection, $updateUserNotificationsQuery);
            mysqli_stmt_bind_param($updateUserNotificationsStatement, "ss", $friendRequestID, $user);
            mysqli_stmt_execute($updateUserNotificationsStatement);
        }
        public function removeFriend(string $friendID, string $sender): void
        {
            // Get the friend request from the notifications table
            $userID = $this->getUser($sender)->getID();

            // Update the friends column for the current user
            $updateCurrentUserQuery = "UPDATE user SET friends = REPLACE(friends, ?, '') WHERE ID = ?";
            $updateCurrentUserStatement = mysqli_prepare($this->databaseConnection, $updateCurrentUserQuery);
            mysqli_stmt_bind_param($updateCurrentUserStatement, "ss", $userID, $friendID);
            mysqli_stmt_execute($updateCurrentUserStatement);

            // Update the friends column for the friend to be removed
            $updateFriendQuery = "UPDATE user SET friends = REPLACE(friends, ?, '') WHERE ID = ?";
            $updateFriendStatement = mysqli_prepare($this->databaseConnection, $updateFriendQuery);
            mysqli_stmt_bind_param($updateFriendStatement, "ss", $friendID, $userID);
            mysqli_stmt_execute($updateFriendStatement);
        }
        public function friendRequestAlreadySent(string $userID)
        {
            // Check if the friend request already exists
            $query = "SELECT ID FROM notifications WHERE type = ? AND for_user_id = ?";
            $statement = mysqli_prepare($this->databaseConnection, $query);
            $type = 'friend_request';
            mysqli_stmt_bind_param($statement, "ss", $type, $userID);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);

            if ($row = mysqli_fetch_assoc($result)) {
                // Friend request already exists, return the ID of the notification
                return $row['ID'];
            }

            // Friend request does not exist
            return false;
        }
        public function userExists(string $email, string $password): bool
        {
            $hashedPassword = md5($password);
            $email = mysqli_real_escape_string($this->databaseConnection, $email);
            $query = "SELECT * FROM User WHERE email='$email' AND password='$hashedPassword'";
            $result = mysqli_query($this->databaseConnection, $query);

            return mysqli_num_rows($result) > 0;
        }
        public function usernameInUse(string $username): bool
        {
            $username = mysqli_real_escape_string($this->databaseConnection, $username);
            $query = "SELECT * FROM User WHERE username='$username'";
            $result = mysqli_query($this->databaseConnection, $query);

            return mysqli_num_rows($result) > 0;
        }
        public function emailInUse(string $email): bool
        {
            $email = mysqli_real_escape_string($this->databaseConnection, $email);
            $query = "SELECT * FROM User WHERE email='$email'";
            $result = mysqli_query($this->databaseConnection, $query);

            return mysqli_num_rows($result) > 0;
        }
        public function checkIfAlreadyInUse(string $email, string $username): array
        {
            $errors = [];

            $email = mysqli_real_escape_string($this->databaseConnection, $email);
            $username = mysqli_real_escape_string($this->databaseConnection, $username);

            if ($this->emailInUse($email)) {
                $errors[] = new FormError("email_in_use", "Email address already being used.");
            }

            if ($this->usernameInUse($username)) {
                $errors[] = new FormError("username_is_used", "Username already being used.");
            }

            return $errors;
        }
    }


