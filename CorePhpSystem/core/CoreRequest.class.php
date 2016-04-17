<?php

class CoreRequest {

    /** REQ * */
// 1. No GET POST or AJAX - default action
// 2. AJAX or NOT
// 3. POST or GET

    var $isAjax = false;
    var $isInIframe = false;
    var $httpMethod = "GET"; // GET, POST, AJAX
    var $module;
    var $action;
    var $itemId;
    var $t_log = array();

    public function CoreRequest(Module $module) {
        $this->isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;
        $this->action = ($module->isInActionList($action)) ? $action : $module->actionDefault;

        if ($this->action != '' && !$module->isInActionList($action)) {
            $t_log[] = 'No such an action [' . $action . '], switching to default.';
        }

        if (isset($_GET['id']) && $_GET['id'] > 0 && empty($_POST)) {
            $this->itemId = $_GET['id'];

            $this->action = ($this->action == $module->actionDefault || $this->action == '') ? $module->actionOnItemSelected : $this->action;
        }
    }

}
