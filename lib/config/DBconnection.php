<?php

    class DBConnection
    {
        private static mysqli $databaseConnection;

        public static function connect(): mysqli
        {
            if (!isset(self::$databaseConnection)) {
                ob_start(); //Output buffer
                session_start();

                $timezone = date_default_timezone_set("Europe/Prague");

                self::$databaseConnection = mysqli_connect("localhost", "root", "", "socialapp", 3306);
                if (mysqli_connect_errno()) {
                    echo "ERROR CONNECTING TO DB" . mysqli_connect_errno();
                }
            }

            return self::$databaseConnection;
        }

        public static function createTablesOnConnection(): void
        {
            $connection = self::connect();
            if (mysqli_connect_errno()) {
                echo "ERROR CONNECTING TO DB" . mysqli_connect_errno();
                return;
            }

            // User table
            $sqlUser = "CREATE TABLE IF NOT EXISTS User (
                    ID INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) UNIQUE,
                    password VARCHAR(255),
                    profile_picture VARCHAR(255),
                    email VARCHAR(255),
                    firstname VARCHAR(255),
                    surname VARCHAR(255),
                    likes TEXT,
                    posts TEXT,
                    comments TEXT,
                    friends TEXT,
                    register_date DATE,
                    account_is_closed BOOLEAN
                )";

            // Post table
            $sqlPost = "CREATE TABLE IF NOT EXISTS Post (
                    ID INT AUTO_INCREMENT PRIMARY KEY,
                    postBody TEXT,
                    date_of_creation DATETIME,
                    creator_id INT,
                    created_for_who VARCHAR(255),
                    comments TEXT,
                    likes TEXT
                )";

            // Comment table
            $sqlComment = "CREATE TABLE IF NOT EXISTS Comment (
                    ID INT AUTO_INCREMENT PRIMARY KEY,
                    comment TEXT,
                    creator_id INT,
                    post_id INT,
                    date_of_creation DATETIME,
                    likes TEXT
                )";

            // Check if the tables exist before creating them
            if (!self::tableExists($connection, "User") && !self::tableExists($connection, "Post") && !self::tableExists($connection, "Comment")) {
                // Execute the queries
                if (mysqli_query($connection, $sqlUser) && mysqli_query($connection, $sqlPost) && mysqli_query($connection, $sqlComment)) {
                    echo "Tables created successfully!";
                    self::addForeignKeys($connection);
                } else {
                    echo "Error creating tables: " . mysqli_error($connection);
                }
            } else {
                echo "Tables already exist!";
            }

            mysqli_close($connection);
        }

        public static function addForeignKeys($connection): void
        {
            // Add foreign keys to Post table
            $sqlPostKeys = "ALTER TABLE Post
                                ADD FOREIGN KEY (creator_id) REFERENCES User(ID)";

            // Add foreign keys to Comment table
            $sqlCommentKeys = "ALTER TABLE Comment
                                   ADD FOREIGN KEY (creator_id) REFERENCES User(ID),
                                   ADD FOREIGN KEY (post_id) REFERENCES Post(ID)";

            // Execute the queries
            if (mysqli_query($connection, $sqlPostKeys) && mysqli_query($connection, $sqlCommentKeys)) {
                echo "Foreign keys added successfully!";
            } else {
                echo "Error adding foreign keys: " . mysqli_error($connection);
            }
        }

        public static function tableExists($connection, $tableName): bool
        {
            $sql = "SHOW TABLES LIKE '$tableName'";
            $result = mysqli_query($connection, $sql);
            return mysqli_num_rows($result) > 0;
        }


    }

