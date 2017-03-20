<?php

/**
 * Should I call you DbConfigurationManager??
 */
class AuthConfigure {

    const TYPE_SQL = 'sql';
    const TYPE_LDAP = 'ldap';
    const TYPE_FACEBOOK = 'facebook';
    const TYPE_GOOGLE_AUTH = 'google';

    public function __construct() {
        if (!isset($GLOBALS['_CONFIG']['AUTH'])) {
            $GLOBALS['_CONFIG']['AUTH'] = array();
        }
    }

}
