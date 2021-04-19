<?php

class userView extends Users {

    protected function showUsers($name) {
        $results = $this->getUser($name);
        echo "Volledige naam: " . $results['users_firstname'] . $results['users_lastname'];
    }
}