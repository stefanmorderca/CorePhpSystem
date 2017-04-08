<?php

/**
 * Should I call you ConfigurationManager??
 */
class AuthConfigure {

    const TYPE_CONFIG = 'phpvariable';
    const TYPE_PHP_FILE = 'textfile';
    const TYPE_SQL = 'sql';
    const TYPE_LDAP = 'ldap';
    const TYPE_FACEBOOK = 'facebook';
    const TYPE_GOOGLE_AUTH = 'google';

    public function __construct() {
        if (!isset($GLOBALS['_CONFIG']['AUTH'])) {
            $GLOBALS['_CONFIG']['AUTH'] = array();
        }
    }

    public function setType($newType) {
        
    }

    public function setTypeConfig() {
        $this->setType(self::TYPE_CONFIG);
    }

    public function setTypePhpFile() {
        $this->setType(self::TYPE_PHP_FILE);
    }

    public function setTypeSql() {
        $this->setType(self::TYPE_SQL);
    }

    public function setTypeGoogleAuth() {
        $this->setType(self::TYPE_GOOGLE_AUTH);
    }

}
