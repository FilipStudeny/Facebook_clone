<?php

    interface IManager{
        public function getByID(string $id);
        public function createNew(array $data);

        public function delete(string $id);
        public function updateData(string $data);
    }


