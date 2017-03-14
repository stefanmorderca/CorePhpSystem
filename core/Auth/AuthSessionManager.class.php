<?php

class AuthSessionManager {

    public function createSession($username, $password, $ip, $userAgent) {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new Exception("[$ip] is not a valid IP address");
        }

        $sessionKey = self::createSessionKey($username, $ip, $userAgent);

        $sql = "INSERT INTO session (username, session_key, ip, user_agent) VALUES ('" . mysql_escape_string($username) . "', '$sessionKey', $userId)";

        if ($sessionId = my_query($sql)) {
            $sql = "SELECT * FROM session WHERE session_id = $sessionId limit 1";
            $t_dane = my_query($sql);

            $_SESSION['auth_session'] = $t_dane[0]['session_key'];
            $_SESSION['username'] = $username;
        }
    }

    public function checkSession($sessionKey, $username, $ip, $userAgent) {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new Exception("[$ip] is not a valid IP address");
        }

        if (self::isSessionKeyValid($sessionKey)) {
            throw new Exception("What the heck is that [$sessionKey]?");
        }

        $sql = "SELECT * FROM session WHERE session_key = '" . $sessionKey . "'";
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

    public function destroySession($sessionKey) {
        if (self::isSessionKeyValid($sessionKey)) {
            throw new Exception("What the heck is that [$sessionKey]?");
        }
    }

    /**
     * Let's check whether given sessionKey looks like some think I've could assign
     * 
     * @param string $sessionKey
     * @return boolean
     */
    private static function createSessionKey($username, $ip, $userAgent) {
        return md5(md5($username) . md5($ip) . md5($userAgent) . md5(microtime(true)));
    }

    /**
     * Let's check whether given sessionKey looks like some think I've could assign
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

}
