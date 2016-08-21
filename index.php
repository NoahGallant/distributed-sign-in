<?php
require 'login-engine.php';
if(!verifySMS("1", "2035305217")){
  echo "f";
}
echo 'Welcome to '.APP_NAME;
?>
<br>
<a href="/login/signup-form.php">Sign up</a>
 or
<a href="/login/signin-form.php">Sign in</a>
