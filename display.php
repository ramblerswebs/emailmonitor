<?php

/*
 * To display any issues wih the backups
 */
define("VERSION", "2.0");
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('assert.warning', 1);
if (version_compare(PHP_VERSION, '8.0.0') < 0) {
    echo 'You MUST be running on PHP version 8.0.0 or higher, running version: ' . \PHP_VERSION . "\n";
    die();
}
// set current directory to current run directory
$exepath = dirname(__FILE__);
define('BASE_PATH', dirname(realpath(dirname(__FILE__))));
chdir($exepath);

require_once 'classes/autoload.php';
spl_autoload_register('autoload');
require_once 'configtest.php';
$config = new config();
// read file or create new records
$file = $config->getBackupsFile();
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
// read web monitor records
$file = $config->getWebmonitorFile();
$string = file_get_contents($file);
if ($string === false) {
    $domains = [];
} else {
    $domains = json_decode($string, true);
    if ($domains === null) {
        // error
        $domains = [];
    }
}
asort($domains);
$oneweekago = new DateTime('7 days ago');
$twoweeksago = new DateTime('15 days ago'); // to cover cases where runs take different times to complete.
echo "<html><head>";
echo "<link href=\"css\display.css\" rel=\"stylesheet\" type=\"text/css\" />";
echo "</head><body>";
$header = true;
foreach ($backups as $item) {
    $backupDate = $item['backupdate'];
    $bdate = new DateTime($backupDate);
    if ($bdate <= $oneweekago) {
        if ($header) {
            $header = false;
            echo "<h2>Backups older than 7 days</h2>";
        }
        echo " [" . $bdate->format('Y-m-d') . "] - <span class='backupname'>" . $item['backupname'] . "</span><br/>";
    }
}

$header = true;
foreach ($backups as $item) {
    $backupDate = $item['backupdate'];
    $bdate = new DateTime($backupDate);
    $backupDate = $item['dateFirstRecord'];
    $firstdate = new DateTime($backupDate);
    if ($bdate == $firstdate) {
        if ($header) {
            $header = false;
            echo "<h2>New backups</h2>";
        }
        echo " [" . $bdate->format('Y-m-d') . "] - <span class='backupname'>" . $item['backupname'] . "</span><br/>";
    }
}

echo "<h2>Monitored Backups</h2>";
echo '<div id="monitoredbackups">';
$line=1;
foreach ($backups as $item) {
    $backupDate = $item['backupdate'];
    $bdate = new DateTime($backupDate);
    echo "<span class='dim'>" . sprintf("%'.04d\n", $line) . "</span>  [" . $bdate->format('Y-m-d') . "] - <span class='backupname'>" . $item['backupname'] . "</span><br/>";
    $line+=1;
}
echo '</div>';

$header = true;
foreach ($domains as $item) {
    $emaildate = $item['emaildate'];
    $bdate = new DateTime($emaildate);
    if ($bdate <= $twoweeksago) {
        if ($header) {
            $header = false;
            echo "<h2>Domains where no web monitor email for 14 days</h2>";
        }
        echo " [" . $bdate->format('Y-m-d') . "] - <span class='backupname'>" . $item['domain'] . "</span><br/>";
    }
}

echo "<h2>Monitored Domains</h2>";
echo '<div id="monitoreddomains">';
$line=1;
foreach ($domains as $item) {
    $domainDate = $item['emaildate'];
    $bdate = new DateTime($domainDate);
    echo "<span class='dim'>" . sprintf("%'.04d\n", $line) . "</span>  [" . $bdate->format('Y-m-d') . "] - <span class='domainname'>" . $item['domain'] . "</span><br/>";
    $line+=1;
}
echo '</div>';

echo "<h2>Change Log</h2>";
echo '<div id="history">';
$lines = file( $config->getChangeLog());

// Loop through our array, show HTML source as HTML source; and line numbers too.
foreach ($lines as $line_num => $line) {
    echo "<span class='dim'>" . sprintf("%'.04d\n", $line_num) . "</span> " . htmlspecialchars($line) . "<br />\n";
}
echo '</div>';
echo '<p></p>';
echo "<div class='version'>Version: ".VERSION."</div>";
echo "</body><html>";


function cmp($a, $b) {
    if ($a == $b) {
        return 0;
    }
    
    return ($a['domain'].$a['backupname'] < $b['domain'].$b['backupname']) ? -1 : 1;
}