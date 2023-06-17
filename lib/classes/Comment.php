<?php


    class Comment implements IRenderable{
        private string $commentID;
        private $commentData;
        private User $creator;
        private mysqli $databaseConnection;

        public function __construct(mysqli $databaseConnection, string $commentID)
        {
            $this->databaseConnection = $databaseConnection;
        }

        public function getCreator(): string{

        }

        public function getComment(): string{

        }
        public function getCreationTime(): string{

        }

        public function render(): void{
            echo $this->getHTML();
        }

        public function getHTML(){
            $creator = new User($this->databaseConnection, $this->getCreator());
            $creatorName = $creator->getUsername();
            $creatorProfilePicture = $creator->getProfilePicture();
            $body = $this->getComment();
            $dateOfCreation = $this->getCreationTime();

            $html = 
            <<<HTML
                <article class='comment'>
                    <header class='comment_header'>
                        <div class='comment_profile_pic_container'>
                            <img class='comment_profile_picture' src='$creatorProfilePicture' width='50' height='50'>
                        </div>
                        <div class='comment_header_user_info'>
                            <nav class='comment_header_user_links'>
                                <a href='$creatorName'>$creatorName</a>
                            </nav>
                            <p class='comment_time_of_creation'>$dateOfCreation</p>
                        </div>
                    </header>
                    <div class='comment_body'>
                        $body
                    </div>
                </article>
            HTML;

            return $html;
        }

    }

?>