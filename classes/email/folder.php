<?php

/**
 * Description of inbox
 *
 * @author ChrisV
 */
class EmailFolder {

    private $mailbox = null;
    private $msgnos = [];

    public function __construct($mailbox) {
        $this->mailbox = $mailbox;
        $msgnos = imap_search($mailbox, 'ALL');
        //print_r($msgnos);
        if ($msgnos !== false) {
            $this->msgnos = $msgnos;
        } 
    }

    public function interateEmails($class, $func) {
        foreach ($this->msgnos as $msgno) {
            //  $header = imap_headerinfo($this->mailbox, $msgno, 0, 1024);
            $email = new Email($this->mailbox, $msgno);
            $class->$func($msgno, $email);
        }
        // remove deleted records
        imap_expunge($this->mailbox);
    }

}
