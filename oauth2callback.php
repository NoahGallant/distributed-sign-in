<?php
require 'login-engine.php'; /* gives valid PDO connection to table */


$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/login/oauth2callback.php';
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
$encrypted = openssl_encrypt($token["refresh_token"], 'AES-256-CBC', $OSSL_ENC_KEY, 0, $iv);
$refresh = bin2hex($encrypted.$IV_SEP.$iv);

if ($client->getAccessToken() && isset($_SESSION["pass-val"]) && isset($_SESSION["sms"])) {
  $password = $_SESSION["pass-val"];
  $iterations = 1000;
  $iv = openssl_random_pseudo_bytes(16);
  $hash = hash_pbkdf2("sha256", $password, $iv, $iterations, 20);
  $_SESSION['pass'] = $hash;

  $fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => $hash,
    'parents' => array('appDataFolder')
  ));
  $file = $service->files->create($fileMetadata, array(
    'data' => '_',
    'mimeType' => 'application/json',
    'uploadType' => 'multipart',
    'fields' => 'id'));
  $response = $service->files->listFiles(array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
  ));

  $statement = $db->prepare("UPDATE users SET token = '$refresh' where sms = :em");
  $statement->execute(array(':em' => $_SESSION["sms"]));
  header('Location: confirm-sms-form.php');
}
else{
  echo "here";
}
?>
