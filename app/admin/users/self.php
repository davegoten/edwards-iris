<?php
namespace EdwardsEyes\admin\users;

use EdwardsEyes\inc\database;

$userId = intval($_SESSION['userinfo']['id']);
$accessLevel = intval($_SESSION['userinfo']['access']);

if (!empty($_GET['user']) && $_GET['user'] && $accessLevel >= array_search('principle investigator', ACL_RANKS)) {
    $userId = intval($_GET['user']);
} else {
    $userId = intval($_SESSION['userinfo']['id']);
}

$connect = new database();
$userData = $connect->getUser($userId, $accessLevel);


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
                <?php include_once SERVER_ROOT . '/inc/nav.php';?>
                <h1>Welcome <?php echo ucwords($userData['username']); ?></h1>
                <?php include_once SERVER_ROOT . '/inc/messages.php';?>
                <form action="<?php echo ROOT_FOLDER;?>/js/process.php" method="post" id="login">
                    <label>Access Level :</label><span><?php echo ucwords(ACL_RANKS[$userData['access']]); ?></span>
                    <label for="user" class="required">Username :</label><input id="user" name="user" type="text" value="<?php echo $userData['username']?>" placeholder="Username" />
                    <label for="oldpass" class="required">Current Password :</label><input id="oldpass" name="oldpass" type="password" value="" placeholder="Current Password" />
                    <label for="pass">Change Password :</label><input id="pass" name="pass" type="password" value="" placeholder="Password" />
                    <label for="pass2">Repeat Password :</label><input id="pass2" name="pass2" type="password" value="" placeholder="Password Again" />
                    <label for="email">Email :</label><input id="email" name="email" type="email" value="<?php echo $userData['email']?>" placeholder="Email" />
                    <label for="contactable">Yes :
                        <input id="contactable" name="contactable" type="checkbox" value="Y" <?php echo ($userData['contactable'] == 'Y')?'checked="checked"':'';?> />
                    </label><span>I wish to be contacted for similar future studies</span>
                    <input type="submit" value="Save Changes" />
                    <input type="hidden" name="access" value="<?php echo $_SESSION['sessionKey']; ?>" />
                    <input type="hidden" name="uid" value="<?php echo $userId; ?>" />
                    <input type="hidden" name="action" value="updateuser" />
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </body>
</html>
