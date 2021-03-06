<?php

include_once 'Db/DbConfigure.class.php';
include_once 'Db/DbInterface.class.php';
include_once 'Db/DbMysqli.class.php';

class Db {

    const CONNECTION_TYPE_MYSQL = 'mysql';
    const CONNECTION_TYPE_MSSQL = 'mssql';
    const CONNECTION_TYPE_ORACLE = 'oracle';
    const CONNECTION_TYPE_SQLITE = 'sqlite';
    const CONNECTION_TYPE_POSTGRESQL = 'postgresql';

    /**
     *
     * @var DbConfigure 
     */
    private $config;

    public function __construct() {
        
    }

    /**
     * 
     * @param type $type
     * @return \DbInterface
     */
    private function getInstanceOfLowLevel($type) {
        $className = "Db" . ucfirst(strtolower($type));

        return new $className();
    }

    private function connect($t_connection, $connname = "_default") {
        /* @var $dbLowLevel DbInterface */
        $dbLowLevel = $this->getInstanceOfLowLevel($t_connection['type']);

        if ($t_connection['conn'] == '') {
            $t_connection['conn'] = $dbLowLevel->connect($t_connection['host'], $t_connection['user'], $t_connection['pass']);
            $dbLowLevel->selectDB($t_connection['base'], $t_connection);

            DbConfigure::addConnectionLinkToConnection($t_connection['conn'], $connname);
        }

        return $t_connection['conn'];
    }

    private function getConnection($connname = '_default') {
        if ($connname == '_default' || $connname == '') {
            $t_connection = DbConfigure::getCurentConnection();
        } else {
            $t_connection = DbConfigure::getCurentConnectionByName($connname);
        }

        if ($t_connection['conn'] == '') {
            $t_connection['conn'] = self::connect($t_connection, $connname);
        }

        return $t_connection;
    }

    private function queryReal($sql, $t_connection) {
        /* @var $dbLowLevel DbInterface */
        $dbLowLevel = $this->getInstanceOfLowLevel($t_connection['type']);

        if ($t_connection['conn'] == '') {
            $t_connection['conn'] = self::connect($t_connection);
        }

        return $dbLowLevel->query($sql, $t_connection);
    }

    /**
     * 
     * @param type $sql
     * @param type $params
     * @return type
     * @throws Exception
     */
    public function query($sql, $params = array()) {
        $fetch = 'assoc';
        $type_of = 0;
        $ile = 0;
        $index = false;
        $numrows = false;

        if (isset($params['fetch'])) {
            $fetch = $params['fetch'];
        }
        if (isset($params['type_of'])) {
            $type_of = $params['type_of'];
        }
        if (isset($params['index'])) {
            $index = $params['index'];
        }
        if (isset($params['group_index'])) {
            $group_index = $params['group_index'];
        }

        $t_output = array();

        if (is_array($sql)) {
            foreach ($sql as $a) {
                $t_output[] = $this->query($a, $params);
            }
            return $t_output;
        }

        if ($fetch != 'assoc' && $fetch != 'row' && $fetch != 'array' && $fetch != 'object') {
            $fetch = 'assoc';
        }

        $query = trim($sql);
        $queryTest = strtoupper($query);

        // TODO: tu jest fail - musi być na pierwszej pozycji, czyli warunek należy przyrównać do 0
        // TODO: pytanie co z typowo Jackowymi zapytaniami? albo jeszcze gorzej Bernardowymi

        if (strpos($queryTest, 'SELECT') === 0 || strpos($queryTest, 'SHOW') === 0 || strpos($queryTest, 'DESC') === 0 || strpos($queryTest, 'EXPLAIN') === 0) {
            $type = 1;
        } elseif (strpos($queryTest, 'INSERT') === 0) {
            $type = 2;
        } elseif (strpos($queryTest, 'UPDATE') === 0) {
            $type = 3;
        } elseif (strpos($queryTest, 'DELETE') === 0) {
            $type = 4;
        } elseif (strpos($queryTest, 'ALERT') === 0) {
            $type = 5;
        } elseif (strpos($queryTest, 'DECLARE') === 0) {
            $type = 6;
        } elseif (strpos($queryTest, 'CREATE') === 0) {
            $type = 7;
        } elseif (strpos($queryTest, 'SET') === 0) {
            $type = 8;
        } else {
            $t_q = explode(" ", $query);
            throw new Exception("DB::query -> operation:" . strtoupper($t_q[0]) . " not permited");
        }

        $t_time = array();
        $t_time[] = microtime(true);

        $t_res = $this->queryReal($query, DbConfigure::getCurentConnection());

        $t_time[] = microtime(true);

        $t_output_tmp = $t_res[0];
        $numrows = $t_res[1];
        $last_id = $t_res[2];

        $ile = 0;
        
        switch ($type) {
            case 1:
                $t_time[] = microtime(true);

                if ($index === false) {
                    switch ($fetch) {
                        case 'assoc':
                            foreach ($t_output_tmp as $t_tmp) {
                                $ile++;
                                $t_output[] = $t_tmp;
                            }
                            break;
                        case 'rows' :
                            foreach ($t_output_tmp as $t_tmp) {
                                $t_row = array();
                                foreach ($t_tmp as $a) {
                                    $ile++;
                                    $t_row[] = $a;
                                }

                                $t_output[] = $t_row;
                            }
                            break;
                        case 'array':
                            foreach ($t_output_tmp as $t_tmp) {
                                $t_row = array();
                                $i = 0;
                                foreach ($t_tmp as $key => $a) {
                                    $ile++;

                                    $t_row[$i] = $a;
                                    $t_row[$key] = $a;
                                    $i++;
                                }

                                $t_output[] = $t_row;
                            }
                            break;
                    }
                } else {
                    switch ($fetch) {
                        case 'assoc':
                            if (isset($group_index)) {
                                if ($index == '') {
                                    foreach ($t_output_tmp as $t_tmp) {
                                        $t_output[$t_tmp[$group_index]][] = $t_tmp;
                                    }
                                } else {
                                    foreach ($t_output_tmp as $t_tmp) {
                                        $t_output[$t_tmp[$group_index]][$t_tmp[$index]] = $t_tmp;
                                    }
                                }
                            } else {
                                foreach ($t_output_tmp as $t_tmp) {
                                    $t_output[$t_tmp[$index]] = $t_tmp;
                                }
                            }
                            break;
                        case 'rows':
                            if (isset($group_index)) {
                                foreach ($t_output_tmp as $t_tmp) {
                                    $t_row = array();
                                    foreach ($t_tmp as $a) {
                                        $t_row[] = $a;
                                    }
                                    $t_output[$t_tmp[$group_index]][$t_tmp[$index]] = $t_row;
                                }
                            } else {
                                foreach ($t_output_tmp as $t_tmp) {
                                    $t_row = array();
                                    foreach ($t_tmp as $a) {
                                        $t_row[] = $a;
                                    }
                                    $t_output[$t_tmp[$index]] = $t_row;
                                }
                            }
                            break;
                        case 'array':
                            if ($fetch == 'array') {
                                foreach ($t_output_tmp as $t_tmp) {
                                    $t_output[$t_tmp[$group_index]][$t_tmp[$index]] = $t_tmp;
                                }
                            } else {
                                foreach ($t_output_tmp as $t_tmp) {
                                    $t_row = array();
                                    $i = 0;
                                    foreach ($t_tmp as $key => $a) {
                                        $t_row[$i] = $a;
                                        $t_row[$key] = $a;
                                        $i++;
                                    }

                                    $t_output[$t_tmp[$index]] = $t_row;
                                }
                            }
                            break;
                    }
                }
                break;
            case 2:
                $t_output = $last_id;
                break;
            case 3:
                $t_output = $numrows;
                break;
            case 4:

                break;
            case 5:

                break;
            case 6:

                break;
            case 7:

                break;
        }

        if ($type_of == 1 && $ile > 1) {
            $t_output = array_pop($t_output);
        }

        $t_time[] = microtime(true);

        $this->logQuery($sql, $t_time, $numrows, $last_id, DbConfigure::getCurentConnectionAlias());

        return $t_output;
    }

    public function queryOneRow($sql, $params = array()) {
        $result = $this->query($sql, $params);
        $returnMe = array_pop($result);
        
        return $returnMe;
    }

    public function querySingleColumn($sql, $params = array()) {
        $result = $this->queryOneRow($sql, $params);
        $returnMe = array_pop($result);
        
        return $returnMe;    }

    public function queryKeyValuePairs($sql) {
        $t_tmp = $this->query($sql);

        $t_keys = array_keys($t_tmp);

        $t_out = array();

        foreach ($t_tmp as $val) {
            $t_out[$val[$t_keys[0]]] = $val[$t_keys[1]];
        }

        return $t_out;
    }

    public function insert($table_name, $t_data, $fl_ignore = 0) {
        global $t_mysql_reserverd_words;

        foreach ($t_data as $a) {
            if (!is_array($a)) {
                $t_data = array($t_data);
                break;
            }
        }

        if (!is_array($t_data)) {
            throw new Exception("DB::insert -> input date must be array (not " . var_dump($t_data) . ")", E_USER_ERROR);
        }

        if (count($t_data) > 5000) {
            throw new Exception("DB::insert -> over 5000 records in this table, are you mad?", E_USER_ERROR);
        }

        if (count($t_data) == 0) {
            throw new Exception("DB::insert -> 0 records in this table.", E_USER_ERROR);
        }

        $t_names = array_keys($t_data[0]);

        foreach ($t_names as $key) {
            $key = "`" . $key . "`";

            if (in_array(strtoupper($key), $t_mysql_reserverd_words)) {
                
            }

            if (isset($names)) {
                $names .= ", " . $key . "";
            } else {
                $names = "" . $key . "";
            }
        }

        $t_values = '';

        foreach ($t_data as $t_tmp) {
            $values = '';

            foreach ($t_tmp as $key => $val) {
                if ($val !== null) {
                    $val = addslashes($val);
                    if ($values != '') {
                        $values .= ", '" . $val . "'";
                    } else {
                        $values = "'" . $val . "'";
                    }
                } else {
                    if ($values != '') {
                        $values .= ", NULL";
                    } else {
                        $values = "NULL";
                    }
                }
            }

            $t_values[] = '(' . $values . ')';
        }

        if ($fl_ignore == 1) {
            $sql = " IGNORE";
        } else {
            $sql = "";
        }

        $sql = "INSERT$sql INTO `$table_name` ($names) VALUES " . implode(',', $t_values);

        if (strlen($sql) > 1048576) {
            $end = count($t_data);
            $half = ($end % 2 ) ? ceil($end / 2) : $end / 2;

            $t_dane_1 = array_slice($t_data, 0, $half);
            $t_dane_2 = array_slice($t_data, $half);

            if ($this->insert($table_name, $t_dane_1, $fl_ignore) !== false) {
                if ($this->insert($table_name, $t_dane_2, $fl_ignore) !== false) {
                    return true;
                }
            }

            throw new Exception("DB::insert -> query size too large (" . strlen($sql) . "), are you mad?", E_USER_ERROR);
        }

        return $this->query($sql);
    }

    public function update($table_name, $t_data, $where = false, $protected = 0) {
        if (!isset($t_data) || !(count($t_data) > 0)) {
            throw new Exception("No data passed for update.");
        }

        if ($where == false) {
            throw new Exception("Update on all rows is not allowed");
        }

        // UPDATE only if row exists and limit UPDATE statment to only different values
        if ($protected == 1) {
            $query = "SELECT * FROM " . $table_name . " WHERE $where";
            $result = $this->query($query);

            if ($result === false) {
                throw new Exception("Row for update not found.");
            }

            foreach ($result as $key => $val_a) {
                if (key_exists($key, $t_data)) {
                    $val_b = $t_data[$key];
                    if ($val_a == $val_b) {
                        unset($t_data[$key]);
                    }
                }
            }
        }

        foreach ($t_data as $key => $val) {
            //if(get_magic_quotes_gpc() == 0) 
            $val = addslashes($val);
            if (is_string($val)) {
                $val = "'$val'";
            }

            if (!isset($values)) {
                $values = "`$key` = $val";
            } else {
                $values .= ", `$key` = $val";
            }
        }

        $query = "UPDATE `" . $table_name . "` SET $values WHERE $where";

        return $this->query($query);
    }

    public function switchToDefaultConnection() {
        
    }

    public function switchConnection() {
        
    }

    public function selectDB($dbname, $connname = '_default') {
        $t_connection = self::getConnection($connname);

        switch ($t_connection['type']) {
            case Db::CONNECTION_TYPE_ORACLE:
                return DbOracle::selectDB($dbname, $t_connection);
            case Db::CONNECTION_TYPE_MYSQL:
                return DbMysql::selectDB($dbname, $t_connection);
            case Db::CONNECTION_TYPE_POSTGRESQL:
                return DbPostgresql::selectDB($dbname, $t_connection);
            case Db::CONNECTION_TYPE_MSSQL:
                return DbMssql::selectDB($dbname, $t_connection);
            case Db::CONNECTION_TYPE_SQLITE:
                return DbSqlite::selectDB($dbname, $t_connection);
            default:
                throw new Exception("DB::selectDB fails! - unknown db type '" . $t_connection['type'] . "'", E_USER_ERROR);
        }
    }

    public function setConnectionCharset($charset, $connname = '_default') {
        $t_connection = self::getConnection($connname);

        switch ($t_connection['type']) {
            case Db::CONNECTION_TYPE_ORACLE:
                return DbOracle::setCharset($charset, $t_connection);
            case Db::CONNECTION_TYPE_MYSQL:
                return DbMysql::setCharset($charset, $t_connection);
            case Db::CONNECTION_TYPE_POSTGRESQL:
                return DbPostgresql::setCharset($charset, $t_connection);
            case Db::CONNECTION_TYPE_MSSQL:
                return DbMssql::setCharset($charset, $t_connection);
            case Db::CONNECTION_TYPE_SQLITE:
                return DbSqlite::setCharset($charset, $t_connection);
            default:
                trigger_error("DB::setConnectionCharset fails! - unknown db type '" . $t_connection['type'] . "'", E_USER_ERROR);
                throw new Exception("DB::setConnectionCharset fails!");
        }
    }

    /**
     * @return DbConfigure
     */
    public function configure() {
        if (empty($this->config)) {
            $this->config = new DbConfigure();
        }

        return $this->config;
    }

    private function logQuery($query, $time_table, $numrows, $lastid, $connname) {
        $time_start = $time_table[0];
        $time_stop = $time_table[1];
        $time_end = array_pop($time_table);
        
        $time_tin_db = round($time_stop - $time_start, 4);
        $time_total = round($time_end - $time_start, 4);

        if (!isset($GLOBALS['_DB'])) {
            $GLOBALS['_DB'] = array();
        }

        if (!isset($GLOBALS['_DB'][$connname])) {
            $GLOBALS['_DB'][$connname] = array();
            $GLOBALS['_DB'][$connname]['counter'] = array();
            $GLOBALS['_DB'][$connname]['counter']['queries'] = 0;
        }

        $queries = $GLOBALS['_DB'][$connname]['counter']['queries'];

        $GLOBALS['_DB'][$connname]['log']['queries'][$queries] = array();
        $GLOBALS['_DB'][$connname]['log']['queries'][$queries]['query'] = substr($query, 0, 255);
        $GLOBALS['_DB'][$connname]['log']['queries'][$queries]['numrows'] = $numrows;
        $GLOBALS['_DB'][$connname]['log']['queries'][$queries]['last_insert_id'] = $lastid;
        $GLOBALS['_DB'][$connname]['log']['queries'][$queries]['time'] = $time_total;
        $GLOBALS['_DB'][$connname]['log']['queries'][$queries]['time_in_db'] = $time_tin_db;

        $GLOBALS['_DB'][$connname]['counter']['queries'] ++;
    }

}
