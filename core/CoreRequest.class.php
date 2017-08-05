<?php

/**
 * Main purpus of this class is as falows:
 * 1. No GET POST or AJAX - default action
 * 2. AJAX or NOT
 * 3. POST or GET
 */
class CoreRequest {

    var $isAjax = false;

    /**
     * Try tu deteminate if page was opend i HTML frame or iframe element
     *
     * @var type 
     */
    var $isInIframe = false;

    /**
     *
     * @var enum  GET|POST
     */
    var $httpMethod = "GET";
    var $module;

    /**
     *
     * @var ModuleAction 
     */
    var $action;
    var $itemId;
    var $t_log = array();

    public function __construct(Module $module, $action = '') {
        $actionName = '';

        $this->module = $module->filename;

        $this->isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;

        if (is_string($action)) {
            $actionName = ($module->isInActionList($action)) ? $action : $module->actionDefault;
        } else {
            $actionName = $action->name;
        }

        if ($actionName != '' && !$module->isInActionList($action)) {
            $t_log[] = 'No such an action [' . $action . '], switching to default.';
        }

        if (is_object($action) && get_class($action) == "ModuleAction") {
            $this->action = $action;
        } else {
            $this->action = new ModuleAction($actionName);
        }

        if ($this->isAjax) {
            $this->action->ajax = $this->action->name;
            $this->action->name = $this->action->default;
        }

        if (isset($_GET['id']) && $_GET['id'] > 0 && empty($_POST)) {
            $this->itemId = $_GET['id'];

            $this->action = ($this->action == $module->actionDefault || $this->action == '') ? $module->actionOnItemSelected : $this->action;
        }

        if (!empty($_POST)) {
            $this->httpMethod = 'POST';
            $this->action->post = ($this->isAjax) ? $this->action->ajax : $this->action->name;
            $this->action->name = 'ValidateForm';
        }
    }

}
