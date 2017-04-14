<?php

require_once(realpath(dirname(__FILE__)) . '/Auth.interface.php');

class AuthProviderPhpVariable implements iAuth {

    private $username = '';
    private $error = array();
    private $user_id = -1;

    public function AuthProviderLocal() {
        if (!$this->isValid() && isset($_POST['username']) && isset($_POST['password'])) {
            $this->login($_POST['username'], $_POST['password']);
        }
    }

    public function isValid() {
        return false;
    }

    public function logout() {
        
    }

    public function login($username, $password) {
        if ($username == '' || $password == '') {
            $this->error[] = "Nazwa użytkownika lub hasło jest puste";
            return false;
        }

        return false;
    }

    public function getError() {
        return $this->error;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function changePassword($pass) {
        
    }

    public function checkPassword($password) {

        return false;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setUserId($id) {
        $this->user_id = $id;
    }

    public function addUser($username, $password) {
        
    }

}
