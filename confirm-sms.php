<?php
require 'login-engine.php';
if (signInRaw($db, $_SESSION["sms"], $_SESSION["pass"]) == $_POST["verify"]){
  $statement = $db->prepare("UPDATE users SET verify = '_' where sms = :em");
  $statement->execute(array(':em' => $_SESSION["sms"]));
  header('Location: secure-page.php');
}
else{
  header('Location: confirm-sms-form.php?e');
}
 ?>
