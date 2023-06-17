<?php

    class FormError{

        readonly string $name;
        readonly string $message;

        function __construct(string $name, string $message)
        {
            $this->name = $name;
            $this->message = $message;
        }

        function getMessage(): string{
            return $this->message;
        }

        function getName(): string{
            return $this->name;
        }
    }

