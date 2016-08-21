<?php
  require 'login-engine.php';
  if(!isset($_SESSION["reset-id-confirm"])){
    navigate('reset-password-form-2.php?e');
  }
  if(isset($_GET["e"])){
    echo "Bad password.";
  }
 ?>
<form method=POST action="reset-password.php">
  <input name="pass" placeholder="New Password">
  <input name="pass-val" placeholder="Confirm Password">
  <input type="submit">
</form>
