 <?php
  require 'login-engine.php';
  $user = authorizeSession($db);

  foreach(USER_FEATURES as $feature){
    if (!isset($_POST[$feature]) || $_POST[$feature] == ''){
      navigate('edit-profile-form.php?e=bad_input');
    }
  }

  foreach (USER_FEATURES as $feature) {
    $user[$feature] = $_POST[$feature];
  }

  saveUser($user);

  navigate(HOME_SECURE);

  ?>
