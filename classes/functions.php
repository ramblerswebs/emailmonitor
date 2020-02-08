<?php

/**
 * Description of functions
 *
 * @author Chris Vaughan
 */
class functions {

    public static function sendError($body) {
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
            echo "   " . $body;
            echo 'Email has been sent';
        }
    }

    public static function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
    
    public static function decodeHeader($text) {
    $elements = imap_mime_header_decode($text);
    $out = '';
    for ($i = 0; $i < count($elements); $i++) {
       // echo "Charset: {$elements[$i]->charset}\n";
       // echo "Text: {$elements[$i]->text}\n\n";
        $out.=$elements[$i]->text;
    }
    return $out;
}

}
