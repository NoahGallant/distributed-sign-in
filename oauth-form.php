<?php
require 'login-engine.php';
$statement = $db->prepare("SELECT id, token, verify from users WHERE sms = :em");
$statement->execute(array(':em' => $_SESSION["sms"]));
$returns = $statement->fetchAll();
if(count($returns) != 1){
  return false;
}
$entry = (array)($returns[0]);
if($entry["token"] != ""){
  header('Location: /');
}

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/login/oauth2callback.php';
$client = new Google_Client();
$client->setAuthConfig("oauth-credentials.json");
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/drive.appdata");
$client->setAccessType("offline");
$client->setApprovalPrompt("force");
$client->setIncludeGrantedScopes(true);
$authURL = $client->createAuthUrl();
echo "Connect your Google account to continue. This is to create a secure login.<br>";
echo "<a href='$authURL'>Connect</a>.";


?>
