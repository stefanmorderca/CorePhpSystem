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

    const LOGIN_METHOD_POST = 1;
    const LOGIN_METHOD_HASH = 2;

    public function __construct() {
        $this->AuthProvider = new AuthProviderLocal();
    }

    public function authenticate() {
        if (!$this->isValid() && isset($_POST['username']) && isset($_POST['password'])) {
            $this->login($_POST['username'], $_POST['password']);

            if ($this->isValid()) {
                $this->method = Auth::LOGIN_METHOD_POST;
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

    public function login($username, $password, $client_ip = '', $client_useragent = '') {
        $client_ip = ($client_ip == '') ? $_SERVER['REMOTE_ADDR'] : $client_ip;
        $client_useragent = ($client_useragent == '') ? $_SERVER['HTTP_USER_AGENT'] : $client_ip;

        if ($username == '' || $password == '') {
            $this->error[] = "Nazwa użytkownika lub hasło jest puste";
            return false;
        }

        if ($this->AuthProvider->login($username, $password)) {
            $this->createSession($username, $client_ip, $client_useragent);
            return true;
        }

        return false;
    }

    public function logout() {
        if ($this->isValid()) {
            AuthSessionManager::destroySession();
        }

        unset($_SESSION['LOGGED_HASH']);
    }

    private function createSession($username, $password, $userId) {
        $session_key = AuthSessionManager::createSession($username, $password, $userId);

        return $session_key;
    }

    private function checkMySession() {
        $fl_ok = false;

        try {
            AuthSessionManager::checkSession($_SESSION['auth_session']);
            $fl_ok = true;
        } catch (Exception $exc) {
            $this->error[] = $exc->getMessage();
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
            if ($this->method == Auth::LOGIN_METHOD_HASH) {
                return true;
            }
        }

        return false;
    }

    public function setLoggedMethodHash() {
        $this->method = Auth::LOGIN_METHOD_HASH;
    }

    /**
     * @return AuthConfigure
     */
    public function configure() {
        $config = new AuthConfigure();

        return $config;
    }

}
