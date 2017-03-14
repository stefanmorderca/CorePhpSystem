<?php

require_once(realpath(dirname(__FILE__)) . '/Auth.interface.php');

class AuthLocal implements iAuth {

    private $username = '';
    private $error = array();
    private $user_id = -1;

    public function AuthLocal() {
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

        $sql = "SELECT * FROM user WHERE username = '" . mysql_escape_string($username) . "'";
        if ($t_dane = my_query($sql)) {
            if ($t_dane[0]['is_active'] == 1 and $t_dane[0]['password'] === md5($password)) {
                $this->setUserId($t_dane[0]['user_id']);

                return true;
            } else {
                if ($t_dane[0]['is_active'] == 0) {
                    $this->error[] = "Konto nie jest jeszcze aktywne. Sprawdź pocztę e-mail.";
                } else {
                    $this->error[] = "Nieprawidłowe hasło";
                }
            }
        } else {
            $this->error[] = "Nieprawidłowa nazwa użytkownika";
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
        $sql = "UPDATE user SET password = '" . md5($pass) . "' WHERE username = '" . mysql_escape_string($this->username) . "' and username != '' limit 1";
        my_query($sql);
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setUserId($id) {
        $this->user_id = $id;
    }

    public function checkPassword($password) {
        $sql = "SELECT * FROM user WHERE username = '" . mysql_escape_string($this->username) . "' and username != '' limit 1";

        if ($t_dane = my_query($sql)) {
            if ($t_dane[0]['is_active'] == 1 and $t_dane[0]['password'] === md5($password)) {
                return true;
            }
            print_r($t_dane);
            die(md5($password));
        }
        return false;
    }

}
