<?php

require_once(realpath(dirname(__FILE__)) . '/Auth.interface.php');

class AuthProviderPhpVariable implements iAuth {

    private $username = '';
    private $error = array();
    private $user_id = -1;

    public function __construct() {
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
            $this->error[] = "Username or password is empty.";
            return false;
        }

        if ($user = $this->findUserByName($username)) {
            if ($user['password'] !== $password) {
                $this->error[] = "Username or password is empty.";
                return false;
            } else {
                return true;
            }
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
        global $t_auth_user_table;

        if ($this->findUserByName($username)) {
            $this->error[] = "User already exists";
        }

        $newUser = array();
        $newUser['username'] = $username;
        $newUser['password'] = $password;

        $t_auth_user_table[] = $newUser;
    }

    public function findUserByName($username) {
        global $t_auth_user_table;

        if (!is_array($t_auth_user_table)) {
            $this->error[] = "findUserByName: No users defined";

            return false;
        }

        foreach ($t_auth_user_table as $row) {
            if ($row['username'] == $username) {
                return $row;
            }
        }

        return false;
    }

}
