<?php
namespace EdwardsEyes\admin\studies;

use EdwardsEyes\inc\database;

$connect = new database();
$studyId = null;
$studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
foreach ($studies as $studyIdIdx => $studyDetails) {
    if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
        $studyId = $studyIdIdx;
    }
}
$userId = intval($_SESSION['userinfo']['id']);
$userData = $connect->getUser($userId);
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
<?php if ($studyId === null) { ?>
                <h1>Run Study</h1>
<?php } elseif (empty($studies[$studyId]['studyname'])) { ?>
                <h1>Run Study</h1><h2>Currently: Study #<?php echo $studyId; ?></h2>
<?php } else { ?>
                <h1>Run Study</h1><h2>Currently: <?php echo $studies[$studyId]['studyname']; ?></h2>
<?php }?>
<?php include_once SERVER_ROOT . '/inc/messages.php';?>

                <form action="<?php echo ROOT_FOLDER;?>/js/process.php" method="post" id="login">
                    <label for="studyId">Currently selected :</label>
                    <select name="studyId" id="studyId">
<?php
foreach ($studies as $studyDetails) {
    if ($studyDetails['coordinatorid'] == $_SESSION['userinfo']['id'] || $_SESSION['userinfo']['access'] > array_search('principle investigator', ACL_RANKS)) {
?>
                        <option value="<?php echo ($studyDetails['studyidnum'] == 'sample')?0:$studyDetails['studyidnum'];?>"<?php
                        echo ($studyDetails['studyidnum'] == $userData['running'])?' selected="selected"':'';
                        ?>><?php echo ($studyDetails['studyidnum'] == 'sample')?'Sample Study':$studyDetails['studyname']?></option>
<?php
    }
}
?>
                    </select>
                    <input type="submit" value="Save Changes" />
                    <input type="hidden" name="access" value="<?php echo $_SESSION['sessionKey']; ?>" />
                    <input type="hidden" name="action" value="updateUserStudy" />
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </body>
</html>
