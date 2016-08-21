<?php
require 'login-engine.php';
$siResponse = signInRaw($db, $_SESSION["sms"], $_SESSION["pass"], true);
$error = isset($_GET["e"]) ? "Incorrect code" : "";
if($siResponse && !is_array($siResponse)){
  ?>
  <form method="POST" action="confirm-sms.php">
    <span class="error"><?=$error?></span>
    <input type="text" name="verify" placeholder="Verify token from sms">
    <input type="submit" value="Confirm!" />
  </form>
  <a href="send-sms-confirm.php?sms=<?=urlencode($_SESSION['sms'])?>">Resend sms.</a>
  <?php
}
else{
  navigate('signup-form.php');
}
?>
