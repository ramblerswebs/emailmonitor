<?php

/*
 * Function: to scan emails perform the following acctions
 *     o record the status of Joomla backups
 *     o record the status of webmonitor scans
 *     o move emails into selected folders
 *     o apply time quotas on selected folders 
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('assert.warning', 1);
if (version_compare(PHP_VERSION, '8.0.0') < 0) {
    echo 'You MUST be running on PHP version 7.0.0 or higher, running version: ' . \PHP_VERSION . "\n";
    die();
}
// set current directory to current run directory
$exepath = dirname(__FILE__);
define('BASE_PATH', dirname(realpath(dirname(__FILE__))));
chdir($exepath);

require_once 'classes/autoload.php';

require 'classes/phpmailer/src/PHPMailer.php';
require 'classes/phpmailer/src/SMTP.php';
require 'classes/phpmailer/src/Exception.php';
spl_autoload_register('autoload');
require_once 'configtest.php';
// require_once 'config.php';

   
echo "<html><head>";
echo "<link href=\"css\scanemails.css\" rel=\"stylesheet\" type=\"text/css\" />";
echo "</head><body>";
$config = new config();
$config->initialise();
$config->process();
echo "</body></html>";