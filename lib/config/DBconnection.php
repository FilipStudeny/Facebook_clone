<?php

    class DBConnection
    {
        private static mysqli $databaseConnection;

        public static function connect(): mysqli
        {
            if (!isset(self::$databaseConnection)) {
                ob_start(); //Output buffer
                session_start();

                date_default_timezone_set("Europe/Prague");

                self::$databaseConnection = mysqli_connect("localhost", "root", "", "socialapp", 3306);
                if (mysqli_connect_errno()) {
                    echo "ERROR CONNECTING TO DB" . mysqli_connect_errno();
                }
            }

            return self::$databaseConnection;
        }

        public static function close(): void
        {
            self::$databaseConnection?->close();
        }
    }

