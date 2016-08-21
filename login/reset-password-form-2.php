<?php
require 'login-engine.php';
if(isset($_POST["sms"])){
  $sms = $_POST["sms"];
  $_SESSION["sms"] = $_POST["sms"];
}
elseif (isset($_SESSION["sms"])) {
  $sms = $_SESSION["sms"];
}
else{
  navigate('reset-password-form-1.php?e');
}
$statement = $db->prepare("SELECT id, token, verify from users WHERE sms = :em");
$statement->execute(array(':em' => $sms));
$returns = $statement->fetchAll();
if(count($returns) != 1){
  navigate('reset-password-form-1.php?e');
}
$statement = $db->prepare("UPDATE users SET reset = 'true' where sms = :em");
$statement->execute(array(':em' => $sms));

$entry = (array)($returns[0]);
if(!($id = idFromRefreshToken($entry["token"]))){
  navigate('reset-password-form-1?e');
}
$_SESSION["reset-id"] = $id;
$e = "";
if(isset($_GET["e"])){
  $e = "Wrong account :(";
}
echo $e;

$redirect_uri = HOME . '/login/reset-password-callback.php';
$client = new Google_Client();
$client->setAuthConfig("oauth-credentials.json");
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/drive.appdata");
$client->setAccessType("offline");
$client->setApprovalPrompt("force");
$client->setIncludeGrantedScopes(true);
$authURL = $client->createAuthUrl();

echo "<a href='$authURL'>Re-connect your Google account to continue</a>.";
