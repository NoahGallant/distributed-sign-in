
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
    elseif ($e == "sv") {
      $error = "sms is invalid.";
    }
    elseif ($e == "ss") {
      $error = "problem sending sms.";
    }
    elseif ($e == "pv") {
      $error = "password does not meet criteria.";
    }
    elseif ($e == "ev") {
      $error = "email is invalid.";
    }
  }
 ?>

<script src='https://www.google.com/recaptcha/api.js'></script>
<form method="POST" action="signup.php">
  <span class="error"><?=$error?></span>
  +<input type="number" name="cc" placeholder="1"><br>
  <input type="number" name="sms" placeholder="sms (no dashes or parenthesis)">
  <input type="number" name="sms-confirm" placeholder="confirm sms"><br>
  <input type="password" name="pass" placeholder="password">
  <input type="password" name="pass-confirm" placeholder="confirm password"><br>
  <input type="email" name="email" placeholder="email">
  <?=initCSRF();?>
  <?php
    foreach(USER_FEATURES as $feature){
      echo "<input type='text' name='$feature' placeholder='$feature'>";
    }
  ?>
  <div class="g-recaptcha" data-sitekey="<?=RECAP_KEY?>"></div>
  <input type="submit" value="Sign Up!" />
</form>
