<?php
require 'login-engine.php';
if (signInRaw($db, $_SESSION["sms"], $_SESSION["pass"], true) == $_POST["verify"]){
  $statement = $db->prepare("UPDATE users SET verify = '_' where sms = :em");
  $statement->execute(array(':em' => $_SESSION["sms"]));
  navigate('secure-page.php');
}
else{
  navigate('confirm-sms-form.php?e');
}
 ?>
