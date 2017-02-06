<?php

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
    }

    public function setDefaultAction($action) {
        $this->actionDefault = $action;

        if ($this->isInActionList($action)) {
            $this->addAction($action);
        }
    }

    public function setActionOnItemSelected($action) {
        $this->actionOnItemSelected = $action;

        if ($this->isInActionList($action)) {
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
        return ($action != '' && in_array($action, $this->actionList)) ? true : false;
    }

}
