<?php
/********************************************************************************
*                                                                               *
*   Copyright 2012 Nicolas CARPi (nicolas.carpi@gmail.com)                      *
*   http://www.elabftw.net/                                                     *
*                                                                               *
********************************************************************************/

/********************************************************************************
*  This file is part of eLabFTW.                                                *
*                                                                               *
*    eLabFTW is free software: you can redistribute it and/or modify            *
*    it under the terms of the GNU Affero General Public License as             *
*    published by the Free Software Foundation, either version 3 of             *
*    the License, or (at your option) any eLabFTWlater version.                 *
*                                                                               *
*    eLabFTW is distributed in the hope that it will be useful,                 *
*    but WITHOUT ANY WARRANTY; without even the implied                         *
*    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR                    *
*    PURPOSE.  See the GNU Affero General Public License for more details.      *
*                                                                               *
*    You should have received a copy of the GNU Affero General Public           *
*    License along with eLabFTW.  If not, see <http://www.gnu.org/licenses/>.   *
*                                                                               *
********************************************************************************/
require_once '../inc/common.php';

$user = new \Elabftw\Elabftw\User();
$crypto = new \Elabftw\Elabftw\CryptoWrapper();

$errflag = false;

/*
 * FIRST PART
 *
 * We send an email with a link + a key + the userid.
 *
 */
if (isset($_POST['email'])) {
    // Get infos about the requester (will be sent in the mail afterwards)
    // Get IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // Get user agent
    $u_agent = $_SERVER['HTTP_USER_AGENT'];

    // Sanitize post
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    // Is email in database ?
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        // Get associated userid
        $sql = "SELECT userid,username FROM users WHERE email = :email";
        $result = $pdo->prepare($sql);
        $result->execute(array(
        'email' => $email));
        $data = $result->fetch();
        $numrows = $result->rowCount();
        // Check email exists
        if ($numrows === 1) {
            // Get info to build the URL

            // the key is the encrypted user's mail address
            // so you need to have access to the secretkey and iv in config.php to get the key.
            $key = $crypto->encrypt($email);

            $protocol = 'https://';
            $reset_url = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
            $reset_link = $protocol . str_replace('app/reset', 'change-pass', $reset_url) . '?key=' . $key . '&userid=' . $data['userid'];
            // Send an email with the reset link
            // Create the message
            $footer = "\n\n~~~\nSent from eLabFTW http://www.elabftw.net\n";
            $message = Swift_Message::newInstance()
            // Give the message a subject
            ->setSubject('[eLabFTW] Password reset for ' . $data['username'])
            // Set the From address with an associative array
            ->setFrom(array(get_config('mail_from') => 'eLabFTW'))
            // Set the To addresses with an associative array
            ->setTo(array($email => $data['username']))
            // Give it a body
            ->setBody(sprintf(_('Hi. Someone (probably you) with the IP address: %s and user agent %s requested a new password on eLabFTW. Please follow this link to reset your password : %s'), $ip, $u_agent, $reset_link) . $footer);
            // generate Swift_Mailer instance
            $mailer = getMailer();
            // now we try to send the email
            try {
                $mailer->send($message);
            } catch (Exception $e) {
                // log the error
                dblog('Error', $_SERVER['REMOTE_ADDR'], $e->getMessage());
                $errflag = true;
            }
            if ($errflag) {
                // problem
                $msg_arr[] = _('There was a problem sending the email! Error was logged.');
                $_SESSION['errors'] = $msg_arr;
            } else { // no problem
                $msg_arr[] = _('Email sent. Check your INBOX.');
                $_SESSION['infos'] = $msg_arr;
            }
        } else {
            $msg_arr[] = _('Email not found in database!');
            $_SESSION['errors'] = $msg_arr;
        }
    } else {
        $msg_arr[] = _("The email is not valid.");
        $_SESSION['errors'] = $msg_arr;
    }
    header("location: ../login.php");
    exit;
}

/*
 * SECOND PART
 *
 * Update the passwords.
 */

if (isset($_POST['password']) &&
    isset($_POST['cpassword']) &&
    isset($_POST['key']) &&
    isset($_POST['userid']) &&
    $_POST['password'] === $_POST['cpassword']) {

    // get email of user
    $sql = "SELECT email FROM users WHERE userid = :userid";
    $req = $pdo->prepare($sql);
    $req->bindParam(':userid', $_POST['userid'], PDO::PARAM_INT);
    $req->execute();
    // Validate key
    if ($req->fetchColumn() != $crypto->decrypt($_POST['key'])) {
        die('Bad key.');
    }

    // Get userid
    if (filter_var($_POST['userid'], FILTER_VALIDATE_INT)) {
        $userid = $_POST['userid'];
    } else {
        die(_("Userid is not valid."));
    }
    // Replace new password in database
    if ($user->updatePassword($_POST['password'], $userid)) {
        dblog('Info', $userid, 'Password was changed for this user.');
        $msg_arr[] = _('New password updated. You can now login.');
        $_SESSION['infos'] = $msg_arr;
    } else {
        $msg_arr[] = sprintf(_("There was an unexpected problem! Please %sopen an issue on GitHub%s if you think this is a bug.") . "<br>E#452A" . $error, "<a href='https://github.com/elabftw/elabftw/issues/'>", "</a>");
        $_SESSION['errors'] = $msg_arr;
    }
    header("location: ../login.php");
}
