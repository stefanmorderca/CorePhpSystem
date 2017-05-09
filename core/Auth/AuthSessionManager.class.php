<?php

class AuthSessionManager {

    const PHP_SESSION_IDENTIFIER_KEY = 'auth_session';
    const PHP_SESSION_USERNAME_KEY = 'username';
    const SESSION_TABLE_NAME = 'session';

    /**
     * 
     * @param type $username
     * @param type $password
     * @param type $ip
     * @param type $userAgent
     * @throws Exception
     */
    public static function createSession($username, $ip, $userAgent) {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new Exception("[$ip] is not a valid IP address");
        }

        $sessionKey = self::createSessionKey($username, $ip, $userAgent);

        $t_session = array();
        $t_session['username'] = mysql_escape_string($username);
        $t_session['session_key'] = $sessionKey;
        $t_session['ip'] = $ip;
        $t_session['useragent'] = $userAgent;

        $sql = "INSERT INTO " . self::SESSION_TABLE_NAME . " (" . implode(", ", array_keys($t_session)) . ") VALUES ('" . implode("', '", $t_session) . "');";

        if ($sessionId = my_query($sql)) {
            $sql = "SELECT * FROM " . self::SESSION_TABLE_NAME . " WHERE session_id = $sessionId limit 1";
            $t_dane = my_query($sql);

            $_SESSION[self::PHP_SESSION_IDENTIFIER_KEY] = $t_dane[0]['session_key'];
            $_SESSION[self::PHP_SESSION_USERNAME_KEY] = $username;
        }
    }

    public static function checkSession($sessionKey, $username, $ip, $userAgent) {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new Exception("[$ip] is not a valid IP address");
        }

        $t_session_row = self::getCurrentSession();

        if ($t_session_row !== false) {
            $givenKey = self::createRecurrentKey($username, $ip, $userAgent);
            $savedKey = self::createRecurrentKey($t_session_row['username'], $t_session_row['ip'], $t_session_row['useragent']);

            if ($givenKey !== $savedKey) {
                throw new Exception("Sessions exists with diffrent details");
            }
        } else {
            throw new Exception("Sessions doesn't exists");
        }

        return true;
    }

    public static function destroySession() {
        $sessionKey = $_SESSION[self::PHP_SESSION_IDENTIFIER_KEY];

        $t_session = self::getCurrentSession();

        unset($_SESSION[self::PHP_SESSION_IDENTIFIER_KEY]);
        unset($_SESSION[self::PHP_SESSION_USERNAME_KEY]);

        $sql = "UPDATE " . self::SESSION_TABLE_NAME . " SET session_key = '" . $sessionKey . "_DONE' WHERE session_id = " . $t_session['session_id'];
        my_query($sql);
    }

    public static function extendSession() {
        $t_session = self::getCurrentSession();

        $sql = "UPDATE " . self::SESSION_TABLE_NAME . " SET time_last_activity = NOW() WHERE session_id = " . $t_session['session_id'] . " AND session_key = '" . $t_session['session_key'] . "';";
        my_query($sql);
    }

    /**
     * 
     * @return array(session_id, user_id, username, useragent, ip, session_key, time_create, time_last_activity)
     * @throws Exception
     */
    public static function getCurrentSession() {
        $sessionKey = $_SESSION[self::PHP_SESSION_IDENTIFIER_KEY];
        $username = $_SESSION[self::PHP_SESSION_USERNAME_KEY];

        if (!self::isSessionKeyValid($sessionKey)) {
            throw new Exception("What the heck is that [$sessionKey]?");
        }

        $sql = "SELECT * FROM " . self::SESSION_TABLE_NAME . " WHERE username = '" . mysql_escape_string($username) . "' AND session_key = '" . $sessionKey . "' LIMIT 1";
        $t_session = my_query($sql);

        if (!(isset($t_session[0]['session_id']) && $t_session[0]['session_id'] > 0)) {
            throw new Exception("Session not found");
        }

        if (count($t_session) != 1) {
            throw new Exception("Multiple Sessions found for key[" . $sessionKey . "] - WTF?");
        }

        return $t_session[0];
    }

    /**
     * Create uniq hash 
     * 
     * @param string $sessionKey
     * @return boolean
     */
    private static function createSessionKey($username, $ip, $userAgent) {
        return md5(md5($username) . md5($ip) . md5($userAgent) . md5(microtime(true)));
    }

    /**
     * Use client meta data to generate some kind of hash that can be recreated for validation of client origins.
     * We should alosow use some sort of time ingredient that is close to login time and could be recreated.
     * (day? day and hour? but what about 23:59 or 12:59)
     * 
     * @param string $sessionKey
     * @return boolean
     */
    private static function createRecurrentKey($username, $ip, $userAgent) {
        return md5(md5($username) . md5($ip) . md5($userAgent));
    }

    /**
     * Let's check whether given sessionKey looks like some think I've could assign
     * 
     * @param string $sessionKey
     * @return boolean
     */
    private static function isSessionKeyValid($sessionKey) {
        $returnMe = true;

        $result = preg_match('/^[a-f0-9]{32}$/', $sessionKey);

        if (0 === $result || false === $result) {
            $returnMe = false;
        }

        return $returnMe;
    }

    public static function getInitializationSQL() {
        $sql = "CREATE TABLE IF NOT EXISTS `session` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `session_key` varchar(40) NOT NULL,
  `time_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_last_activity` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `username` (`username`),
  KEY `session_key` (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

        return $sql;
    }

}
