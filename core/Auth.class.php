<?php

require_once('Auth/Auth.interface.php');
require_once('Auth/AuthConfigure.class.php');
require_once('Auth/AuthListner.interface.php');
require_once('Auth/AuthProviderGoogle.class.php');
require_once('Auth/AuthProviderLocalPhpFile.class.php');
require_once('Auth/AuthProviderPhpVariable.class.php');
require_once('Auth/AuthProviderPhpVariable.class.php');
require_once('Auth/AuthSessionManager.class.php');

/**
 * Auth main class
 */
class Auth implements iAuth {

    /**
     *
     * @var string 
     */
    private $username = '';

    /**
     *
     * @var string[]
     */
    private $error = array();

    /**
     *
     * @var int
     */
    private $user_id = -1;

    /**
     *
     * @var iAuth 
     */
    private $AuthProvider;

    /**
     *
     * @var string 
     */
    private $method = 'none';

    /**
     *
     * @var AuthConfigure 
     */
    private $config;

    const LOGIN_METHOD_POST = 1;
    const LOGIN_METHOD_HASH = 2;

    /**
     *
     * @var AuthListner 
     */
    private $authListner;

    public function __construct() {
        $this->AuthProvider = new AuthProviderPhpVariable();
    }

    /**
     * 
     * @return type
     */
    private function getInstanceOfAuthProvider() {
        $isInitializationOfProviderRequired = false;
        $authProviderType = $this->configure()->getAuthProviderClass();

        if (empty($this->AuthProvider)) {
            $isInitializationOfProviderRequired = true;
        } else {
            $providerType = get_class($this->AuthProvider);

            if ($providerType !== $authProviderType) {
                $isInitializationOfProviderRequired = true;
            }
        }

        if ($isInitializationOfProviderRequired) {
            $this->AuthProvider = new $authProviderType();
        }

        return $this->AuthProvider;
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

    public function loginByHash($userId, $hash, $client_ip = '', $client_useragent = '') {
        if ($userId == '' || $hash == '') {
            $this->error[] = "Nazwa użytkownika lub hash są puste";
            return false;
        }

        $client_ip = ($client_ip == '') ? $_SERVER['REMOTE_ADDR'] : $client_ip;
        $client_useragent = ($client_useragent == '') ? $_SERVER['HTTP_USER_AGENT'] : $client_ip;

        if ($username = $this->getInstanceOfAuthProvider()->loginByHash($userId, $hash)) {
            $this->createSession($username, $password, $this->getInstanceOfAuthProvider()->getUserId());
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

        if ($this->getInstanceOfAuthProvider()->login($username, $password)) {
            $this->createSession($username, $client_ip, $client_useragent);
           
            $this->handleSuccessfulLogin();
            
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

    public function handleUnauthorizedAccess() {
        if ($this->authListner == '') {
            throw new Exception("Unauthorized access attempt without AuthListner");
        }

        $this->authListner->onUnauthorizedAccess();
    }

    public function handleSuccessfulLogin() {
        if ($this->authListner == '') {
            return;
        }

        $this->authListner->onSuccessfulLogin();
    }

    public function handleFailedLogin() {
        if ($this->authListner == '') {
            throw new Exception("Login failed");
        }

        $this->authListner->onFailedLogin();
    }

    public function handleLogout() {
        if ($this->authListner == '') {
            return;
        }

        $this->authListner->onLogout();
    }

    private function createSession($username, $password, $userId) {
        $session_key = AuthSessionManager::createSession($username, $password, $userId);

        return $session_key;
    }

    private function checkMySession() {
        $isOk = false;

        try {
            AuthSessionManager::checkSession($_SESSION['auth_session'], $_SESSION['username'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            $isOk = true;
        } catch (Exception $exc) {
            $this->error[] = $exc->getMessage();
        }

        return $isOk;
    }

    public function getError() {
        if (is_array($this->error) && $this->error != array()) {
            return $this->error;
        }

        return $this->getInstanceOfAuthProvider()->getError();
    }

    public function getErrorString() {
        $error = '';

        $t_error = self::getError();

        if (is_array($t_error) && $t_error != array()) {
            $error = print_r($t_error, 1);
        }

        return $error;
    }

    public function getUsername() {
        return $this->username;
    }

    private function setUsername($username) {
        $this->username = $username;
        $this->getInstanceOfAuthProvider()->setUsername($username);
    }

    private function setUserId($id) {
        $this->user_id = $id;
        $this->getInstanceOfAuthProvider()->setUserId($id);
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function changePassword($password) {
        return $this->getInstanceOfAuthProvider()->changePassword($password);
    }

    public function checkPassword($password) {
        return $this->getInstanceOfAuthProvider()->checkPassword($password);
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

    public function addUser($username, $password) {
        $this->getInstanceOfAuthProvider()->addUser($username, $password);
    }

    /**
     * @return AuthConfigure
     */
    public function configure() {
        if (empty($this->config)) {
            $this->config = new AuthConfigure();
        }

        return $this->config;
    }

    public function setUnauthorizedAccessAttemptListner(AuthListner $listner) {
        $this->authListner = $listner;
    }

}
