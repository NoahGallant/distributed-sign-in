<?php
require 'login-engine.php';
verifyCSRF();

if(!isset($_POST["sms"]) || !isset($_POST["pass"])){
  navigate('signin-form.php?e');
}

$attempt = signInPost($db);

if(is_array($attempt)){
  $_SESSION["sms"] = $_POST["sms"];
  $redirect = "";
  if (isset($_GET["redirect"]))
  {
    $r = $_GET["redirect"];
    header("Location: $r");
  }
  else{
    header("Location: " . HOME_SECURE);
  }
}
else{
  header('Location: signin-form.php?e');
}
?>
