<?php

interface DbInterface {
    
    static function connect($host, $user, $pass);

    static function selectDB($dbname, $t_connection);

    static function setCharset($charset, $t_connection);

    static function query($query, $t_connection);
}
