 <?php
require 'vendor/autoload.php';
require '.env.php';
require 'base.php';

session_start();

$dbh = "pgsql:host=".DB_HOST.";port=5432;dbname=".DB_NAME.";user=".DB_USER.";password=".DB_PASSWORD;
$db = new PDO($dbh);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

//FOR DEVELOPMENT
//==========
ini_set('display_errors', 1);
//==========

//FOR PRODUCTION
//==========
//ini_set('display_errors', 0);
ini_set('log_errors', 1);
//==========

//CREATE TABLE
//===========
/*
if($pdo){
  $drop = 'DROP TABLE users';
  $pdo->exec($drop);
  $table ='CREATE TABLE IF NOT EXISTS users  (id serial PRIMARY KEY, sms text NOT NULL, token text NOT NULL, verify text NOT NULL, reset text)';
  $pdo->exec($table);
}
/**/
//===========

function navigate($location){
  header("Location: $location");
  exit();
}

function signInPost($pdo){
  $sms = $_POST["sms"];
  return signInRaw($pdo, $sms, $_POST["pass"], false);
}

function signInSession($pdo){
  $sms = $_SESSION["sms"];
  $pass = $_SESSION["pass"];
  return signInRaw($pdo, $sms, $pass, true);
}
function signInRaw($pdo, $sms, $pass, $alreadyHashed=false){
  $statement = $pdo->prepare("SELECT id, token, verify from users WHERE sms = :em");
  $statement->execute(array(':em' => $sms));
  $returns = $statement->fetchAll();
  if(count($returns) != 1){
    return false;
  }
  $entry = (array)($returns[0]);
  if($entry["token"] == ""){
    return 1;
  }
  $client = new Google_Client();
  $client->setAuthConfig("oauth-credentials.json");
  $encryptedToken = hex2bin($entry["token"]);
  $parts = explode(IV_SEP, $encryptedToken);
  $decryptedToken = openssl_decrypt($parts[0], 'AES-256-CBC', OSSL_ENC_KEY, 0, $parts[1]);
  if(!$client->fetchAccessTokenWithRefreshToken($decryptedToken)){
    return false;
  }
  $service = new Google_Service_Drive($client);
  $response = $service->files->listFiles(array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
  ));
  $files = $response->files;
  $pwFileId = "";
  $idFileId = "";
  $userFileId = "";
  foreach($files as $f){
    if ($f->name == "pw"){
      $pwFileId = $f->id;
    }
    elseif ($f->name == "id") {
      $idFileId = $f->id;
    }
    elseif ($f->name == "user") {
      $userFileId = $f->id;
    }
  }
  $passHash = $service->files->get($pwFileId, array(
    'alt' => 'media' ))->getBody()->__toString();
  $user = (array)json_decode($service->files->get($userFileId, array(
    'alt' => 'media' ))->getBody()->__toString());
  $user["_token"] = $decryptedToken;
  $user["sms"] = $sms;
  $parts = explode(':', $passHash);
  $salt = $parts[1];
  $hash = $parts[0];
  if(!$alreadyHashed){
    $pass = hash_pbkdf2("sha256", $pass, $salt, ENC_ITERS);
  }
  $userId = $service->files->get($idFileId, array(
      'alt' => 'media' ))->getBody()->__toString();
  if(hash_equals($pass, $hash)){
    if($entry["verify"] == "_"){
      $_SESSION["pass"] = $pass;
      return $user;
    }
    else{
      return $entry["verify"];
    }
  }else{
    return false;
  }
}

function sendSMSConfirm($pdo, $sms){
  $statement = $pdo->prepare("SELECT verify from users WHERE sms = :em");
  $statement->execute(array(':em' => $sms));
  $returns = $statement->fetchAll();
  if(count($returns) != 1){
    return false;
  }
  $entry = (array)($returns[0]);
  if($entry["verify"] == "_"){
    return false;
  }
  $entry = (array)($returns[0]);
  $verify = $entry["verify"];

  $url = ZAPIER_HOOK_URL;
  $fields = array(
   'number' => $sms,
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
  return $result;
  return (array)json_decode($result);
}

function getUser($pdo, $sms){ //don't user this for user verification, use, authorizeSession
  $statement = $pdo->prepare("SELECT id, token, verify from users WHERE sms = :em");
  $statement->execute(array(':em' => $sms));
  $returns = $statement->fetchAll();
  if(count($returns) != 1){
    return false;
  }
  $entry = (array)($returns[0]);
  if($entry["token"] == ""){
    return 1;
  }
  $client = new Google_Client();
  $client->setAuthConfig("oauth-credentials.json");
  $encryptedToken = hex2bin($entry["token"]);
  $parts = explode(IV_SEP, $encryptedToken);
  $decryptedToken = openssl_decrypt($parts[0], 'AES-256-CBC', OSSL_ENC_KEY, 0, $parts[1]);
  if(!$client->fetchAccessTokenWithRefreshToken($decryptedToken)){
    return false;
  }
  $service = new Google_Service_Drive($client);
  $response = $service->files->listFiles(array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
  ));
  $files = $response->files;
  $pwFileId = "";
  $idFileId = "";
  $userFileId = "";
  foreach($files as $f){
    if ($f->name == "user") {
      $userFileId = $f->id;
    }
  }
  $user = (array)json_decode($service->files->get($userFileId, array(
    'alt' => 'media' ))->getBody()->__toString());

  return $user;

}

function authorizeSession($db, $email=REQUIRE_EMAIL_CONFIRM){
  $redirect = $_SERVER['PHP_SELF'];
  $attempt = signInRaw($db, $_SESSION["sms"], $_SESSION["pass"], true);
  if(!$attempt){
    navigate('signin-form.php?redirect_uri='.$redirect);
  }
  elseif($attempt==1){
    navigate('oauth-form.php');
  }
  elseif (!is_array($attempt)) {
    navigate('confirm-sms-form.php');
  }
  if($email){
    if($attempt['email_confirm'] == false){
      return navigate('confirm-email.php');
    }
  }
  return $attempt;
}

function idFromRefreshToken($token){
  $client = new Google_Client();
  $client->setAuthConfig("oauth-credentials.json");
  $encryptedToken = hex2bin($token);
  $parts = explode(IV_SEP, $encryptedToken);
  $decryptedToken = openssl_decrypt($parts[0], 'AES-256-CBC', OSSL_ENC_KEY, 0, $parts[1]);
  if(!$client->fetchAccessTokenWithRefreshToken($decryptedToken)){
    return false;
  }
  $service = new Google_Service_Drive($client);
  $response = $service->files->listFiles(array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
  ));
  $files = $response->files;
  $idFileId = "";
  foreach($files as $f){
    if ($f->name == "id") {
      $idFileId = $f->id;
    }
  }
  return $service->files->get($idFileId, array(
      'alt' => 'media' ))->getBody()->__toString();
}

function idFromClient($client){
  $service = new Google_Service_Drive($client);
  $response = $service->files->listFiles(array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
  ));
  $files = $response->files;
  $idFileId = "";
  foreach($files as $f){
    if ($f->name == "id") {
      $idFileId = $f->id;
    }
  }
  return $service->files->get($idFileId, array(
      'alt' => 'media' ))->getBody()->__toString();
}

function saveUser($user){
  $token = $user["_token"];
  $client = new Google_Client();
  $client->setAuthConfig("oauth-credentials.json");
  if(!$client->fetchAccessTokenWithRefreshToken($token)){
    return false;
  }
  $service = new Google_Service_Drive($client);
  $response = $service->files->listFiles(array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
  ));
  $files = $response->files;
  $userFileId = "";
  foreach($files as $f){
    if ($f->name == "user") {
      $userFileId = $f->id;
    }
  }
  $service->files->delete($userFileId);
  $fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => 'user',
    'parents' => array('appDataFolder')
  ));
  $file = $service->files->create($fileMetadata, array(
    'data' => json_encode($user),
    'mimeType' => 'text/plain',
    'uploadType' => 'multipart',
    'fields' => 'id'));

}

function initCSRF(){
  $bytes = bin2hex(openssl_random_pseudo_bytes(16));
  $_SESSION["csrf"] = $bytes;
  return "<input type='hidden' name='csrf' value='$bytes'>";
}
function verifyCSRF(){
  if($_POST['csrf'] == $_SESSION['csrf']){
    unset($_SESSION['csrf']);
    return true;
  }
  navigate('/');
}

// Does string contain letters?
function has_letters( $string ) {
    return preg_match( '/[a-zA-Z]/', $string );
}
// Does string contain numbers?
function has_numbers( $string ) {
    return preg_match( '/\d/', $string );
}
// Does string contain special characters?
function has_special_chars( $string ) {
    return preg_match('/[^a-zA-Z\d]/', $string);
}

function verifyEmail($email){
  return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function verifySMS($cc, $sms){
  $number = preg_replace('/[a-zA-Z]/', '', $cc.$sms);
  if (strlen($number) > 10 && strlen($number) < 16) {
    return '+'.$number;
  }
  else{
    return false;
  }
}
function verifyPass($pass){
  return has_letters($pass) && has_numbers($pass) && has_special_chars($pass) && strlen($pass) > 5;
}
?>
