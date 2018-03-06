<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of config
 *
 * @author Chris Vaughan
 */
class config {

    Const WEBMONITORJSONFILE = "data/domainrecords.json";
    Const BACKUPRECORDSJSONFILE = "data/backuprecords.json";
    Const CHANGELOG = "data/changelog.log";
    Const ERRORFROM = "admin@ramblers-webs.org.uk";
    Const ERRORTO = "admin@ramblers-webs.org.uk";

    public $imapserver = "{mail.xxxxxxxxxxx.com:143}";
    public $imapuser = "admin@xxxxxxxxxxxxxxxx.org.uk";
    public $imappassword = "xxxxxxxx";

    public function getMoveFolder($type) {
        switch ($type) {
            case email::BACKUP:
                return "INBOX/Backup";
                break;
            case email::REMOVEBACKUP:
                return "INBOX/Backup";
                break;
            case email::WEBMONITOR:
                return "INBOX/Web Monitors";
                break;
            case email::REMOVEWEBMONITOR:
                return "INBOX/Web Monitors";
                break;
            case email::JOOMLAUPDATE:
                return "INBOX/Site updates";
                break;
            default:
                return null;
                break;
        }
    }

    public function getFolderPeriod($type) {
        switch ($type) {
            case email::BACKUP:
                return new DateInterval('P10D');
                break;
            case email::WEBMONITOR:
                return new DateInterval('P14D');
                break;
            case email::JOOMLAUPDATE:
                return new DateInterval('P14D');
                break;
            default:
                return "";
                break;
        }
    }

}
