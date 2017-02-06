<?php

include_once 'ModuleAction.class.php';

class Module {

    var $filename = '';
    var $actionDefault = '';
    var $actionOnItemSelected = '';

    /**
     *
     * @var ModuleAction[] 
     */
    var $actionList = array();

    /**
     *
     * @var boolean
     */
    var $isAuthRequired;

    public function Module() {
        
    }

    /**
     * 
     * @param type $action
     * @param type $ModuleActionType
     */
    public function addAction($action, $ModuleActionType = '') {
        // is String - tworzę nową akcję o nazwie takiej jak String
        // is ModuleAction - dodaje akcję
        // is String[] - tworzę listę akcji na podstawie tablicy

        if (is_object($action) && get_class($action)) {
            if (!$this->isInActionList($action->name)) {
                $this->actionList[] = $action;
                return;
            }
        }

        if (is_string($action)) {
            $action = array($action);
        }

        foreach ($action as $key => $name) {
            $moduleAction = new ModuleAction($name);

            if (!$this->isInActionList($name)) {
                $this->actionList[] = $moduleAction;
            }
        }
    }

    public function setDefaultAction($action) {
        $this->actionDefault = $action;

        if (!$this->isInActionList($action)) {
            $this->addAction($action);
        }
    }

    public function setActionOnItemSelected($action) {
        $this->actionOnItemSelected = $action;

        if (!$this->isInActionList($action)) {
            $this->addAction($action);
        }
    }

    public function setFilename($name) {
        $this->filename = $name;
    }

    public function setNoAuthRequired() {
        $this->isAuthRequired = false;
    }

    public function setAuthRequired() {
        $this->isAuthRequired = true;
    }

    public function isInActionList($action) {
        foreach ($this->actionList as $key => $value) {
            if ($value->name == $action) {
                return true;
            }
        }

        return false;
    }

}
