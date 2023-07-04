<?php

    class Notification
    {
        private array $data;
        public function __construct(array $data)
        {
            $this->data = $data;
        }

        private function getMessage(): string{
            return $this->data['content'];
        }

        private function getType(): string{
            return $this->data['type'];
        }

        private function getID(): string{
            return $this->data['ID'];
        }
        public function render(): void{
            echo $this->getHTML();
        }

        public function getHTML(): string{

            $message = $this->getMessage();
            $ID = $this->getID();
            $isFriendRequest = $this->getType() == "friend_request";

            $friendRequestButtons = $isFriendRequest ?
                <<<HTML
                    <div class="notification_form">
                        <button class="accept" data-notification-id="$ID">Accept</button>
                        <button class="decline" data-notification-id="$ID" >Decline</button>
                    </div>
                HTML : '';

            return <<<HTML
                <section class="notification">
                   <div class="notification_user_profile_picture_container">
                        <img class="notification_user_profile_picture" src="./../../assets/defaults/user_icon.png">
                   </div>
                   <p class="notification_message">
                        $message
                   </p>
                   
                    $friendRequestButtons
                </section>
                HTML;

        }

    }