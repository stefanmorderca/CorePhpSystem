<?php

class Module {

    var $filename = '';
    var $actionDefault = '';
    var $actionOnItemSelected = '';

    /**
     *
     * @var ModuleAction[] 
     */
    var $actionsList = array();
    
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
        // in String - tworzę nową akcję o nazwie takiej jak String
        // in ModuleAction - dodaje akcję
        // in String[] - tworzę listę akcji na podstawie tablicy
    }

    public function setDefaultAction() {
        
    }

    public function setActionOnItemSelected() {
        
    }

    public function setFilename($name) {
        $this->filename = $name;
    }

    public function existsInActionList() {
        
    }

    public function setNoAuthRequired() {
        $this->isAuthRequired = false;
    }

    public function setAuthRequired() {
        $this->isAuthRequired = true;
    }

    public function isInActionList($action) {
        return ($action != '' && in_array($action, $this->actionsList)) ? true : false;
    }

}
