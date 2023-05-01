<?php
namespace EdwardsEyes\admin;

if (!empty($_SESSION['userinfo']['password'])) {
    header('location: ' . ROOT_FOLDER . '/admin/dashboard.php');
    exit();
}
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
                <h1>Setup Password</h1>
                <form action="<?php echo ROOT_FOLDER;?>/js/process.php" method="post" id="login" class="clearfix">
                    <label for="username">Password: </label>
                    <input type="password" value="" placeholder="Password" id="password" name="password" />

                    <label for="password">Repeat Password: </label>
                    <input type="password" value="" placeholder="Password Again" id="passwordck" name="passwordck" />
                    <input type="hidden" name="access" value="<?php echo $_SESSION['sessionKey']; ?>" />
                    <input type="submit" value="Create Password" name="action" />
                </form>
                <script>
                $(document).ready(function () {
                    // @todo: add input listeners to validate passwords and suggest stronger passwords
                });
                </script>
            </div>
        </div>
    </body>
</html>
