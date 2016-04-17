<?php

require_once('Auth/Auth.interface.php');
require_once('Auth/AuthLocal.class.php');

/**
 * Auth main class
 */
class Auth implements iAuth {

    private $username = '';
    private $error = array();
    private $user_id = -1;
    private $AuthProvider;
    private $method = 'none';
    
    public static $LOGIN_METHOD_POST = 1;
    public static $LOGIN_METHOD_HASH = 2;

    public function Auth() {
        $this->AuthProvider = new AuthLocal();

        if (!$this->isValid() && isset($_POST['username']) && isset($_POST['password'])) {
            $this->login($_POST['username'], $_POST['password']);

            if ($this->isValid()) {
                $this->method = Auth::$LOGIN_METHOD_POST;
            }
        }

        if (!$this->isValid() && isset($_GET['user_id']) && isset($_GET['hash'])) {
            $this->loginByHash($_GET['user_id'], $_GET['hash']);

            if ($this->isValid()) {
                $this->setLoggedMethodHash();
            }
        }
    }

    public function isValid() {
        if (isset($_SESSION['auth_session']) && isset($_SESSION['username'])) {
            return $this->checkMySession();
        }

        return false;
    }

    public function logout() {
        if ($this->isValid()) {
            $sql = "DELETE FROM session WHERE username = '" . mysql_escape_string($_SESSION['username']) . "' and session_key = '" . $_SESSION['auth_session'] . "' limit 1";
            my_query($sql);
        }

        unset($_SESSION['LOGGED_HASH']);
        unset($_SESSION['username']);
        unset($_SESSION['auth_session']);
    }

    public function loginByHash($userId, $hash) {
        if ($userId == '' || $hash == '') {
            $this->error[] = "Nazwa użytkownika lub hash są puste";
            return false;
        }

        if ($username = $this->AuthProvider->loginByHash($userId, $hash)) {
            $this->createSession($username, $password, $this->AuthProvider->getUserId());
            return true;
        }

        return false;
    }

    public function login($username, $password) {
        if ($username == '' || $password == '') {
            $this->error[] = "Nazwa użytkownika lub hasło jest puste";
            return false;
        }

        if ($this->AuthProvider->login($username, $password)) {
            $this->createSession($username, $password, $this->AuthProvider->getUserId());
            return true;
        }

        return false;
    }

    private function createSession($username, $password, $userId) {
        $sql = "INSERT INTO session (username, session_key, user_id) VALUES ('" . mysql_escape_string($username) . "', '" . md5(md5($username) . md5($password) . md5(time())) . "', $userId)";

        if ($sessionId = my_query($sql)) {
            $sql = "SELECT * FROM session WHERE session_id = $sessionId limit 1";
            $t_dane = my_query($sql);

            $_SESSION['auth_session'] = $t_dane[0]['session_key'];
            $_SESSION['username'] = $username;
        }
    }

    private function checkMySession() {
        $fl_ok = false;

        $sql = "SELECT * FROM session WHERE username = '" . $_SESSION['username'] . "' and session_key = '" . $_SESSION['auth_session'] . "'";

        if ($t_dane = my_query($sql)) {
            if (isset($t_dane[0]['username']) && $t_dane[0]['username'] == $_SESSION['username']) {
                $this->setUsername($t_dane[0]['username']);
                $this->setUserId($t_dane[0]['user_id']);

                $fl_ok = true;
            } else {
                $this->error[] = "O_o? WTF? checkMySession faild";
            }
        } else {
            $this->error[] = "Nie ma takiej sesji";
        }

        return $fl_ok;
    }

    public function getError() {
        if (is_array($this->error) && $this->error != array()) {
            return $this->error;
        }

        return $this->AuthProvider->getError();
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
        $this->AuthProvider->setUsername($username);
    }

    public function setUserId($id) {
        $this->user_id = $id;
        $this->AuthProvider->setUserId($id);
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function changePassword($password) {
        $this->AuthProvider->changePassword($password);
    }

    public function checkPassword($password) {
        return $this->AuthProvider->checkPassword($password);
    }

    public function isLoggedInThroughHash() {
        if ($this->isValid()) {
            if ($this->method == Auth::$LOGIN_METHOD_HASH) {
                return true;
            }
        }

        return false;
    }

    public function setLoggedMethodHash() {
        $this->method = Auth::$LOGIN_METHOD_HASH;
    }

}
