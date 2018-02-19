<?php

/**
 * Description of adminemail
 *
 * @author Chris Vaughan
 */
class adminemail {

    private $header;
    private $subject;
    private $from;
    private $to;
    private $emailfrom;
    private $date;
    private $domain;

    public function __construct($mbox, $value) {
        $this->header = imap_headerinfo($mbox, $value, 0, 1024);
        $this->subject = $this->header->subject;
        if ($this->subject === null) {
            $this->subject = "No subject";
        }
        $this->from = $this->header->from[0];
        $this->to = $this->header->to[0];
        $this->emailfrom = $this->from->mailbox . '@' . $this->from->host;
        $this->domain = $this->to->host;
        $this->date = $this->header->date;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getFromEmail() {
        return $this->emailfrom;
    }

    public function getType() {
        if (strpos($this->subject, 'Backup ') === 0) {
            return config::BACKUP;
        }
        if (strpos($this->subject, 'Update is available') === 0) {
            return config::JOOOMLAUPDATE;
        }
        if (strpos($this->subject, 'updates are available') > 0) {
            return config::JOOOMLAUPDATE;
        }
        if (strpos($this->subject, 'WebMonitor: ') === 0) {
            return config::WEBMONITOR;
        }
        if (strpos($this->subject, 'Remove backup ') === 0) {
            return config::REMOVEBACKUP;
        }

        return config::UNKNOWN;
    }

    public function getBackupName() {
        if ($this->getType() == config::BACKUP or $this->getType() == config::REMOVEBACKUP) {
            $text = $this->subject;
            $items = explode(" ", $text);
            foreach ($items as $item) {
                switch ($item) {
                    case "Remove":
                    case "backup":
                    case "Backup":
                    case "of":
                    case "":
                    case " ":
                        break;
                    default:
                        return $item;
                        break;
                }
            }
        } else {
            return "";
        }
    }

    public function getNoUpdates() {
        if ($this->getType() == config::JOOOMLAUPDATE) {
            $text = $this->subject;
            $items = explode(" ", $text);
            $cno = $items[0];
            return intval($cno);
        }
        return 0;
    }

    public function getDate() {
        return $this->date;
    }

}
