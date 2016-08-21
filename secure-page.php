<?php
  require 'login-engine.php';
  $user = authorizeSession($db);
  $name = $user['name'];
  $email = $user['email'];
  echo "Hello! $name. Your email is $email.";
 ?>
 <br>
 <a href="/login/edit-profile-form.php">Edit profile</a>
 <a href="/login/logout.php">Log out</a>
 <a href="/login/reset-password-form-1.php">Change password</a>
