<?php
  require 'login-engine.php';
  $user = authorizeSession($db);
  if(!isset($_POST["email"]) && !isset($_POST["email-confirm"])){
    navigate(HOME);
  }
  $email = $_POST["email"];
  $email_confirm = $_POST["email-confirm"];
  if($email != $email_confirm){
    navigate('edit-profile-form.php?e=email_match');
  }

  if(!verifyEmail($email)){
    navigate('edit-profile-form.php?e=email_verify');
  }

  if($user["email"] == $email){
    navigate('edit-profile-form.php?e=email_same');
  }

  $user["email"] = $email;
  $user["email_confirm"] = false;

  saveUser($user);

  navigate('confirm-email.php');

?>
