<?php

require_once(realpath(dirname(__FILE__)) . '/Auth.interface.php');

/**
 * 

My "simple as fuck" example of Google OAuth2 implementation

spl_autoload_register(function ($class_name) {
    $class_name = str_replace('_', '/', $class_name);
    $class_name = str_replace('\\', '/', $class_name);

    include 'src/' . $class_name . '.php';
});

$client = new Google_Client();
$client->setClientId('0000000000000-xaxaxaxaxaxa.apps.googleusercontent.com');
$client->setClientSecret('xaxaxaxaxaxa000000000000');
$client->setRedirectUri('http://www.mypage.com/auth/');
$client->addScope(Google_Service_Plus::USERINFO_EMAIL);

$service = new Google_Service_Oauth2($client);

if (isset($_GET['logout'])) { // logout: destroy token
    unset($_SESSION['googleApiLoginToken']);
    die('Logged out.');
}

if (isset($_GET['code'])) { // we received the positive auth callback, get the token and store it in session
    $client->authenticate($_GET['code']);
    $_SESSION['googleApiLoginToken'] = $client->getAccessToken();

    header("Location: ./");
    die;
}

if (isset($_SESSION['googleApiLoginToken'])) { // extract token from session and configure client
    $token = $_SESSION['googleApiLoginToken'];
    $client->setAccessToken($token);

    $user = $service->userinfo->get(); //get user info
    print_r($user);
    * print_r($user);
     * 
     * Google_Service_Oauth2_Userinfoplus Object
     *     [email] => michal.kozak@apaczka.pl
     *     [familyName] => Kozak
     *     [gender] => male
     *     [givenName] => Michał
     *     [hd] => apaczka.pl
     *     [id] => 105768385646515829958
     *     [link] => https://plus.google.com/105768385646515829958
     *     [locale] => 
     *     [name] => Michał Kozak
     *     [picture] => https://lh3.googleusercontent.com/-5YaEuZXGWbE/AAAAAAAAAAI/AAAAAAAAAAA/er9z_olY_Yk/photo.jpg
     *     [verifiedEmail] => 1
     *     [modelData:protected] => Array
     *             [verified_email] => 1
     *             [given_name] => Michał
     *             [family_name] => Kozak
     *
}

if (!$client->getAccessToken()) { // auth call to google
    $authUrl = $client->createAuthUrl();
    header("Location: " . $authUrl);
    die;
}

 */
class AuthProviderGoogle implements iAuth {

    private $username = '';
    private $error = array();
    private $user_id = -1;

    /**
     *
     * @var Google_Client 
     */
    private $googleClient;

    public function __construct() {
        initializeClient();
        
        if (!$this->isValid() && isset($_POST['username']) && isset($_POST['password'])) {
            $this->login($_POST['username'], $_POST['password']);
        }
    }
 
    private function initializeClient() {
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId('432487073992-8gjr8itchhg4d2dppa2746eootdl0kqf.apps.googleusercontent.com');
        $this->googleClient->setClientSecret('MISV8LKdtzQpwugOfPDedIp8');
        $this->googleClient->setRedirectUri('http://www.kozak.waw.pl/auth/');
        $this->googleClient->addScope("https://www.googleapis.com/auth/userinfo.email");
    }

    public function isValid() {

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
        throw new Exception;
    }

    public function addUser($username, $password) {
        throw new Exception("How do you expect me to add account to Google cloud?");
    }

}
