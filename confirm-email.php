<?php
require 'login-engine.php';
$user = authorizeSession($db, false);

$bits = bin2hex(openssl_random_pseudo_bytes(8));

$user["email_confirm_code"] = $bits;

$mail = new PHPMailer;

$mail->isSMTP();  // Set mailer to use SMTP
$mail->Host = 'smtp.mailgun.org';  // Specify mailgun SMTP servers
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = MAILGUN_USER; // SMTP username from https://mailgun.com/cp/domains
$mail->Password = MAILGUN_PASS; // SMTP password from https://mailgun.com/cp/domains
$mail->SMTPSecure = 'tls';   // Enable encryption, 'ssl'

$mail->From = 'mail@gallant.io'; // The FROM field, the address sending the email
$mail->FromName = APP_NAME.' Mailer'; // The NAME field which will be displayed on arrival by the email client
$mail->addAddress($user["email"], $user['name']); // Recipient's email address and optionally a name to identify him
$mail->isHTML(true);   // Set email to be sent as HTML, if you are planning on sending plain text email just set it to false

$mail->Subject = 'Confirm your email! ['.APP_NAME.']';
$body = 'Click this link to confirm your email!';
$link = HOME."/login/confirm-email-get.php?sms=".urlencode($user["sms"])."&bits=".$bits;
$mail->Body    = $body." <a href='".$link."'>Confirm Email!</a>";
$mail->AltBody = 'Click this link to confirm your email: '.$link;

if(!$mail->send()) {
    echo "Message hasn't been sent. ";
    echo 'Mail Error: ' . $mail->ErrorInfo . "\n";
} else {
    echo "Message has been sent to confirm email! :) \n";
    saveUser($user);
}
