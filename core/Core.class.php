<?php

/**
 * Project:     CorePphpSyetem: Antiframework for MVC websites inspired by jQuery
 * File:        Core.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU GENERAL PUBLIC LICENSE
 * Version 2 as published by the Free Software Foundation.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * GENERAL PUBLIC LICENSE Version 2 more details.
 *
 * You should have received a copy of the GNU GENERAL PUBLIC LICENSE
 * Version 2 along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com
 *
 * @link      https://github.com/stefanmorderca/CorePhpSystem
 * @copyright 2016 MK Programing Michał Kozak
 * @author    Michał Kozak
 * @package   CorePhpSystem
 * @version   0.1
 */
include_once 'Db.class.php';
include_once 'Auth.class.php';
include_once 'Module.class.php';
include_once 'CoreRequest.class.php';

class Core {

    /**
     * @var Auth 
     */
    var $auth;

    /**
     * @var DB
     */
    var $db;

    /**
     * 
     *
     * @var boolean  
     */
    var $isDebugModeOn = false;

    /**
     *
     * @var OnShutdownListner
     */
    var $onShutdownListner = '';

    /**
     *
     * @var boolean 
     */
    var $isShutdownInitialized = false;

    public function __construct() {
        $this->initDebugMode();
        $this->initShutdownFunction();

        $this->auth = new Auth();
        $this->db = new Db();
    }

    public function initShutdownFunction() {
        $shutdownFunction = 'Core::shutdown';

        register_shutdown_function($shutdownFunction);
    }

    public function initDebugMode() {
        if (isset($_GET['debug'])) {
            if ($_GET['debug'] == 'on') {
                $_SESSION['debug_mode'] = 1;
            }

            if ($_GET['debug'] == 'off') {
                unset($_SESSION['debug_mode']);
            }
        }

        if (isset($_SESSION['debug_mode']) && $_SESSION['debug_mode'] == 1) {
            $this->isDebugModeOn = true;
        }
    }

    public function setResponceHandler() {
        
    }

    /**
     * 
     * @param Module $mod
     * @return \CoreRequest
     */
    public function initModule(Module $mod) {
        $coreRequest = new CoreRequest($mod);

        if ($mod->isAuthRequired()) {
            if ($this->auth == '') {
                throw new Exception("Auth is uninitialized");
            } else {
                if (!$this->auth->isValid()) {
                    $this->auth->handleUnauthorizedAccess();
                }
            }
        }

        return $coreRequest;
    }

    public function setShutdownListner(OnShutdownListner $listner) {
        $this->onShutdownListner = $listner;
        $this->initShutdownFunction();
    }

    /**
     * 
     * @return \Module
     */
    static public function getInstanceOfModule() {
        $debug_backtrace = debug_backtrace();
        $t_debug = array_pop($debug_backtrace);
        $fileOfModule = basename($t_debug['file']);

        $module = new Module();
        $module->filename = $fileOfModule;

        return $module;
    }

    static public function shutdown($die = '') {
        /* @var $request CoreRequest */
        $resp = (isset($GLOBALS['request'])) ? $GLOBALS['request'] : '';
        /* @var $core Core */
        $core = $GLOBALS['core'];

        if ($core->isShutdownInitialized) {
            die();
        }

        $core->isShutdownInitialized = true;

        if ($core->onShutdownListner !== '') {
            $core->onShutdownListner->OnShutdown($die);
            die();
        }

        print_r($resp);
        var_dump($die);

        echo "<br>\n<br>\n";
        echo 'Script executed with success', PHP_EOL;

        echo('END');
    }

}

interface OnShutdownListner {

    function OnShutdown($die = '');
}
