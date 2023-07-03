<?php

    require_once __DIR__ . '/../classes/User.php';

    class UserManager{

        private mysqli $databaseConnection;

        public function __construct(mysqli $databaseConnection){
            $this->databaseConnection = $databaseConnection;

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

        public function sendFriendRequest(string $fromID, string $toID): void
        {
            $newFriend = $this->getUser($toID);
            $newFriendID = $newFriend->getID();

            $user = $this->getUser($fromID);

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
                    $query = "INSERT INTO notifications (type, for_user_id, content, date_of_creation, opened) VALUES (?, ?, ?, ?, '0')";
                    $statement = mysqli_prepare($this->databaseConnection, $query);

                    // Bind the parameters
                    mysqli_stmt_bind_param($statement, "ssss", $type, $newFriendID, $content, $date);
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


