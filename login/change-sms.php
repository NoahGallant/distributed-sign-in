<?php
  require 'login-engine.php';
  $user = authorizeSession($db);
  if(!isset($_POST["sms"]) && !isset($_POST["sms-confirm"])){
    navigate(HOME);
  }
  $sms = verifySMS('', $_POST["sms"]);
  if(!$sms){
    navigate('edit-profile-form.php?e=sms_valid');
  }
  $sms_confirm = $_POST["sms-confirm"];
  if($_POST["sms"] != $sms_confirm){
    navigate('edit-profile-form.php?e=sms_match');
  }

  $statement = $db->prepare("SELECT id from users WHERE sms = :em");
  $statement->execute(array(':em' => $sms));
  $returns = $statement->fetchAll();
  if(count($returns) != 0){
    navigate('edit-profile-form.php?e=sms_taken');
  }

  $verify = bin2hex(openssl_random_pseudo_bytes(3));
  $statement = $db->prepare("UPDATE users SET (sms, verify) = (:new, '$verify') where sms = :old");
  $statement->execute(array(':new' => $sms, ':old'=> $user['sms']));

  $send = sendSMSConfirm($db, $sms);
  if(!$send){
    $statement = $db->prepare("DELETE from users where sms = :em");
    $statement->execute(array(':em' => $sms));
    navigate('edit-profile-form.php?e=sms_valid');
  }

    $_SESSION["sms"] = $sms;

  navigate('confirm-sms-form.php');

?>
