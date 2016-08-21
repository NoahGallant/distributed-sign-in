<?php
require 'login-engine.php';

if(!isset($_SESSION["sms"]) || !isset($_POST["pass"])){
  navigate('password-reset-form-1.php?e');
}

$sms = $_SESSION["sms"];
$pass = $_POST["pass"];

if(!verifyPass($pass)){
  navigate('password-reset-form-3.php');
}

$statement = $db->prepare("SELECT id, token, verify, reset from users WHERE sms = :em");
$statement->execute(array(':em' => $sms));
$returns = $statement->fetchAll();

if(!isset($_SESSION["reset-id-confirm"]) || count($returns) != 1){
  navigate('password-reset-form-1.php?e');
}

$entry = (array)($returns[0]);
$iterations = ENC_ITERS;
$iv = openssl_random_pseudo_bytes(16);
$hash = hash_pbkdf2("sha256", $pass, $iv, $iterations);
$bytes = bin2hex(openssl_random_pseudo_bytes(3));

$client = new Google_Client();
$client->setAuthConfig("oauth-credentials.json");
$client->setRedirectUri(HOME . '/login/reset-password-callback.php');

$token = $entry["token"];
$encryptedToken = hex2bin($token);
$parts = explode(IV_SEP, $encryptedToken);
$decryptedToken = openssl_decrypt($parts[0], 'AES-256-CBC', OSSL_ENC_KEY, 0, $parts[1]);
if(!$client->fetchAccessTokenWithRefreshToken($decryptedToken) || $_SESSION["reset-id-confirm"] != idFromClient($client)){
  navigate('password-reset-form-1.php?e');
}
$service = new Google_Service_Drive($client);

$statement = $db->prepare("UPDATE users SET reset = 'true' where sms = :em");
$statement->execute(array(':em' => $_SESSION["sms"]));

$response = $service->files->listFiles(array(
  'spaces' => 'appDataFolder',
  'fields' => 'nextPageToken, files(id, name)',
  'pageSize' => 10
));

foreach($response as $r){
  if($r->name == "rs-pass" || $r->name == "rs-key"){
    $service->files->delete($r->id);
  }
}

$fileMetadata = new Google_Service_Drive_DriveFile(array(
  'name' => 'rs-pass',
  'parents' => array('appDataFolder')
));

$file = $service->files->create($fileMetadata, array(
  'data' => $hash.IV_SEP.$iv,
  'mimeType' => 'text/plain',
  'uploadType' => 'multipart',
  'fields' => 'id'));

$fileMetadata = new Google_Service_Drive_DriveFile(array(
  'name' => 'rs-key',
  'parents' => array('appDataFolder')
));

$file = $service->files->create($fileMetadata, array(
  'data' => $bytes,
  'mimeType' => 'text/plain',
  'uploadType' => 'multipart',
  'fields' => 'id'));


echo "Now text the following code:<br><i>$bytes</i><br>to +14044451450.<br>Once you have done so your new password will be in affect.<br><a href='logout.php'>All set!</a>";

?>
