<?php
require 'login-engine.php';
$user = authorizeSession($db);
 ?>
<form method="POST" action="change-sms.php">
  <input type="text" name="sms" value="<?=$user['sms']?>">
  <input type="text" name="sms-confirm" placeholder="Confirm new SMS">
  <input type="submit">
</form>
<form method="POST" action="change-email.php">
  <input type="text" name="email" value="<?=$user['email']?>">
  <input type="text" name="email-confirm" placeholder="Confirm new email.">
  <input type="submit">
</form>
<form method="POST" action="update-user.php">
  <?php
  foreach(USER_FEATURES as $feature){
    $userfeat = $user[$feature];
    echo "<input type='text' name='$feature' placeholder='$feature' value='$userfeat']'>";
  }
  ?>
  <input type="submit">
</form>
<br>
<a href="reset-password-form-1.php">Reset password</a>
