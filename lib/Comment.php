<?php


    class Comment{
        private string $commentID;
        private $commentData;
        private User $creator;
        private mysqli $databaseConnection;

        public function __construct(mysqli $databaseConnection, string $commentID)
        {
            $this->databaseConnection = $databaseConnection;
        }
    }

?>