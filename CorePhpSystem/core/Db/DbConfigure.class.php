<?php

class DbConfigure {

    const TYPE_MYSQL = 'mysql';
    const TYPE_MSSQL = 'mssql';
    const TYPE_PGSQL = 'pgsql';
    const TYPE_ORACLE = 'oracle';

    public function DbConfigure() {
        $GLOBALS['_CONFIG']['DB']['connection_list'] = array();
    }

    public function setConnection($connection_name) {
        if (in_array($connection_name, array_keys($GLOBALS['_CONFIG']['DB']['connection_list']))) {
            $GLOBALS['_CONFIG']['DB']['connection'] = $connection_name;
        } else {
            throw new Exception("setConnection: Supplied connection name [$connection_name] is unsupported");
        }
    }

    public function getCurentConnection() {
        return $GLOBALS['_CONFIG']['DB']['connection_list'][$GLOBALS['_CONFIG']['DB']['connection']];
    }

    public function addConnection($DbConnectionType, $host, $user, $pass, $base, $connection_name = '_default') {
        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name] = array();
        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name]['host'] = $host;
        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name]['base'] = $base;
        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name]['user'] = $user;
        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name]['pass'] = $pass;
        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name]['type'] = $DbConnectionType;
        $GLOBALS['_CONFIG']['DB']['connection_list'][$connection_name]['conn'] = '';

        if ($connection_name == '_default') {
            $this->setConnection('_default');
        }
    }

    public function addConnectionToMysql($host, $user, $pass, $base, $connection_name = '_default') {
        $this->addConnection(DbConfigure::TYPE_MYSQL, $host, $user, $pass, $base, $connection_name);
    }
}
