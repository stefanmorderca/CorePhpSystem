<?php

/**
 * Should I call you DbConfigurationManager??
 */
class DbConfigure {

    const TYPE_MYSQL = 'mysql';
    const TYPE_MSSQL = 'mssql';
    const TYPE_PGSQL = 'pgsql';
    const TYPE_ORACLE = 'oracle';

    public function __construct() {
        if (!isset($GLOBALS['_CONFIG']['DB']['connection_list'])){
            $GLOBALS['_CONFIG']['DB']['connection_list'] = array();
        }
    }

    public static function setConnection($connection_name) {
        if (in_array($connection_name, array_keys($GLOBALS['_CONFIG']['DB']['connection_list']))) {
            $GLOBALS['_CONFIG']['DB']['connection'] = $connection_name;
        } else {
            throw new Exception("setConnection: Supplied connection name [$connection_name] does not exist on connection list");
        }
    }

    public static function getCurentConnection() {
        return $GLOBALS['_CONFIG']['DB']['connection_list'][$GLOBALS['_CONFIG']['DB']['connection']];
    }

    public static function getCurentConnectionByName($connectionAlias) {
        if (!isset($GLOBALS['_CONFIG']['DB']['connection_list'][$connectionAlias])) {
            throw new Exception("There is no registered connection with alias ['$connectionAlias']");
        }

        return $GLOBALS['_CONFIG']['DB']['connection_list'][$connectionAlias];
    }

    public static function addConnection($DbConnectionType, $host, $user, $pass, $base, $connection_name = '_default') {
        $t_connecion = array();
        $t_connecion['host'] = $host;
        $t_connecion['base'] = $base;
        $t_connecion['user'] = $user;
        $t_connecion['pass'] = $pass;
        $t_connecion['type'] = $DbConnectionType;
        $t_connecion['conn'] = '';

        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name] = $t_connecion;

        if ($connection_name == '_default') {
            self::setConnection('_default');
        }
    }

    public static function addConnectionLinkToConnection($link, $connection_name = '_default') {
        if (!is_resource($link) && !is_object($link)) {
            throw new Exception("Supplied argument must be a resource. Best choice would be database connection link.");
        }

        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name]['conn'] = $link;
    }

    public static function addConnectionToMysql($host, $user, $pass, $base, $connection_name = '_default') {
        self::addConnection(DbConfigure::TYPE_MYSQL, $host, $user, $pass, $base, $connection_name);
    }

}
