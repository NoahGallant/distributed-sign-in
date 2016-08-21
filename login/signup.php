<?php
require 'login-engine.php';

verifyCSRF();

$cc = $_POST["cc"];
$sms = $_POST["sms"];
$smsConfirm = $_POST["sms-confirm"];

$pass = $_POST["pass"];
$passConfirm = $_POST["pass-confirm"];

$email = $_POST["email"];

if(!verifyPass($pass)){
  navigate('signup-form.php?e=pv');
}

$sms = verifySMS($cc, $sms);
if(!$sms){
  navigate('signup-form.php?e=sv');
}

if(!verifyEmail($email)){
  navigate('signup-form.php?e=ev');
}


$url = 'https://www.google.com/recaptcha/api/siteverify';
$fields = array(
 'response' => $_POST['g-recaptcha-response'],
 'secret' => RECAP_SECRET_KEY
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

if($sms == "" || $sms == null || $_POST["sms"] != $smsConfirm){
  navigate('signup-form.php?e=sms');
}
elseif($pass == "" || $pass == null || $pass != $passConfirm){
  navigate('signup-form.php?e=pass');
}
elseif (false && !$googleResponse["success"]){
  navigate(' signup-form.php?e=robot');
}
else{

  $statement = $db->prepare("SELECT id FROM users where sms = :em");
  $statement->execute(array(':em' => $sms));
  $results = $statement->fetchAll();
  if(count($results)!=0){
    navigate('signup-form.php?e=taken');
  }
  else{
  $_SESSION["sms"] = $sms;
  $iterations = ENC_ITERS;
  $iv = openssl_random_pseudo_bytes(16);
  $hash = hash_pbkdf2("sha256", $pass, $iv, $iterations);
  $_SESSION["pass-val"] = $hash.IV_SEP.$iv;

  $verify = bin2hex(openssl_random_pseudo_bytes(3));
  $statement = $db->prepare("INSERT into users (sms, token, verify, reset) values (:em, '', '$verify', 'false')");
  $statement->execute(array(':em' => $sms));

  $_SESSION["user"] = [];
  foreach(USER_FEATURES as $feature){
    $_SESSION["user"][$feature] = $_POST[$feature];
  }
  $_SESSION["user"]["email"] = $_POST["email"];
  $send = sendSMSConfirm($db, $sms);
  if(!$send){
    $statement = $db->prepare("DELETE from users where sms = :em");
    $statement->execute(array(':em' => $sms));
    navigate('signup-form.php?e=ss');
  }
  header('Location: signup-oauth-form.php');
  }}
?>
