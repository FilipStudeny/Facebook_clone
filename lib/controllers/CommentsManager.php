<?php


    class CommentsManager{


        private mysqli $databaseConnection;
        private string $loggedInUser;

        public function __construct(mysqli $databaseConnection, string $loggedInUser){
            $this->databaseConnection = $databaseConnection;
            $this->loggedInUser = $loggedInUser;
        }

        public function getComment(string $id){
            return "";
        }

        public function createNewComment(string $commentData, string $postID){

        }

        public function getComments(string $postID){

        }
    }


?>