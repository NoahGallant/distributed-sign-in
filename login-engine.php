 <?php
require 'vendor/autoload.php';
require '.env.php';
require 'base.php';

session_start();

$dbh = "pgsql:host=$DB_HOST;port=5432;dbname=$DB_NAME;user=$DB_USER;password=$DB_PASSWORD";
$db = new PDO($dbh);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

//FOR DEVELOPMENT
//==========
//ini_set('display_errors', 1);
//==========

//FOR PRODUCTION
//==========
ini_set('display_errors', 1);
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

function signInPost($pdo){
  $sms = $_POST["sms"];
  $salt = openssl_random_pseudo_bytes(16);
  $hash = hash_pbkdf2("sha256", $_POST["pass"], $salt, $iterations, 20);
  return signInRaw($pdo, $sms, $hash);
}

function signInSession($pdo){
  $sms = $_SESSION["sms"];
  $pass = $_SESSION["pass"];
  return signInRaw($pdo, $sms, $pass);
}
function signInRaw($pdo, $sms, $pass){
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
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  $client = new Google_Client();
  $client->setAuthConfig("oauth-credentials.json");
  $encryptedToken = hex2bin($entry["token"]);
  $parts = explode(':', $encryptedToken);
  $OSSL_ENC_KEY = "QqX94yJ5f6dkgxona3EtLu3fHbmUrZqF8kJKKKDDGOP5N9K75Wi1vnGkxnMKQv14";
  $decryptedToken = openssl_decrypt($parts[0], 'AES-256-CBC', $OSSL_ENC_KEY, 0, $parts[1]);

  if(!$client->fetchAccessTokenWithRefreshToken($decryptedToken)){
    return false;
  }
  $service = new Google_Service_Drive($client);
  $response = $service->files->listFiles(array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
  ));
  $file = $response->files[0];
  if(hash_equals($file->name, $pass)){
    return $entry["verify"];
  }else{
    return false;
  }
}
function authorizeSession($db){
  $attempt = signInSession($db);
  if(!$attempt){
    return header('Location: signup-form.php');
  }
  elseif($attempt==1){
    return heaer('Location: oauth-form.php');
  }
  elseif ($attempt != "_") {
    return header('Location: confirm-sms-form.php');
  }
  $statement = $db->prepare("SELECT id from users WHERE sms = :em");
  $statement->execute(array(':em' => $_SESSION["sms"]));
  $returns = $statement->fetchAll();
  $userArray =  (array)$returns[0];
  return $userArray["id"];
}
?>
