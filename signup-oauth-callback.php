<?php
require 'login-engine.php'; /* gives valid PDO connection to table */


$redirect_uri = HOME.'/login/signup-oauth-callback.php';
$client = new Google_Client();
$client->setAuthConfig("oauth-credentials.json");
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/drive.appdata");
$client->setAccessType("offline");
$client->setIncludeGrantedScopes(true);

$service = new Google_Service_Drive($client);

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
$client->setAccessToken($token);

$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
$encrypted = openssl_encrypt($token["refresh_token"], 'AES-256-CBC', OSSL_ENC_KEY, 0, $iv);
$refresh = bin2hex($encrypted.IV_SEP.$iv);

if ($client->getAccessToken() && isset($_SESSION["pass-val"]) && isset($_SESSION["sms"])) {
  $passval = $_SESSION["pass-val"];
  $parts = explode(IV_SEP, $passval);
  $_SESSION["pass"] = $parts[0]; //SECURITY CHECK WOULD BE HELPFUL
  #unset($_SESSION["pass-val"]);
  #/*CLEAR PREVIOUSLY STORED APP DATA:
  $response = $service->files->listFiles(array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
  ));
  foreach($response as $r){
    $service->files->delete($r->id);
  }
  /**/

  $fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => 'pw',
    'parents' => array('appDataFolder')
  ));

  $file = $service->files->create($fileMetadata, array(
    'data' => $passval,
    'mimeType' => 'text/plain',
    'uploadType' => 'multipart',
    'fields' => 'id'));

  $fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => 'id',
    'parents' => array('appDataFolder')
  ));

  $file = $service->files->create($fileMetadata, array(
    'data' => uniqid(),
    'mimeType' => 'text/plain',
    'uploadType' => 'multipart',
    'fields' => 'id'));

  $fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => 'user',
    'parents' => array('appDataFolder')
  ));

  $userData = [];
  foreach(USER_FEATURES as $feature){
    $userData[$feature] = $_SESSION["user"][$feature];
  }
  $email = $_SESSION["user"]["email"];
  unset($_SESSION["user"]);

  $emailCode = bin2hex(openssl_random_pseudo_bytes(3));

  foreach(USER_HIDDEN_FEATURES as $feature){
    if($feature == "email_confirm_code"){
        $userData[$feature] = $emailCode;
    }
    elseif ($feature == "email") {
        $userData[$feature] = $email;
    }
    else{
        $userData[$feature] = "";
    }
  }

  $file = $service->files->create($fileMetadata, array(
    'data' => json_encode($userData),
    'mimeType' => 'text/plain',
    'uploadType' => 'multipart',
    'fields' => 'id'));


  $statement = $db->prepare("UPDATE users SET token = '$refresh' where sms = :em");
  $statement->execute(array(':em' => $_SESSION["sms"]));
  navigate('confirm-sms-form.php');
}
else{
  navigate('signup-oauth-form.php?e');
}
?>
