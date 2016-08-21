<?php
require 'login-engine.php';
$sms = $_POST["From"];
$code = $_POST["Body"];
$statement = $db->prepare("SELECT id, reset, token from users WHERE sms = :em");
$statement->execute(array(':em' => $sms));
$returns = $statement->fetchAll();
if(count($returns)>0){
  $entry =  (array)$returns[0];
  $resetting = $entry['reset'];
  if($resetting == "true"){
      if(verifyPass($code)){
        $client = new Google_Client();
        $client->setAuthConfig("oauth-credentials.json");

        $encryptedToken = hex2bin($entry["token"]);
        $parts = explode(IV_SEP, $encryptedToken);
        $decryptedToken = openssl_decrypt($parts[0], 'AES-256-CBC', OSSL_ENC_KEY, 0, $parts[1]);

        if(!$client->fetchAccessTokenWithRefreshToken($decryptedToken)){
          $body = "A problem occurred with your Google account.";
        }
        else{
          $service = new Google_Service_Drive($client);
          $response = $service->files->listFiles(array(
            'spaces' => 'appDataFolder',
            'fields' => 'nextPageToken, files(id, name)',
            'pageSize' => 10
          ));

          $newPassId = "";
          $keyId = "";
          $passId = "";

          foreach($response as $r){
            if($r->name == "rs-pass"){
              $newPassId = $r->id;
            }
            if($r->name == "rs-key"){
              $keyId = $r->id;
            }
            if($r->name == "pw"){
              $passId = $r->id;
            }
          }

          if($keyId == "" || $newPassId==""){
            $body = "Please go through the password reset prompt online to enable password reset.";
          }
          else{
            $resetKey = $service->files->get($keyId, array(
                'alt' => 'media' ))->getBody()->__toString();
            if($resetKey != $code){
              $body = "Codes for reset do not match.";
            }
            else{
              $service->files->delete($passId);
              $service = new Google_Service_Drive($client);
              $newPassVal = $service->files->get($newPassId, array(
                  'alt' => 'media' ))->getBody()->__toString();
                  
              $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => 'pw',
                'parents' => array('appDataFolder')
              ));
              $file = $service->files->create($fileMetadata, array(
                'data' => $newPassVal,
                'mimeType' => 'text/plain',
                'uploadType' => 'multipart',
                'fields' => 'id'));

              $service->files->delete($keyId);
              $service->files->delete($newPassId);
              $statement = $db->prepare("UPDATE users SET reset = 'false' where sms = :em");
              $statement->execute(array(':em' => $_POST["From"]));
              $body = "Looks good! Click 'All Set' in your browser.";
            }
          }
        }
      }
      else{
        $body = "Please use a more secure password.";
      }
  }
  else{
    $body = "Account status: OK";
  }
}
else{
  $body = "No known account with this number.";
}
?>
<?xml version="1.0" encoding="UTF-8"?>
<Response>
    <Message>
        <Body><?=$body?></Body>
    </Message>
</Response>
