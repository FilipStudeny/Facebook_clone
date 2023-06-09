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
    function validateLength(string $input,int $minLength,int $maxLength): bool
    {
        $length = strlen($input);
        return ($length >= $minLength && $length <= $maxLength);
    }

    // Helper function to display error messages
    function displayFormError(string $errorMessage): string
    {
        return  
        "<li>
            <span class='form_error'>{$errorMessage}</span><br>
        </li>";
        
    }

?>