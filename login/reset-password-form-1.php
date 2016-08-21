<?php
  if(isset($_GET["e"])){
    echo "Couldn't find account";
  }
 ?>
  <form method=POST action="reset-password-form-2.php">
  <input name="sms" placeholder="sms">
  <input type="submit">
</form>
