<?php
  session_unset();
  $r = '';
  if(isset($_GET["r"])){
    $r = "?r";
  }
  header("Location: signin-form.php$r");

 ?>
