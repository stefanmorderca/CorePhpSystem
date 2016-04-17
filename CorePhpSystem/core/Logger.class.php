<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Logger {

    static $DEFAULT_LOG_EVENT_LEVEL = 4;
    static $DEFAULT_LOGFILE_PATH = '/private/log/';
    static $DEFAULT_LOGFILE_PREFIX = 'default';
    static $DEFAULT_LOGFILE_EXTENSION = '.log';
    static $EVENT_LEVEL_ALL = 99999;
    static $EVENT_LEVEL_TRACE = 6;
    static $EVENT_LEVEL_DEBUG = 5;
    static $EVENT_LEVEL_INFO = 4;
    static $EVENT_LEVEL_WARN = 3;
    static $EVENT_LEVEL_ERROR = 2;
    static $EVENT_LEVEL_FATAL = 1;
    static $EVENT_LEVEL_OFF = 0;

    static private function log($LogLevel, $msg, $tag) {
        if ($LogLevel == self::$EVENT_LEVEL_DEBUG) {
            self::debugHandler($msg, $tag);
        }

        if ($LogLevel > self::getLogLevel()) {
            return;
        }

        $file = self::getExecutionFileName();
        $level = self::decodeLogLevel($LogLevel);
        $myLogFile = self::getFilename($code);

        if (gettype($msg) != 'string') {
            $msg = "(" . gettype($msg) . ") " . print_r($msg, 1);
        }

        $content = date('c') . " ";
        $content .= "[$level]";
        $content .= ($tag !== '') ? "[$tag]" : '';
        $content .= "[$file]: $msg\n";

        file_put_contents($myLogFile, $content, FILE_APPEND);
    }

    /**
     * What a Terrible Failure: Report an exception that should never happen.
     * 
     * @param type $msg
     * @param type $tag
     */
    static public function wtf($msg, $tag) {
        
    }

    /**
     * Logs a message with the specific Marker at the TRACE level.
     * 
     * @param string $msg
     * @param string $tag
     */
    static public function trace($msg, $tag = '') {
        self::log(self::$EVENT_LEVEL_TRACE, $msg, $tag);
    }

    /**
     * Logs a message with the specific Marker at the DEBUG level.
     * It is also responsible for debug session prints
     * 
     * @param string $msg
     * @param string $tag
     */
    static public function debug($msg, $tag = '') {
        self::log(self::$EVENT_LEVEL_DEBUG, $msg, $tag);
    }

    /**
     * Logs a message with the specific Marker at the INFO level.
     * 
     * @param string $msg
     * @param string $tag
     */
    static public function info($msg, $tag = '') {
        self::log(self::$EVENT_LEVEL_INFO, $msg, $tag);
    }

    /**
     * Logs a message with the specific Marker at the WARN level.
     * 
     * @param string $msg
     * @param string $tag
     */
    static public function warn($msg, $tag = '') {
        self::log(self::$EVENT_LEVEL_WARN, $msg, $tag);
    }

    /**
     * Logs a message with the specific Marker at the ERROR level.
     * 
     * @param string $msg
     * @param string $tag
     */
    static public function error($msg, $tag = '') {
        self::log(self::$EVENT_LEVEL_ERROR, $msg, $tag);
    }

    /**
     * Logs a message with the specific Marker at the FATAL level.
     * 
     * @param string $msg
     * @param string $tag
     */
    static public function fatal($msg, $tag = '') {
        self::log(self::$EVENT_LEVEL_FATAL, $msg, $tag);
    }

    /**
     * 
     * @return string
     */
    static private function getFilename() {
// TODO: na podstawie konfiguracji sprawdzić, czy dany: loglevel, $tag, $file, $class nie ma filtrować logu
        $filename = Logger::$DEFAULT_LOGFILE_PATH . Logger::$DEFAULT_LOGFILE_PREFIX . date('Y-m-d') . Logger::$DEFAULT_LOGFILE_EXTENSION;

        return $filename;
    }

    static private function getLogLevel() {
        return self::$DEFAULT_LOG_EVENT_LEVEL;
    }

    static private function getExecutionFileName() {
        $t_debug = debug_backtrace();

        return $t_debug[2]['file'] . ":" . $t_debug[2]['line'];
    }

    public static function decodeLogLevel($LogLevel) {
        $returnMe = 'undefined';

        switch ($LogLevel) {
            case self::$EVENT_LEVEL_TRACE:
                $returnMe = 'TRACE';
                break;
            case self::$EVENT_LEVEL_DEBUG:
                $returnMe = 'DEBUG';
                break;
            case self::$EVENT_LEVEL_INFO:
                $returnMe = 'INFO';
                break;
            case self::$EVENT_LEVEL_WARN:
                $returnMe = 'WARN';
                break;
            case self::$EVENT_LEVEL_ERROR:
                $returnMe = 'ERROR';
                break;
            case self::$EVENT_LEVEL_FATAL:
                $returnMe = 'FATAL';
                break;
        }

        return $returnMe;
    }

}
