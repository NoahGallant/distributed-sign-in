<?php
  if(!isset($_GET["sms"]) && !isset($_GET["bits"])){
    navigate('/');
  }

  require 'login-engine.php';

  $sms = $_GET["sms"];
  $bits = $_GET["bits"];

  $user = getUser($db, $sms);
  if($bits == $user["email_confirm_code"]){
    $user["email_confirm"] = true;
    $user["email_confirm_code"] = "_";
    saveUser($user);
    navigate(HOME_SECURE);
  }
  else{
    echo "Error confirming email! :(";
  }


 ?>
