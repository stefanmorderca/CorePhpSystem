<?php

class SmartyShutdonwListner implements OnShutdownListner {

    /**
     * @var Smarty
     */
    var $smarty;

    /**
     * @var SmartyConfigure
     */
    var $config;

    function __construct() {
        
    }

    function OnShutdown() {
        require_once($this->config->getSmartyLibPath());

        $this->smarty = new Smarty();

        $this->smarty->setTemplateDir($this->config->getTemplateDir());
        $this->smarty->setCompileDir($this->config->getCompileDir());
        $this->smarty->setCacheDir($this->config->getCacheDir());
        $this->smarty->setConfigDir($this->config->getConfigDir());

        $this->smarty->debugging = false;

        $templateVars = $this->config->globalVaraviableNameToAssign;

        global $$templateVars;

        if (isset($$templateVars) && is_array($$templateVars)) {
            foreach ($$templateVars as $key => $val) {
                $this->smarty->assign($key, $val);
            }
        }

        if (isset($smarty_display)) {
            $this->smarty->assign("inFrame", true);

            if ($isJson === true) {
                header('Content-Type: application/json');
                $html = $this->smarty->fetch($smarty_display);
                $json = json_encode(array('html' => $html, 'meta' => $html_meta));
                die($json);
            } else {
                $this->smarty->display($smarty_display);
            }
        } else {
            $this->smarty->display('index.html');
        }
    }

    /**
     * @return SmartyConfigure
     */
    public function configure() {
        $this->config = new SmartyConfigure();

        return $this->config;
    }

}

class SmartyConfigure {

    var $arrayOfPluginsDir = array();
    var $smartyLibPath = 'libs/Smarty/Smarty.class.php';
    var $smartyRootDir = 'smarty';
    var $templateName = 'default';
    var $templateDir = 'template';
    var $compileDir = 'compile';
    var $cacheDir = 'cache';
    var $configDir = 'configs';
    var $debugging = false;
    var $globalVaraviableNameToAssign = 'smartyAssign';

    public function setAssignSourceValeName($variable_name) {
        $this->globalVaraviableNameToAssign = $variable_name;

        return $this;
    }

    public function setDebugModeOn() {
        $this->debugging = true;

        return $this;
    }

    public function setDebugModeOff() {
        $this->debugging = false;

        return $this;
    }

    /**
     * 
     * @param string $path to smarty library class file
     * @return SmartyConfigure
     * @throws Exception
     */
    public function setSmartyLibPath($path_to_smarty_class) {
        $this->smartyLibPath = $path_to_smarty_class;

        return $this;
    }

    public function getSmartyLibPath() {
        return $this->smartyLibPath;
    }

    /**
     * 
     * @param type $path
     * @return SmartyConfigure
     */
    public function addPluginsDir($path) {
        $path = trim($path);

        if (!in_array($path, $this->arrayOfPluginsDir)) {
            $this->arrayOfPluginsDir[] = $path;
        }

        return $this;
    }

    /**
     * 
     * @param type $path
     * @return SmartyConfigure
     */
    public function setTemplateDir($path) {
        $this->templateDir = $path;

        return $this;
    }

    /**
     * 
     * @param type $path
     * @return SmartyConfigure
     */
    public function setTemplateName($name = 'default') {
        $this->templateName = $name;

        return $this;
    }

    /**
     * 
     * @param type $path
     * @return SmartyConfigure
     * @throws Exception
     */
    public function setSmartyRootDir($path) {
        $this->smartyRootDir = $path;

        return $this;
    }

    public function getTemplateMainDir() {
        return $this->smartyRootDir . "/" . $this->templateName;
    }

    public function getTemplateDir() {
        if ($this->templateDir == '') {
            return $this->smartyRootDir . "/" . $this->templateName . "/" . $this->templateDir;
        } else {
            return $this->templateDir;
        }
    }

    public function getCompileDir() {
        return $this->smartyRootDir . "/" . $this->templateName . "/" . $this->compileDir;
    }

    public function getCacheDir() {
        return $this->smartyRootDir . "/" . $this->templateName . "/" . $this->cacheDir;
    }

    public function getConfigDir() {
        return $this->smartyRootDir . "/" . $this->templateName . "/" . $this->configDir;
    }

    public function checkConfig() {
        if (!file_exists($this->getTemplateDir())) {
            throw new Exception("The TemplateDir [" . $this->getTemplateDir() . "] does not exist");
        }

        if (!file_exists($this->getCompileDir())) {
            throw new Exception("The CompileDir [" . $this->getCompileDir() . "] does not exist");
        }

        if (!file_exists($this->getCacheDir())) {
            throw new Exception("The CacheDir [" . $this->getCacheDir() . "] does not exist");
        }

        if (!file_exists($this->getConfigDir())) {
            throw new Exception("The ConfigDir [" . $this->getConfigDir() . "] does not exist");
        }
    }

    public function initDirectories() {

        if (!file_exists($this->getTemplateMainDir())) {
            mkdir($this->getTemplateMainDir(), 0755);
        }

        if (!file_exists($this->getTemplateDir())) {
            mkdir($this->getTemplateDir(), 0755);
        }

        if (!file_exists($this->getCompileDir())) {
            mkdir($this->getCompileDir(), 0755);
        }

        if (!file_exists($this->getCacheDir())) {
            mkdir($this->getCacheDir(), 0755);
        }

        if (!file_exists($this->getConfigDir())) {
            mkdir($this->getConfigDir(), 0755);
        }
    }

}
