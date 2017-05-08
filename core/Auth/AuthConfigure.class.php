<?php

/**
 * Should I call you ConfigurationManager??
 */
class AuthConfigure {

    const TYPE_CONFIG = 'AuthProviderPhpVariable';
    const TYPE_PHP_FILE = 'AuthProviderLocalPhpFile';
    const TYPE_SQL = 'AuthProviderSql';
    const TYPE_LDAP = '';
    const TYPE_FACEBOOK = '';
    const TYPE_GOOGLE_AUTH = 'AuthProviderGoogle';

    public static function setType($newType) {
        $GLOBALS['_CONFIG']['AUTH']['type'] = $newType;
    }

    public static function getType() {
        return $GLOBALS['_CONFIG']['AUTH']['type'];
    }

    public static function getAuthProviderClass() {
        return $GLOBALS['_CONFIG']['AUTH']['type'];
    }

    public static function setTypeConfig() {
        self::setType(self::TYPE_CONFIG);
    }

    public static function setTypePhpFile() {
        self::setType(self::TYPE_PHP_FILE);
    }

    public static function setTypeSql() {
        self::setType(self::TYPE_SQL);
    }

    public static function setTypeGoogleAuth() {
        self::setType(self::TYPE_GOOGLE_AUTH);
    }

}
