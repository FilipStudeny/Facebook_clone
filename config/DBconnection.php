<?php

        ob_start(); //Output buffer
        session_start();

        $timezone = date_default_timezone_set("Europe/Prague");

        $connection = mysqli_connect("localhost","root","","socialapp",3306);
        if(mysqli_connect_errno()){
            echo "ERROR CONNECTING TO DB" . mysqli_connect_errno();
        }



