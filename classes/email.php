<?php

/**
 * Description of adminemail
 *
 * @author Chris Vaughan
 */
class Email {

    private $header;
    private $subject;
    private $from;
    private $to;
    private $emailfrom;
    private $date;
    private $domain;
    private $body = "";

    public function __construct($mbox, $msgno) {
        
        $this->header = imap_headerinfo($mbox, $msgno, 0, 1024);
        if ($this->header === false) {
            // connection lost
            $this->subject = null;
        }
        $text = $this->header->subject;
        $this->subject = functions::decodeHeader($text);
        if ($this->subject === null) {
            $this->subject = "";
        }
        $this->from = $this->header->from[0];
        if (isset($this->header->to[0])) {
            $this->to = $this->header->to[0];
            $this->emailfrom = $this->from->mailbox . '@' . $this->from->host;
            $this->domain = $this->to->host;
        } else {
            $this->emailfrom = "";
            $this->domain = "";
        }
        $this->body = imap_fetchbody($mbox, $msgno, 1.2);
        $this->date = $this->header->date;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function getBody() {
        return  $this->body;
    }

    public function getFromDomain() {
        return $this->domain;
    }

    public function getFromEmail() {
        return $this->emailfrom;
    }

    public function getDate() {
        return $this->date;
    }

}
