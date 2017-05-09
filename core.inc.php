<?php

include_once 'core/Core.class.php';
include_once 'core/Logger.class.php';
include_once 'corePlugins/ShutdownListnerSmarty.class.php';

$path = realpath(dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . $path."/vendor");

require_once 'smarty/smarty/libs/Smarty.class.php';

$core = new Core();
$core->db->configure()->addConnectionToMysql('localhost', 'root', '!@#Delta8', 'test');

$smartyShutdown = new smartyShutdonwListner();
$smartyShutdown->configure()->setSmartyLibPath('/smarty/smarty/libs/Smarty.class.php')->setSmartyRootDir('../private/smarty')->setTemplateName("default")->initDirectories();

$core->setShutdownListner($smartyShutdown);

