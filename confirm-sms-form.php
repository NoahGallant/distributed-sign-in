<?php
require 'login-engine.php';
$siResponse = signInRaw($db, $_SESSION["sms"], $_SESSION["pass"]);
$error = isset($_GET["e"]) ? "Incorrect code" : "";
if($siResponse && $siResponse != '_'){
  ?>
  <form method="POST" action="confirm-sms.php">
    <span class="error"><?=$error?></span>
    <input type="text" name="verify" placeholder="Verify token from sms">
    <input type="submit" value="Confirm!" />
  </form>
  <?php
}
else{
  header('Location: signup-form.php');
}
?>
