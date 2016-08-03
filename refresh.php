<?php
require 'vendor/autoload.php';
require '.env.php';
require 'db.php'; /* gives valid PDO connection to table */
require 'base.php';

session_start();

$email = $_SESSION["email"];
$statement = $pdo->prepare("SELECT id, token, verify from users WHERE email = :em");
$statement->execute(array(':em' => $email));
$returns = $statement->fetchAll();

if(count($returns) != 1 || $verify != '_'){
  header('Location: signin.php');
}
$entry = (array)($return[0]);

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$client = new Google_Client();
$client->setAuthConfig("oauth-credentials.json");
if(!$client->fetchAccessTokenWithRefreshToken($entry["token"])){
  header('Location: signin.php');
}
$service = new Google_Service_Drive($client);
$response = $service->files->listFiles(array(
  'spaces' => 'appDataFolder',
  'fields' => 'nextPageToken, files(id, name)',
  'pageSize' => 10
));
$file = $response->files[0];
  //echo $file->name;
  //$service->files->delete($file->id);
echo hash_equals($file->name, $_POST["pass"]);
?>
