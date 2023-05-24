<?php

/**
 * Description of quotas
 *
 * @author ChrisV
 */
class EmailQuotas {

    static function applyQuotas($imapserver, $imapuser, $imappassword, $folder, $period) {
        echo "<h3>Folder " . $folder . "</h3>";
        $server = $imapserver . $folder;
        $imap = imap_open($server, $imapuser, $imappassword, 0, 3);
        if ($imap === false) {
            $err = "Unable to open mailbox" . PHP_EOL;
            $err .= "Mail box: " . $server . PHP_EOL;
            $err .= "User: " . $this->imapuser . PHP_EOL;
            $errors = imap_errors();
            if ($errors != false) {
                foreach ($errors as $error) {
                    $err .= $error . PHP_EOL;
                }
            }
            return;
        }

        $interval = new DateInterval($period);
        $cutoff = new DateTime();
        $cutoff->sub($interval);

        $MC = imap_check($imap);
        if ($MC->Nmsgs === 0) {
            // either no emails or no folder
            echo "<p>Folder is empty</p>";
            imap_close($imap);
            return;
        }
        // Fetch an overview for all messages in INBOX
        $result = imap_fetch_overview($imap, "1:{$MC->Nmsgs}", 0);
        $no = 0;
        foreach ($result as $overview) {
            $date = DateTime::createFromFormat('D, d M Y H:i:s O', $overview->date);
            if ($date < $cutoff) {
                imap_delete($imap, $overview->msgno);
                $no += 1;
            }
        }
        echo "Number of emails removed: " . $no;
        imap_close($imap);
    }

}
