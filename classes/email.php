<?php

/**
 * Description of adminemail
 *
 * @author Chris Vaughan
 */
class email {

    Const UNKNOWN = 0;
    Const BACKUP = 1;
    Const WEBMONITOR = 2;
    Const JOOMLAUPDATE = 3;
    Const REMOVEBACKUP = 4;
    const REMOVEWEBMONITOR = 5;
    Const WEBMONITORWITHERRORS = 6;
    Const ADMINTOOLS = 7;
    Const WATCHFULUPDATES = 8;
    Const WATCHFUL = 9;

    private $header;
    private $subject;
    private $from;
    private $to;
    private $emailfrom;
    private $date;
    private $domain;

    public function __construct($mbox, $value) {
        $this->header = imap_headerinfo($mbox, $value, 0, 1024);
        $text = $this->header->subject;
        $this->subject = functions::decodeHeader($text);
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
        $comp = strtolower($this->subject);
        if (strpos($comp, 'backup ') === 0) {
            return self::BACKUP;
        }
        if (strpos($comp, 'update is available') === 0) {
            return self::JOOMLAUPDATE;
        }
        if (strpos($comp, 'updates are available') > 0) {
            return self::JOOMLAUPDATE;
        }
        if (strpos($comp, 'webmonitor: ') === 0) {
            return self::WEBMONITOR;
        }
        if (strpos($comp, 'ramblers webs watchful: ') === 0) {
            return self::WATCHFULUPDATES;
        }
        if (strpos($comp, 'remove webmonitor: ') === 0) {
            return self::REMOVEWEBMONITOR;
        }
        if (strpos($comp, 'remove webmonitor ') === 0) {
            return self::REMOVEWEBMONITOR;
        }
        if (strpos($comp, 'remove backup ') === 0) {
            return self::REMOVEBACKUP;
        }
        if (strpos($comp, 'with errors: webmonitor: ') === 0) {
            return self::WEBMONITORWITHERRORS;
        }
        if (strpos($comp, ') logged in ') > 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'security exception on ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'user account temporarily blocked on ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'failed administrator login for user ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, ' logged in on ') > 0) {
            return self::ADMINTOOLS;
        }

        if (strpos($comp, 'automatic ip blocking notification for ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'configuration options for ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'critical file modified on ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'super users were added to ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'rescue url requested on ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'critical file modified on ') === 0) {
            return self::ADMINTOOLS;
        }
        if (strpos($comp, 'info: watchful notification') === 0) {
            return self::WATCHFUL;
        }
        if (strpos($comp, 'website is now down: ') === 0) {
            return self::WATCHFUL;
        }
        if (strpos($comp, 'website is now up: ') === 0) {
            return self::WATCHFUL;
        }
        return self::UNKNOWN;
    }

    public function getBackupName() {
        if ($this->getType() == self::BACKUP or $this->getType() == self::REMOVEBACKUP) {
            $text = strtolower($this->subject);
            $items = explode(" ", $text);
            foreach ($items as $item) {
                switch ($item) {
                    case "remove":
                    case "backup":
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

    public function getDomainName() {
        switch ($this->getType()) {
            case self::WEBMONITOR :
            case self::REMOVEWEBMONITOR :
            case self::WEBMONITORWITHERRORS :
                $text = strtolower($this->subject);
                $items = explode(" ", $text);
                foreach ($items as $item) {
                    switch ($item) {
                        case "with":
                        case "errors:":
                        case "remove":
                        case "webmonitor:":
                        case "webmonitor":
                        case "new":
                        case "install:":
                        case "":
                        case " ":
                            break;
                        default:
                            return $item;
                            break;
                    }
                }

                break;

            default:
                break;
        }
        return "";
    }

    public function getNoUpdates() {
        if ($this->getType() == self::JOOMLAUPDATE) {
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
