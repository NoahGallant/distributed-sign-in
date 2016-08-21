<?php
require 'login-engine.php';

if(!isset($_SESSION["reset-id"])){
  navigate('/');
}

$client = new Google_Client();
$client->setAuthConfig("oauth-credentials.json");
$client->setRedirectUri(HOME . '/login/reset-password-callback.php');
$service = new Google_Service_Drive($client);
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
if ($attemptId = idFromClient($client)) {
  if($attemptId == $_SESSION["reset-id"]){
    $_SESSION["reset-id-confirm"] = $attemptId;
    header('Location: reset-password-form-3.php');
  }
  else{
    header('Location: reset-password-form-2.php?e');
  }
}
?>
