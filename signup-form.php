
<?php
  require 'login-engine.php';
  $error = "";
  if(isset($_GET["e"])){
    $e = $_GET["e"];
    if ($e == "robot"){
      $error = "You seem to be a robot...";
    }
    elseif ($e == "pass") {
      $error = "Passwords do not match.";
    }
    elseif ($e == "sms"){
      $error = "smss do not match.";
    }
    elseif ($e == "taken"){
      $error = "sms is already in use.";
    }
  }
 ?>

<script src='https://www.google.com/recaptcha/api.js'></script>
<form method="POST" action="signup.php">
  <span class="error"><?=$error?></span>
  <input type="sms" name="sms" placeholder="sms >=11 digits">
  <input type="sms" name="sms-confirm" placeholder="confirm sms">
  <input type="password" name="pass" placeholder="password">
  <input type="password" name="pass-confirm" placeholder="confirm password">
  <div class="g-recaptcha" data-sitekey="<?=$RECAP_KEY?>"></div>
  <input type="submit" value="Sign Up!" />
</form>
