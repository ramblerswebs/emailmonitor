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
$backups_processed = false;

$mailbox = imap_open($config->imapserver, $config->imapuser, $config->imappassword);
if ($mailbox === false) {
    $err = "Unable to open mailbox" . PHP_EOL;
    $errors = imap_alerts();
    if ($errors != false) {
        foreach ($errors as $error) {
            $err.= $error . PHP_EOL;
        }
    }
    sendError($err);
    die();
}
// echo "<h1>Mailboxes</h1>\n";
$folders = imap_listmailbox($mailbox, $config->imapserver, "*");

if ($folders == false) {
    sendError("Unable to access mailbox folders");
} else {
    foreach ($folders as $val) {
       // echo $val . "<br />\n";
    }
}

// echo "<h1>Headers in INBOX</h1>\n";
$headers = imap_headers($mailbox);

if ($headers == false) {
    sendError("Unable to retrieve headers");
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
    $email = new adminemail($mailbox, $msgno);
    $type = $email->getType();
    switch ($type) {
        case config::BACKUP:
            $backup = [];
            $backupname = $email->getBackupName();
            $backup['backupname'] = $backupname;
            $backup['backupdate'] = $email->getDate();
            $backup['emailsubject'] = $email->getSubject();
            $backup['domain'] = $email->getDomain();
            $backup['fromemail'] = $email->getFromEmail();
            if (array_key_exists($backupname, $backups)) {
                $record = $backups[$backupname];
                $datecreated = $backups[$backupname]['dateFirstRecord'];
                $backup['dateFirstRecord'] = $datecreated;
            } else {
                // new record
                $backup['dateFirstRecord'] = $email->getDate();
                $log->addRecord("New backup", $backupname);
            }
            $backups[$backupname] = $backup;
            $backups_processed = true;
            echo "Backup - " . $email->getBackupName() . "<br />\n";
            break;
        case config::REMOVEBACKUP:
            $backupname = $email->getBackupName();
            if (array_key_exists($backupname, $backups)) {
                $backups_processed = true;
                unset($backups[$backupname]);
                echo "Remove backup - " . $backupname . "<br />\n";
                $log->addRecord("Remove backup", $backupname);
            } else {
                sendError("Unable to remove backup for " . $backupname);
            }
            break;
        case config::JOOOMLAUPDATE:
            // there is no need to monitor these emails as they record a central file
            echo "Joomla updates " . $email->getNoUpdates() . "<br />\n";
            break;
        case config::WEBMONITOR:
            echo "Web monitor <br />\n";
            break;
        default:
            break;
    }
    $folder = $config->getMoveFolder($type);
    if ($folder != null) {
        // move email to different folder
        imap_setflag_full($mailbox, $msgno, "\\Seen");
        $ok = imap_mail_move($mailbox, $msgno, $folder);
        if ($ok === false) {
            sendError("Unable to move email to another folder: " . $folder);
        }
    }
}
// remove deleted records
imap_expunge($mailbox);
imap_close($mailbox);
// print_r($backups);
if ($backups_processed) {
    $myJSON = json_encode($backups, JSON_PRETTY_PRINT);

    //echo $myJSON;
    file_put_contents(config::BACKUPRECORDSJSONFILE, $myJSON);
// check file to see if any not backed up
    // send email to say what new sites or not backup up sites
}
$log->close();

function sendError($body) {
    $mailer = new PHPMailer\PHPMailer\PHPMailer;
    $mailer->setFrom(config::ERRORFROM);
    $mailer->addAddress(config::ERRORTO);
    //$mailer->isHTML(true);
    $mailer->Subject = "Ramblers-webs email monitor ERROR";
    $mailer->Body = $body;
    if (!$mailer->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mailer->ErrorInfo;
    } else {
        echo "ERROR - an error has ben encountered";
        echo "   ".$body;
        echo 'Email has been sent';
    }
}
