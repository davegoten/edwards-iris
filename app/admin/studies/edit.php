<?php
namespace EdwardsEyes\admin\studies;

use EdwardsEyes\inc\database;

$accessLevel = intval($_SESSION['userinfo']['access']);

if ($accessLevel < array_search('coordinator', ACL_RANKS)) {
    header('Location: ' . ROOT_FOLDER . '/admin/dashboard.php');
    exit();
}
$connect = new database();
$data = array();

$studyId = null;
if (!empty($_GET['study']) && $_GET['study'] && $_SESSION['userinfo']['access'] >= array_search('principle investigator', ACL_RANKS)) {
    $studyId = intval($_GET['study']);
} else {
    $studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
    foreach ($studies as $studyIdIdx => $studyDetails) {
        if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
            $studyId = $studyIdIdx;
        }
    }
}
if ($studyId > 0) {
    $data = $connect->getStudyData($studyId);
    $data = end($data);
} else {
    $studyId = 'new';
    $data = array(
        'coordinatorid' => null,
        'username' => null,
        'studyid' => null,
        'studyname' => null,
        'additional_data' => null,
        'types' => null,
        'running' => null,
        'studyid' => $studyId,
        'curstudy' => null
    );
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
                <?php include_once SERVER_ROOT . '/inc/nav.php';?>
<?php if ($studyId === 'new') { ?>
                <h1>New study</h1>
<?php } elseif (empty($studies[$studyId]['studyname'])) { ?>
                <h1>Study #<?php echo $studyId; ?></h1>
<?php } else { ?>
                <h1><?php echo $studies[$studyId]['studyname']; ?></h1>
<?php }?>
<?php include_once SERVER_ROOT . '/inc/messages.php';?>
                <form action="<?php echo ROOT_FOLDER;?>/js/process.php" method="post" id="login">
<?php if (!empty($_SESSION['userinfo']['access']) && $_SESSION['userinfo']['access'] >= array_search('principle investigator', ACL_RANKS)) {
?>
                    <input type="button" value="Create New Study" onclick="window.location = 'edit.php?study=new'"/>
<?php }?>
                    <label class="required">Belongs to :</label>
<?php
$users = $connect->getUsersByRank('coordinator');
if (!empty($users) && !empty($_SESSION['userinfo']['access']) && $_SESSION['userinfo']['access'] >= array_search('principle investigator', ACL_RANKS)) {
?>

                    <select id="coordinator" name="coordinator">
<?php foreach ($users as $uid => $uname) {?>
                        <option value="<?php echo $uid?>"<?php echo ($data['coordinatorid'] == $uid)?' selected="seleceted"':'';?>><?php echo ucwords($uname); ?></option>
<?php } ?>
                    </select>
<?php } else {?>
                    <span><?php echo ucwords($data['username']); ?></span>
<?php } ?>
                    <label for="studyname" class="required">Study Name :</label>
                    <input id="studyname" name="studyname" type="text" value="<?php echo $data['studyname']?>" placeholder="Study Name" />

                    <label for="ethnicities">Ethnicities :</label>
                    <input id="ethnicities" name="ethnicities" type="text" value="<?php echo $data['types']?>" placeholder="Ethnicities" />

                    <label for="database">Additional Info in :</label>
                    <span><?php echo $data['additional_data']?>
                    <!--input type="button" value="Add" class="innerButton" onclick="alert('Feature coming soon'); return false;"-->
                    </span>

                    <label for="running">Yes :
                        <input id="running" name="running" type="checkbox" value="Y" <?php echo ($data['running'] == 'Y')?'checked="checked"':'';?> />
                    </label><span>This study can be run.<?php
                    if ($data['curstudy'] == $data['studyid']) {
                        echo '<br />It is also my currently running study';
                    }
                    ?></span>

                    <label for="eye_side">Eyes are on :</label>
                    <select id="eye_side" name="eye_side" type="text"><?php
                    foreach (['right', 'left'] as $side) {?>
                        <option value="<?php echo $side;?>"<?php
                        if (!empty($studies[$studyId]['eye_side']) && $studies[$studyId]['eye_side'] == $side) {
                            echo ' selected="selected"';
                        }
                        ?>><?php echo ucwords($side);?> side of participant's face</option>
                    <?php
                        }
                    ?></select>


                    <input type="submit" value="Save Changes" />
                    <input type="hidden" name="studyid" value="<?php echo $data['studyid']; ?>" />
                    <input type="hidden" name="access" value="<?php echo $_SESSION['sessionKey']; ?>" />
                    <input type="hidden" name="action" value="updatestudy" />
                    <div class="clearfix"></div>
                </form>

            </div>
        </div>
    </body>
</html>
