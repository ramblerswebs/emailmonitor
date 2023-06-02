<?php

/**
 * Description of emailProcessor
 *
 * @author ChrisV
 */
class EmailMailbox {

    private $email = null;
    private $imap = null;
    private $imapuser = null;
    private $imappassword = null;
    private $folders = [];

    public function __construct($email, $imapserver, $imapuser, $imappassword) {
        $this->imapuser = $imapuser;
        $this->imappassword = $imappassword;
        $this->email = $email;
        $this->openEmail($imapserver, $imapuser, $imappassword);
    }

    public function isRunning() {
        return $this->imap !== null;
    }

    public function getFolders() {
        return $this->folders;
    }

    private function openEmail($imapserver, $imapuser, $imappassword) {
        for ($retries = 1; $retries <= 3; $retries++) {
            $imap = imap_open($imapserver, $imapuser, $imappassword);
            if (($imap === false)) {
                sleep(2);
            } else {
                break;
            }
        }
        if ($imap === false) {
            $err = "Unable to open mailbox" . PHP_EOL;
            $err .= "Mail box: " . $imapserver . PHP_EOL;
            $err .= "User: " . $imapuser . PHP_EOL;
            $errors = imap_errors();
            if ($errors != false) {
                foreach ($errors as $error) {
                    $err .= $error . PHP_EOL;
                }
            }
            functions::sendError($this->email, $err);
            return false;
        } else {
            $this->imap = $imap;
            $this->folders = imap_list($imap, $imapserver, "*");
        }
        return true;
    }

    public function getInbox() {
        $inbox = new EmailFolder($this->imap);
        return $inbox;
    }

    public function moveEmailToFolder($msgno, $folder, $seen = true) {
        if ($seen) {
            imap_setflag_full($this->imap, $msgno, "\\Seen");
        }
        $ok = imap_mail_move($this->imap, $msgno, $folder);
        if ($ok == false) {
            functions::sendError($this->email, "Unable to move email to another folder: " . $folder);
        }
    }

    public function close() {
        if ($this->isRunning()) {
            imap_expunge($this->imap);
            imap_close($this->imap);
            $this->imap = null;
            $this->folders = [];
        }
    }

}
