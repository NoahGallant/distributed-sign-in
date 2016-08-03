<?php
  require 'login-engine.php';
  $error = "";
  if(isset($_GET["e"])){
    $error = "Bad username or password.";
  }
 ?>

<form method="POST" action="signin.php">
  <span class="error"><?=$error?></span>
  <input type="sms" name="sms" placeholder="sms">
  <input type="password" name="pass" placeholder="password">
  <input type="submit" value="Sign In">
</form>
