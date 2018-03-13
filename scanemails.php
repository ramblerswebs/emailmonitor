<?php

/*
 * Function: to scan emails perform the following acctions
 *     o record the status of Joomla backups
 *     o remove backups fron the list of monitored sites
 *     o move Joomla update notifications out of the Inbox
 *     o move Web monitor emails into another folder
 *     o move Site status emails into another folder
 */
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
require 'classes/phpmailer/src/PHPMailer.php';
require 'classes/phpmailer/src/SMTP.php';
require 'classes/phpmailer/src/Exception.php';
$config = new config();
// set up change log
$log = new changelog(config::CHANGELOG);

// read file or create new records
$backups = new jsonlogfile(config::BACKUPRECORDSJSONFILE, "Backup");

// read web monitor records
$domains = new jsonlogfile(config::WEBMONITORJSONFILE, "Domain");

$mailbox = imap_open($config->imapserver, $config->imapuser, $config->imappassword);
if ($mailbox === false) {
    $err = "Unable to open mailbox" . PHP_EOL;
    $errors = imap_alerts();
    if ($errors != false) {
        foreach ($errors as $error) {
            $err.= $error . PHP_EOL;
        }
    }
    functions::sendError($err);
    die();
}
// echo "<h1>Mailboxes</h1>\n";
$folders = imap_listmailbox($mailbox, $config->imapserver, "*");

if ($folders == false) {
    functions::sendError("Unable to access mailbox folders");
} else {
    foreach ($folders as $val) {
        // echo $val . "<br />\n";
    }
}

// echo "<h1>Headers in INBOX</h1>\n";
$headers = imap_headers($mailbox);

if ($headers === false) {
    functions::sendError("Unable to retrieve headers");
} else {
    foreach ($headers as $val) {
        //   echo $val . "<br />\n";
    }
}
$msgnos = imap_search($mailbox, 'ALL');
//print_r($msgnos);
if ($msgnos === false) {
    $msgnos = [];
}

foreach ($msgnos as $msgno) {
    $header = imap_headerinfo($mailbox, $msgno, 0, 1024);
    $email = new email($mailbox, $msgno);
    $type = $email->getType();
    switch ($type) {
        case email::BACKUP:
            $item = [];
            $name = $email->getBackupName();
            $item['backupname'] = $name;
            $item['backupdate'] = $email->getDate();
            $item['emailsubject'] = $email->getSubject();
            $item['domain'] = $email->getDomain();
            $item['fromemail'] = $email->getFromEmail();
            $backups->addItem($name, $item, $email->getDate(), $log);
            break;
        case email::REMOVEBACKUP:
            $name = $email->getBackupName();
            $backups->removeItem($name, $log);
            break;
        case email::JOOMLAUPDATE:
            // there is no need to monitor these emails as they record a central file
            echo "Joomla updates " . $email->getNoUpdates() . "<br />\n";
            break;
        case email::WEBMONITOR:
        case email::WEBMONITORWITHERRORS:
            $item = [];
            $name = $email->getDomainName();
            $item['domain'] = $name;
            $item['emaildate'] = $email->getDate();
            $item['emailsubject'] = $email->getSubject();
            $domains->addItem($name, $item, $email->getDate(), $log);
            break;
        case email::REMOVEWEBMONITOR:
            $name = $email->getDomainName();
            $domains->removeItem($name, $log);
            break;
        default:
            break;
    }
    $folder = $config->getMoveFolder($type);
    if ($folder != null) {
        // move email to different folder
        imap_setflag_full($mailbox, $msgno, "\\Seen");
        $ok = imap_mail_move($mailbox, $msgno, $folder);
        if ($ok == false) {
            functions::sendError("Unable to move email to another folder: " . $folder);
        }
    }
}
// remove deleted records
imap_expunge($mailbox);
imap_close($mailbox);
// print_r($backups);
$backups->storeItems();
$domains->storeItems();
$log->close();
