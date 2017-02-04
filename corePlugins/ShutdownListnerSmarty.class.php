<?php

class smartyShutdonwListner implements OnShutdownListner {

    /**
     * @var Smarty
     */
    var $smarty;

    /**
     * @var SmartyConfigure
     */
    var $config = array();

    function __construct() {
        
    }

    function OnShutdown() {
        die($this->config->getSmartyLibPath());
        require_once($this->config->getSmartyLibPath());

        $html['template_sticky_notes'] = 1;
        $html['sticky_notes'] = $jsonNotes;

        $smarty = new Smarty();

        $theme = 'default';

        if (isset($_GET['theme']) && file_exists('theme/' . $_GET['theme'])) {
            $theme = $_GET['theme'];
            $_SESSION['theme'] = $theme;
        }

        if (isset($_SESSION['theme']) && $_SESSION['theme'] != '') {
            $theme = $_SESSION['theme'];
        }

        $smarty->addPluginsDir('libs/Smarty/plugins');
        $smarty->setTemplateDir('theme/' . $theme);
        $smarty->setCompileDir('smarty/compile/' . $theme);
        $smarty->setCacheDir('smarty/cache/' . $theme);
        $smarty->setConfigDir('smarty/configs/' . $theme);

        $smarty->debugging = false;

        if (isset($html) && is_array($html)) {
            foreach ($html as $key => $val) {
                $smarty->assign($key, $val);
            }
        }

        $time_stop = microtime(1) - $time_start;

        $smarty->assign('moduleFile', basename($moduleFile));
        $smarty->assign('auth', $auth);
        $smarty->assign('t_user', $t_user);
        $smarty->assign('time_stop', $time_stop);
        $smarty->assign('template_dir', 'theme/' . $theme);

        if ($_SESSION['debug_mode'] == 1) {
            $smarty->assign('debug', $debug);
        }

        if (isset($smarty_display)) {

            $smarty->assign("inFrame", true);

            if ($isJson === true) {
                header('Content-Type: application/json');
                $html = $smarty->fetch($smarty_display);
                $json = json_encode(array('html' => $html, 'meta' => $html_meta));
                die($json);
            } else {
                $smarty->display($smarty_display);
            }
        } else {
            $smarty->display('index.html');
        }
    }

    /**
     * @return SmartyConfigure
     */
    public function configure() {
        $config = new SmartyConfigure();

        return $config;
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
        return $smartyLibPath;
    }

    /**
     * 
     * @param string $path
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
     * @param string $path
     * @return SmartyConfigure
     */
    public function setTemplateDir($path) {
        $this->templateDir = $path;

        return $this;
    }

    /**
     * 
     * @param string $path
     * @return SmartyConfigure
     */
    public function setTemplateName($name = 'default') {
        $this->templateName = $name;

        return $this;
    }

    /**
     * 
     * @param string $path
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
        return $this->smartyRootDir . "/" . $this->templateName . "/" . $this->templateDir;
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
            mkdir($this->getTemplateDir(), 0777);
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
