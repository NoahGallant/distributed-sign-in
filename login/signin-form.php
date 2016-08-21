<form method="POST" action="signin.php">
<?php
  require 'login-engine.php';
  $error = "";
  if(isset($_GET["e"])){
    $error = "Bad username or password.";
  }
  elseif (isset($_GET["r"])) {
    $error = "Password successfully reset. Please login.";
  }

  if(isset($_GET["redirect_uri"])){
    $redirect = $_GET["redirect_uri"];
    echo "<input type='hidden' name='redirect' value='$redirect'>";
  }
 ?>
  <span class="error"><?=$error?></span>
  <input type="sms" name="sms" placeholder="sms">
  <input type="password" name="pass" placeholder="password">
  <?=initCSRF()?>
  <input type="submit" value="Sign In">
</form>
