<?php

/**
 * Description of configbase
 *
 * @author ChrisV
 */
abstract class Type {

    const Backup = "Backup";
    const RemoveBackup = "Remove Backup";
    const Webmonitor = "Webmonitor";
    const WebmonitorError = "Webmonitor error";
    const RemoveWebmonitor = "Remove Webmonitor";
    const FolderMove = "Move to folder";
    const Other = "Unknown";

}

abstract class configbase {

    protected $email = null;
    protected $imapserver = null;
    protected $imapuser = null;
    protected $imappassword = null;
    protected $changelog = null;
    protected $webmonitor = [];
    protected $backups = [];
    protected $quotas = null;
    protected $moves = null;
    private $log = null;
    private $backupsLog = null;
    private $domainsLog = null;
    private $inputErrors = false;

    public function getChangeLog() {
        return $this->changelog;
    }

    public function getBackupsFile() {
        return $this->backups['file'];
    }

    public function getWebmonitorFile() {
        return $this->webmonitor['file'];
    }

    public function initialise() {
        if ($this->test) {
            echo "<h2>Server Settings</h2>";
            echo "<ul>";
            $this->checkItem("Email address", $this->email);
            $this->checkItem("Imap server", $this->imapserver);
            $this->checkItem("Imap user", $this->imapuser);
            $this->checkItem("Imap password", $this->imappassword, "", true);
            $this->checkItem("Change log ", $this->changelog);
            echo "</ul>";

            echo "<h2>Webmonitor Settings</h2>";
            echo "<ul>";
            $this->checkItem("File", $this->webmonitor['file']);
            $this->checkItem("Folder", $this->webmonitor['folder']);
            $this->checkItem("Error folder", $this->webmonitor['errorfolder']);
            echo "</ul>";
            echo "<h2>Backup Settings</h2>";
            echo "<ul>";
            $this->checkItem("File", $this->backups['file']);
            $this->checkItem("Folder", $this->backups['folder']);
            echo "</ul>";
        }
// open mailbox
        $this->mailbox = new EmailMailbox($this->email, $this->imapserver, $this->imapuser, $this->imappassword);
        if (!$this->mailbox->isRunning()) {
            die();
        }
        $folders = $this->mailbox->getFolders();
        if ($this->test) {

            echo "<h2>Move folder settings</h2>";
            echo "<table>";
            echo "<th>Title</th><th>Body</th><th>Folder</th><th>Errors</th>";
            foreach ($this->moves as $move) {
                echo "<tr>";
                $title = json_encode($move['search']);
                $body = "";
                if (array_key_exists('body', $move)) {
                    $body = json_encode($move['body']);
                }
                $result = $this->checkItemExists("folder", $move);
                $folder = $result->item;
                $error = $result->error;
                if (!in_array($this->imapserver . $folder, $folders)) {
                    $error = "Folder not found";
                    $this->inputErrors = true;
                }
                echo "<td>" . $title . "</td><td>" . $body . "</td><td>" . $folder . "</td><td>" . $error . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            echo "<h2>Quota folder settings</h2>";
            echo "<table>";
            echo "<th>Folder</th><th>Period</th><th>Errors</th>";

            foreach ($this->quotas as $quota) {
                echo "<tr>";

                $period = $quota['period'];
                $result = $this->checkItemExists("folder", $quota);
                $folder = $result->item;
                $error = $result->error;
                if (!in_array($this->imapserver . $quota['folder'], $folders)) {
                    $error = "ERROR folder not found ";
                    $this->inputErrors = true;
                }
                echo "<td>" . $folder . "</td><td>" . $period . "</td><td>" . $error . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            if ($this->test) {
                $this->testEmailCompare();
            }

            if ($this->inputErrors) {
                echo "<p>Processing terminated</p>";
                $this->mailbox->close();
                die();
            }
        }
    }

    public function process() {

        $this->log = new changelog($this->changelog);

// initialise mailbox access
        $this->mailbox = new EmailMailbox($this->email, $this->imapserver, $this->imapuser, $this->imappassword);
        if (!$this->mailbox->isRunning()) {
            die();
        }
        $inbox = $this->mailbox->getInbox();
        if ($this->backups !== []) {
// read file or create new records
            $this->backupsLog = new jsonlogfile($this->backups['file'], "Backup", $this->log);
        }
        If ($this->webmonitor !== []) {
// read web monitor records
            $this->domainsLog = new jsonlogfile($this->webmonitor['file'], "Domain", $this->log);
        }
        echo "<h2>Processing emails from Inbox</h2>";
        echo "<table>";
        echo "<tr><th>Subject</th><th>Type</th><th>Folder</th></tr>";

        $inbox->interateEmails($this, "processEmail");
        echo "</table>";
        $this->mailbox->close();
        $this->backupsLog->storeItems();
        $this->domainsLog->storeItems();
        if ($this->log !== null) {
            $this->log->close();
        }
        $dow = date("l");
        if ($dow === "Friday") {
            echo "<h2>Applying Quotas to folders</h2>";
            $this->processQuotas();
        }else{
            echo "<h2>Quotas not applied today</h2>";
        }
        echo "<hr/><p>End of processing</p>";
    }

    public function processEmail($msgno, $email) {
        $subject = $email->getSubject();
        if ($subject === null) {
            echo "<tr><td>Connection closed</span></td></tr>";
            return;
        }
        $result = $this->getEmailType($email);
        switch ($result->type) {
            case Type::Backup:
                $item = [];
                $name = $this->getBackupSite($email->getSubject());
                $item['backupname'] = $name;
                $item['backupdate'] = $email->getDate();
                $item['emailsubject'] = $email->getSubject();
                $item['domain'] = $email->getFromDomain();
                $item['fromemail'] = $email->getFromEmail();
                $this->backupsLog->addItem($name, $item, $email->getDate(), $this->log);
                break;
            case Type::RemoveBackup:
                $name = $this->getBackupSite($email->getSubject());
                if (!$this->backupsLog->removeItem($name, $this->log)) {
                    functions::sendError($this->email, "Unable to remove " . $result->type . " for " . $name);
                }
                break;
            case Type::Webmonitor:
            case Type::WebmonitorError:
                $item = [];
                $name = $this->getDomainName($email->getSubject());
                $item['domain'] = $name;
                $item['emaildate'] = $email->getDate();
                $item['emailsubject'] = $email->getSubject();
                $this->domainsLog->addItem($name, $item, $email->getDate(), $this->log);
                break;
            case Type::RemoveWebmonitor:
                $name = $this->getDomainName($email->getSubject());
                if (!$this->domainsLog->removeItem($name, $this->log)) {
                    functions::sendError($this->email, "Unable to remove " . $result->type . " for " . $name);
                }
                break;
            case Type::FolderMove:
                break;
            default:
        }
        $folder = $result->folder;
        if ($folder != null) {
            $this->mailbox->moveEmailToFolder($msgno, $folder, $result->seen);
        }
        echo "<tr><td>" . $subject . "</td><td>" . $result->type . "</td><td>" . $result->folder . "</td></tr>";
    }

    private function updateDomainLog($email) {
        $item = [];
        $name = $email->getDomainName();
        $item['domain'] = $name;
        $item['emaildate'] = $email->getDate();
        $item['emailsubject'] = $email->getSubject();
        $this->domainsLog->addItem($name, $item, $email->getDate());
    }

    private function getBackupSite($subject) {

        $text = strtolower($subject);
        $items = explode(" ", $text);
        foreach ($items as $item) {
            switch ($item) {
                case "remove":
                case "remove:":
                case "backup":
                case "of":
                case "":
                case " ":
                    break;
                default:
                    return $item;
            }
        }
        return 'Unknown backup';
    }

    private function getDomainName($subject) {
        $text = strtolower($subject);
        $items = explode(" ", $text);
        foreach ($items as $item) {
            switch ($item) {
                case "with":
                case "errors:":
                case "remove":
                case "remove:":
                case "webmonitor:":
                case "webmonitor":
                case "new":
                case "install:":
                case "":
                case " ":
                    break;
                default:
                    return $item;
            }
        }
        return "";
    }

    private function getEmailType($email) {
        $result = new result($email);
        $result->folder = $this->defaultFolder;
        $result->seen = false;
        $comp = new EmailComparer($email);

        if ($comp->isOfType(['Backup ', ' of '])) {
            $result->type = Type::Backup;
            $result->folder = $this->backups['folder'];
            $result->seen = true;
            return $result;
        }
        if ($comp->isOfType(['Remove ', 'Backup ', ' of '])) {
            $result->type = Type::RemoveBackup;
            $result->folder = $this->backups['folder'];
            $result->seen = true;
            return $result;
        }
        if ($comp->isOfType(['Remove: ', 'Backup ', ' of '])) {
            $result->type = Type::RemoveBackup;
            $result->folder = $this->backups['folder'];
            $result->seen = true;
            return $result;
        }

        if ($comp->isOfType(['Webmonitor: '])) {
            $result->type = Type::Webmonitor;
            $result->folder = $this->webmonitor['folder'];
            $result->seen = true;
            return $result;
        }
        if ($comp->isOfType(['Error ', 'Webmonitor: '])) {
            $result->type = Type::WebmonitorError;
            $result->folder = $this->webmonitor['errorfolder'];
            $result->seen = false;
            return $result;
        }

        if ($comp->isOfType(['Remove ', 'Webmonitor: '])) {
            $result->type = Type::RemoveWebmonitor;
            $result->folder = $this->webmonitor['folder'];
            $result->seen = false;
            return $result;
        }
        if ($comp->isOfType(['Remove: ', 'Webmonitor: '])) {
            $result->type = Type::RemoveWebmonitor;
            $result->folder = $this->webmonitor['folder'];
            $result->seen = false;
            return $result;
        }

        foreach ($this->moves as $move) {
            $body = [];
            if (array_key_exists('body', $move)) {
                $body = $move['body'];
            }
            if ($comp->isOfType($move['search'], $body)) {
                $result->type = Type::FolderMove;
                $result->folder = $move['folder'];
                $result->seen = $move['seen'];
                return $result;
            }
        }
        return $result;
    }

    private function processQuotas() {
        foreach ($this->quotas as $item) {
            $folder = $item['folder'];
            $period = $item['period'];
            EmailQuotas::applyQuotas($this->imapserver, $this->imapuser, $this->imappassword, $folder, $period);
        }
    }

    public function checkItem($name, $item, $extra = "", $private = false) {
        if ($item === null) {
            $this->inputErrors = true;
            echo "<li>Value of <b>" . $name . "</b> not set</li>";
            return;
        }
        if (!is_string($item)) {
            $this->inputErrors = true;
            echo "<li>Value of <b>" . $name . "</b> is not a string</li>";
            return;
        }

        if ($private) {
            echo "<li><b>" . $name . "</b>: *******  " . $extra . "</li>";
        } else {
            echo "<li><b>" . $name . "</b>: " . $item . " " . $extra . "</li>";
        }
    }

    public function checkItemExists($name, $array) {
        $result = new class {
            
        };
        $result->error = "";
        if (!array_key_exists($name, $array)) {
            $this->inputErrors = true;
            $result->error = "<b>" . $name . "</b> not set";
            return $result;
        }
        $result->item = $array[$name];
        if (!is_string($result->item)) {
            $this->inputErrors = true;
            $result->error = "Value of <b>" . $name . "</b> is not a string";
        }
        return $result;
    }

    private function testEmailCompare() {
        $comp = new EmailComparer(null);
        $comp->testCompareItem();
    }

    public function __destruct() {
        
    }

}

class result {

    public $email = null;
    public $folder = null;
    public $seen = false;
    public $type = type::Other;

    public function __construct($email) {
        $this->email = $email;
    }

}
