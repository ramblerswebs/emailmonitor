<?php

/*
 * To display any issues wih the backups
 */
define("VERSION", "1.00"); 
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('assert.warning', 1);
if (version_compare(PHP_VERSION, '7.0.0') < 0) {
    echo 'You MUST be running on PHP version 7.0.0 or higher, running version: ' . \PHP_VERSION . "\n";
    die();
}
// set current directory to current run directory
$exepath = dirname(__FILE__);
define('BASE_PATH', dirname(realpath(dirname(__FILE__))));
chdir($exepath);
require_once 'config.php';
require_once 'classes/autoload.php';
$config = new config();
// read file or create new records
$file = config::BACKUPRECORDSJSONFILE;
$string = file_get_contents($file);
if ($string === false) {
    $backups = [];
} else {
    $backups = json_decode($string, true);
    if ($backups === null) {
        // error
        $backups = [];
    }
}
uasort($backups, 'cmp');
$date = new DateTime('7 days ago');
echo "<html><head>";
echo "<link href=\"display.css\" rel=\"stylesheet\" type=\"text/css\" />";
echo "</head><body>";
$header = true;
foreach ($backups as $backup) {
    $backupDate = $backup['backupdate'];
    $bdate = new DateTime($backupDate);
    if ($bdate <= $date) {
        if ($header) {
            $header = false;
            echo "<h2>Backups older than 7 days</h2>";
        }
        echo " [" . $bdate->format('Y-m-d') . "] - <span class='backupname'>" . $backup['backupname'] . "</span><br/>";
    }
}

$header = true;
foreach ($backups as $backup) {
    $backupDate = $backup['backupdate'];
    $bdate = new DateTime($backupDate);
    $backupDate = $backup['dateFirstRecord'];
    $firstdate = new DateTime($backupDate);
    if ($bdate == $firstdate) {
        if ($header) {
            $header = false;
            echo "<h2>New backups</h2>";
        }
        echo " [" . $bdate->format('Y-m-d') . "] - <span class='backupname'>" . $backup['backupname'] . "</span><br/>";
    }
}

echo "<h2>Monitored Backups</h2>";
echo '<div id="monitoredbackups">';
$line=1;
foreach ($backups as $backup) {
    $backupDate = $backup['backupdate'];
    $bdate = new DateTime($backupDate);
    echo "<span class='dim'>" . sprintf("%'.04d\n", $line) . "</span>  [" . $bdate->format('Y-m-d') . "] - <span class='backupname'>" . $backup['backupname'] . "</span><br/>";
    $line+=1;
}
echo '</div>';

echo "<h2>Change Log</h2>";
echo '<div id="history">';
$lines = file(config::CHANGELOG);

// Loop through our array, show HTML source as HTML source; and line numbers too.
foreach ($lines as $line_num => $line) {
    echo "<span class='dim'>" . sprintf("%'.04d\n", $line_num) . "</span> " . htmlspecialchars($line) . "<br />\n";
}
echo '</div>';
echo "<div class='version'>Version: ".VERSION."</div>";
echo "</body><html>";


function cmp($a, $b) {
    if ($a == $b) {
        return 0;
    }
    
    return ($a['domain'].$a['backupname'] < $b['domain'].$b['backupname']) ? -1 : 1;
}