<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * @copyright  2009 Darryl Patterson <widgetapps@gmail.com>
 * @version    $Id:$
 * @link       http://www.widgetapps.ca/rotaryauction
*/


if (strpos($_SERVER['REQUEST_URI'], '/feeds') === false && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')) {
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: https://system.metrotorontorotaryauction.com' . $_SERVER['REQUEST_URI']);
}

define('APPLICATION_PATH', $_SERVER['APPLICATION_PATH']);
define('APPLICATION_ENV', $_SERVER['APPLICATION_ENV']);
define('METIS_PATH', $_SERVER['METIS_PATH']);
define('ZF_PATH', $_SERVER['ZF_PATH']);

$paths = array(ZF_PATH, METIS_PATH, APPLICATION_PATH, APPLICATION_PATH . '/library', '.');
set_include_path(implode(PATH_SEPARATOR, $paths));

require_once('Zend/Loader/Autoloader.php');
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Metis_');
$autoloader->registerNamespace('Auction_');
$autoloader->registerNamespace('models_');

// Create application, bootstrap, and run
require_once($_SERVER['METIS_PATH'] . DIRECTORY_SEPARATOR . 'Metis' . DIRECTORY_SEPARATOR . 'Application.php');
$application = new Metis_Application();
$application->bootstrap()->run();
