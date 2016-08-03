<?php
ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);
require 'login-engine.php';
if(signInPost($db)){
  $_SESSION["sms"] = $_POST["sms"];
  $iterations = 1000;
  $salt = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
  $_SESSION["pass"] = hash_pbkdf2("sha256", $_POST["pass"], $salt, $iterations, 20);
  header('Location: /');
}
else{
  header('Location: signin-form.php?e');
}
?>
