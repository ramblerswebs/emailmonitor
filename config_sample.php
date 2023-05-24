<?php

/**
 * Description of config
 *
 * @author Chris Vaughan
 */
class config extends configbase {

    public function __construct() {
        $this->test = true; // displays config data 
        $this->email = "someone@somewhere.com";
        $this->imapserver = "{mail.stackmail.com:993/imap/ssl}";
        $this->imapuser = "admin@********.com";
        $this->imappassword = "*********";
        $this->changelog = "data/changelog.log";
        $this->webmonitor = [
            'file' => "data/domainrecords.json",
            'folder' => "INBOX/Web Monitors",
            'errorfolder' => "INBOX/Errors"];

        $this->backups = [
            'file' => "data/backuprecords.json",
            'folder' => "INBOX/Backup"];
        $this->defaultFolder = "INBOX/Unknown";
        $this->quotas = [
            ['folder' => 'INBOX/Admin Tools', 'period' => 'P90D'],
            ['folder' => 'INBOX/Backup', 'period' => 'P42D'],
            ['folder' => 'INBOX/Cloud Next', 'period' => 'P90D'],
            ['folder' => 'INBOX/Errors', 'period' => 'P90D'],
            ['folder' => 'INBOX/Unknown', 'period' => 'P100D'],
            ['folder' => 'INBOX/Web Monitors', 'period' => 'P28D'],
            ['folder' => 'INBOX/Your Sites', 'period' => 'P28D'],
            ['folder' => 'Sent', 'period' => 'P56D']
        ];

        // the first part of the search and body items test the start of the string
        $this->moves = [
            ['folder' => 'INBOX/Admin Tools', 'search' => ['User ', ') logged in '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['Security exception on '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['User account temporarily blocked on '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['Failed administrator login for user '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['Automatic ip blocking notification for '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['Configuration options for '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['Critical file modified on '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['Super users were added to '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['Rescue url requested on '], 'seen' => true],
            ['folder' => 'INBOX/Admin Tools', 'search' => ['Blocked request on '], 'seen' => true],
            ['folder' => 'INBOX/Cloud Next', 'search' => ['Your Upcoming Cloud Next Renewal'], 'body' => ['', 'The good news is'], 'seen' => false],
            ['folder' => 'INBOX/Cloud Next', 'search' => ['Hosting Activated for', 'with Cloud Next'], 'seen' => false],
            ['folder' => 'INBOX/Your Sites', 'search' => ['One or more of your client websites has a core update available'], 'seen' => false],
            ['folder' => 'INBOX/Your Sites', 'search' => ['Your Client Sites Have Extension Updates Available'], 'seen' => false],
            ['folder' => 'INBOX/Your Sites', 'search' => ['Sites Up : One or more'], 'seen' => false],
            ['folder' => 'INBOX/Your Sites', 'search' => ['Sites Down : One or more'], 'seen' => false],
            ['folder' => 'INBOX/Your Sites', 'search' => ['We were unable to check one or more of your client websites for core updates'], 'seen' => false],
            ['folder' => 'INBOX/Errors', 'search' => ['Ramblers Feed Error'], 'seen' => false],
            ['folder' => 'INBOX/Errors', 'search' => ['Ramblers-webs email monitor ERROR'], 'seen' => false],
        ];
    }

}
