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

        $sql = "INSERT INTO " . self::SESSION_TABLE_NAME . " (username, session_key, ip, user_agent) VALUES ('" . mysql_escape_string($username) . "', '$sessionKey', $userId)";

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

        if (self::isSessionKeyValid($sessionKey)) {
            throw new Exception("What the heck is that [$sessionKey]?");
        }

        $sql = "SELECT * FROM " . self::SESSION_TABLE_NAME . " WHERE session_key = '" . $sessionKey . "'";
        $t_session_row = my_query($sql);

        if ($t_session_row !== false) {
            if (count($t_session_row) !== 1) {
                throw new Exception("Duplicated sessions");
            }

            $t_session_row = $t_session_row[0];

            $givenKey = self::createRecurrentKey($username, $ip, $userAgent);
            $savedKey = self::createRecurrentKey($t_session_row['username'], $t_session_row['ip'], $t_session_row['user_agent']);

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

        if (self::isSessionKeyValid($sessionKey)) {
            throw new Exception("What the heck is that [$sessionKey]?");
        }

        unset($_SESSION[self::PHP_SESSION_IDENTIFIER_KEY]);
        unset($_SESSION[self::PHP_SESSION_USERNAME_KEY]);

        $sql = "SELECT * FROM " . self::SESSION_TABLE_NAME . " WHERE username = '" . mysql_escape_string($_SESSION[self::PHP_SESSION_USERNAME_KEY]) . "' AND session_key = '" . $_SESSION[self::PHP_SESSION_IDENTIFIER_KEY] . "' LIMIT 1";
        $t_session = my_query($sql);

        if (isset($t_session[0]['session_id']) && $t_session[0]['session_id'] > 0) {
            $sql = "UPDATE " . self::SESSION_TABLE_NAME . " SET session_key = '" . $_SESSION[self::PHP_SESSION_IDENTIFIER_KEY] . "_DONE' WHERE session_id = " . $t_session[0]['session_id'];
            my_query($sql);
        } else {
            throw new Exception("Session not found");
        }
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
     * Use some data given to generate some kind of hash that can be created for validation
     * We should use some sort of time ingredient that is close to login time and can be recreated.
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

        if (false === preg_match('/^[a-f0-9]{32}$/', $sessionKey)) {
            $returnMe = false;
        }

        return $returnMe;
    }

    public static function getInitializationSQL() {
        $sql = "CREATE TABLE IF NOT EXISTS `session` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `session_key` varchar(40) NOT NULL,
  `time_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  KEY `username` (`username`,`session_key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

        return $sql;
    }

}
