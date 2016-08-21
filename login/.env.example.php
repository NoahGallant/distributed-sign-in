<?php
define('HOME', "https://gallant.io");
define('HOME_SECURE', "https://gallant.io/login/secure-page.php");
define('APP_NAME',  "Noah's App");

define('USER_FEATURES', ['name']);
define('USER_HIDDEN_FEATURES', ['email','email_confirm', 'email_confirm_code']);
define('REQUIRE_EMAIL_CONFIRM', true);

define('DB_HOST', "ec2-00-00-000-0.compute-1.amazonaws.com");
define('DB_NAME',  "database_name");
define('DB_USER',  "database_user");
define('DB_PASSWORD',  "databasePASSWORD123");

define('OSSL_ENC_KEY', "QqX94yJ5f6dkgxona3EtLu3fHbmUrZqF8kJKKKDDGOP5N9K75Wi1vnGkxnMKQv14"); //example-key CHANGE
define('IV_SEP',  ":");
define('ENC_ITERS',  1024);

define('RECAP_KEY',  "6LdJCSYTAAAAAOxYdxfVKo06Xjeapk8UC-n-PWRd"); //example-key CHANGE
define('RECAP_SECRET_KEY',  "6LdJCSYTAAAAAGWzTM5M5zEFKxyG_5wgAJRom06N"); //example-key CHANGE

define('MAILGUN_USER', 'postmaster@sandbox123456789.mailgun.org'); // create your own mailgun at mailgun.com
define('MAILGUN_PASS', '12345');

define('ZAPIER_HOOK_URL',  "https://hooks.zapier.com/hooks/catch/sms/hook/url/"); // go to zapier.com to make your own
?>
