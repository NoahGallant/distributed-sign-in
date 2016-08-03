<?php
require 'login-engine.php';

$url = 'https://www.google.com/recaptcha/api/siteverify';
$fields = array(
 'response' => $_POST['g-recaptcha-response'],
 'secret' => $RECAP_SECRET_KEY
);
$postData = "";
foreach($fields as $key=>$value) { $postData .= $key.'='.$value.'&'; }
rtrim($postData, '&');
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$googleResponse = (array)json_decode($result);

$sms = $_POST["sms"];
$smsConfirm = $_POST["sms-confirm"];

$pass = $_POST["pass"];
$passConfirm = $_POST["pass-confirm"];

if($sms == "" || $sms == null || $sms != $smsConfirm){
  header('Location: signup-form.php?e=sms');
}
elseif($pass == "" || $pass == null || $pass != $passConfirm){
  header('Location: signup-form.php?e=pass');
}
elseif (!$googleResponse["success"]){
  header('Location: signup-form.php?e=robot');
}
else{
  $statement = $db->prepare("SELECT id FROM users where sms = :em");
  $statement->execute(array(':em' => $sms));
  $results = $statement->fetchAll();
  if(count($results)!=0){
    header('Location: signup-form.php?e=taken');
  }
  else{
  $_SESSION["sms"] = $sms;
  $_SESSION["pass-val"] = $pass;
  $verify = bin2hex(openssl_random_pseudo_bytes(2));
  $statement = $db->prepare("INSERT into users (sms, token, verify) values (:em, '', '$verify')");
  $statement->execute(array(':em' => $sms));

  $url = 'https://hooks.zapier.com/hooks/catch/1573459/4gye3h/';
  $fields = array(
   'number' => $_POST['sms'],
   'body' => "Copy and paste this token: $verify into the confirm account prompt."
  );
  $postData = "";
  foreach($fields as $key=>$value) { $postData .= $key.'='.$value.'&'; }
  rtrim($postData, '&');
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POST, count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  $googleResponse = (array)json_decode($result);
  header('Location: oauth-form.php');
  }}
?>
