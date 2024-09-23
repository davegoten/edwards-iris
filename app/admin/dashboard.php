<?php
namespace EdwardsEyes\admin;


$accessLevel = intval($_SESSION['userinfo']['access']);

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
                <?php include_once SERVER_ROOT . '/inc/nav.php'?>
            <h1>Dashboard Overview</h1>
                <ul id="dashboardActions">
                <?php if ($accessLevel >= 20) { // Developer ?>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/">Manage Studies</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/users/">Manage Coordinators &amp; Participants</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/users/self.php">Manage Account</a></li>
                <?php }?>
                <?php if ($accessLevel >= 10 && $accessLevel < 20) { // Coordinator ?>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/">Manage my studies</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/users/">Manage Participants</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/users/self.php">Manage Account</a></li>
                <?php }?>
                <?php if ($accessLevel >= 0) { // Participant ?>
                    <li class="last-child"><a href="<?php echo ROOT_FOLDER; ?>/admin/whichStudy.php">Participate in study</a></li>
                <?php }?>
                </ul>
            </div>
        </div>
    </body>
</html>
