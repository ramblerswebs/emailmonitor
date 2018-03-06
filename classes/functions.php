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

}
