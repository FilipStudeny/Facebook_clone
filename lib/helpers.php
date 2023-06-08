<?php

    // Helper function to sanitize input
    function sanitizeInput($input, $firstLetterUP=true)
    {
        $input = strip_tags($input);
        $input = str_replace(' ', '', $input);

        if($firstLetterUP){
            return ucfirst(strtolower($input));
        }else{
            return $input;
        }
    }

    // Helper function to validate length
    function validateLength($input, $minLength, $maxLength)
    {
        $length = strlen($input);
        return ($length >= $minLength && $length <= $maxLength);
    }

    // Helper function to display error messages
    function displayError($errorCode, $errorMessages)
    {
        if (isset($errorMessages[$errorCode])) {
            echo "<span class='error'>{$errorMessages[$errorCode]}</span><br>";
        }
    }

?>