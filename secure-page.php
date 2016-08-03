<?php
  require 'login-engine.php';
  $userId = authorizeSession($db);
  echo "Hello! User #$userId";

 ?>
