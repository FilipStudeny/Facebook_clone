<?php

    namespace App\lib\utils;

    use DateTimeImmutable;

    class Time
    {

        public static function getTimeSinceCreation(string $timeOfCreation): string
        {
            // Time frame
            $startDate = 0;
            try {
                $startDate = new DateTimeImmutable($timeOfCreation);
            } catch (\Exception $e) {
            }
            $endDate = new DateTimeImmutable();
            $interval = $endDate->diff($startDate);

            if ($interval->y >= 1) {
                return $interval->format("%y year" . ($interval->y > 1 ? "s" : "") . " ago.");
            } elseif ($interval->m >= 1) {
                return $interval->format("%m month" . ($interval->m > 1 ? "s" : "") . " ago.");
            } elseif ($interval->d >= 1) {
                return ($interval->d === 1 ? "Yesterday." : $interval->format("%d days ago."));
            } elseif ($interval->h >= 1) {
                return $interval->format("%h hour" . ($interval->h > 1 ? "s" : "") . " ago.");
            } elseif ($interval->i >= 1) {
                return $interval->format("%i minute" . ($interval->i > 1 ? "s" : "") . " ago.");
            } else {
                return ($interval->s <= 30 ? "Just now." : $interval->format("%s seconds ago."));
            }
        }
    }