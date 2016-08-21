<?php
  require 'login-engine.php';
  if(!isset($_GET["sms"])){
    navigate('/');
  }
  $response = sendSMSConfirm($db, $_GET["sms"]);
  if($response){
    navigate('confirm-sms-form.php');
  }
  else {
    echo "Error sending confirm :(";
  }

?>
