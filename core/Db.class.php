<?php

include_once 'Db/DbConfigure.class.php';
include_once 'Db/DbInterface.class.php';
include_once 'Db/DbMysqli.class.php';

class Db {

    public function Db() {
        
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

    private function queryReal($sql, $t_connection) {
        /* @var $dbLowLevel DbInterface */
        $dbLowLevel = $this->getInstanceOfLowLevel($t_connection['type']);

        if (!is_resource($t_connection['conn'])) {
            $t_connection['conn'] = $dbLowLevel->connect($t_connection['host'], $t_connection['user'], $t_connection['pass']);
            $dbLowLevel->selectDB($t_connection['base'], $t_connection);
        }

        return $dbLowLevel->query($sql, $t_connection);
    }

    public function query($sql) {
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
        // TODO: pytanie co z typowo Jackowymi zapytaniami?

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
        $ile = $t_res[1];
        $last_id = $t_res[2];

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
                $t_output = $ile;
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

        $this->logQuery($sql, $t_time, $numrows, $last_id);

        return $t_output;
    }

    static public function queryOneRow($sql) {
        
    }

    static public function querySingleColumn($sql) {
        
    }

    static public function queryKeyValuePairs($sql) {
        
    }

    static public function insert($table_name, $t_dane, $fl_ignore = 0) {
        global $t_mysql_reserverd_words;

        foreach ($t_dane as $a) {
            if (!is_array($a)) {
                $t_dane = array($t_dane);
                break;
            }
        }

        if (!is_array($t_dane)) {
            throw new Exception("DB::insert -> input date must be array (not " . var_dump($t_dane) . ")", E_USER_ERROR);
        }

        if (count($t_dane) > 5000) {
            throw new Exception("DB::insert -> over 5000 records in this table, are you mad?", E_USER_ERROR);
        }

        if (count($t_dane) == 0) {
            throw new Exception("DB::insert -> 0 records in this table.", E_USER_ERROR);
        }

        $t_names = array_keys($t_dane[0]);

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
        $values_all = '';
        $i = 0;

        foreach ($t_dane as $t_tmp) {
            $values = '';
            foreach ($t_tmp as $key => $val) {
                if ($val !== null) {
                    $val = mysql_real_escape_string($val);
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
            $end = count($t_dane);
            $half = ($end % 2 ) ? ceil($end / 2) : $end / 2;

            $t_dane_1 = array_slice($t_dane, 0, $half);
            $t_dane_2 = array_slice($t_dane, $half);

            if ($this->insert($table_name, $t_dane_1, $fl_ignore) !== false) {
                if ($this->insert($table_name, $t_dane_2, $fl_ignore) !== false) {
                    return true;
                }
            }

            throw new Exception("DB::insert -> query size too large (" . strlen($sql) . "), are you mad?", E_USER_ERROR);
        }

        return $this->query($sql);
    }

    public function update() {
        
    }

    public function switchToDefaultConnection() {
        
    }

    public function switchConnection() {
        
    }

    public function selectDB($dbname, $connname = '_default') {
        if ($connname == '_default' || $connname == '') {
            $t_connection = DbConfigure::getCurentConnection();
        } else {
            $t_connection = DbConfigure::getCurentConnectionByName($connname);
        }

        switch ($t_connection['type']) {
            case 'oracle':
                return DbOracle::selectDB($dbname, $t_connection);
                break;
            case 'mysql':
                return DbMysql::selectDB($dbname, $t_connection);
                break;
            default:
                throw new Exception("DB::init fails! - unknown db type '" . $t_connection['type'] . "'", E_USER_ERROR);
                break;
        }
    }

    public function setConnectionCharset($charset, $connname = '_default') {
        if ($connname == '_default' || $connname == '') {
            $t_connection = DbConfigure::getCurentConnection();
        } else {
            $t_connection = DbConfigure::getCurentConnectionByName($connname);
        }

        switch ($t_connection['type']) {
            case 'oracle':
                return DbOracle::setCharset($charset, $t_connection);
                break;
            case 'mysql':
                return DbMysql::setCharset($charset, $t_connection);
                break;
            default:
                trigger_error("DB::init fails! - unknown db type '" . $t_connection['type'] . "'", E_USER_ERROR);
                throw new Exception("DB::connect fails!");
                break;
        }
    }

    /**
     * @return DbConfigure
     */
    public function configure() {
        $config = new DbConfigure();

        return $config;
    }

    private function logQuery($sql, $time_table, $numrows, $latid) {
        // $time_start = $time_table[0];
        // $time_stop = array_pop($time_table);
        // $time_total = round($time_stop - $time_start, 4);

        $time_start = $time_table[0];
        $time_stop = $time_table[1];

        $time_tin_db = round($time_stop - $time_start, 4);

        @$GLOBALS['_DB'][$connname]['counter']['queries'] ++;
        @$GLOBALS['_DB'][$connname]['log']['queries'][$GLOBALS['_DB'][$connname]['counter']['queries']]['query'] = substr($query, 0, 255);
        @$GLOBALS['_DB'][$connname]['log']['queries'][$GLOBALS['_DB'][$connname]['counter']['queries']]['numrows'] = $ile;
        @$GLOBALS['_DB'][$connname]['log']['queries'][$GLOBALS['_DB'][$connname]['counter']['queries']]['time'] = $time_total;
        @$GLOBALS['_DB'][$connname]['log']['queries'][$GLOBALS['_DB'][$connname]['counter']['queries']]['time_in_db'] = $time_tin_db;
    }

}
