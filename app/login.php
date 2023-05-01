<?php
namespace EdwardsEyes;


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
                <h1>Login Area</h1>
                <?php include_once SERVER_ROOT . '/inc/messages.php';?>
                <form action="<?php echo ROOT_FOLDER;?>/js/process.php" method="post" id="login" class="clearfix">
                    <label for="username">Username: </label>
                    <input type="text" value="" placeholder="Username" id="username" name="username" />

                    <label for="password">Password: </label>
                    <input type="password" value="" placeholder="Password" id="password" name="password" />
                    <input type="hidden" name="access" value="<?php echo $_SESSION['sessionKey']; ?>" />
                    <input type="submit" value="Login" name="action" />
                </form>
            </div>
        </div>
    </body>
</html>
