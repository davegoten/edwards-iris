<?php
namespace EdwardsEyes;


unset($_SESSION['sessionKey']);
unset($_SESSION['userinfo']);
session_unset();
session_destroy();
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo SITE_TITLE; ?></title>
        <link rel="stylesheet" href="<?php echo ROOT_FOLDER;?>/css/style.php" type="text/css" />
        <script src="<?php echo ROOT_FOLDER;?>/js/jquery.min.js"></script>
        <script src="<?php echo ROOT_FOLDER;?>/js/script.php"></script>
<?php
include_once SERVER_ROOT . '/inc/analytics.php';
?>
    </head>
    <body>
        <div class="wrapper">
            <div class="content">
                <h1>Logout</h1>
                <p>Thank you!</p>
                <p>You have successfully Logged out</p>
                <p><a href="<?php echo ROOT_FOLDER; ?>/login">Login again</a></p>
                <p><a href="<?php echo ROOT_FOLDER; ?>">Return to main</a></p>
            </div>
        </div>
    </body>
</html>
