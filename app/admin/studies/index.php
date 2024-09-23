<?php
namespace EdwardsEyes\admin\studies;

use EdwardsEyes\inc\database;

$accessLevel = intval($_SESSION['userinfo']['access']);

if ($accessLevel < array_search('coordinator', ACL_RANKS)) {
    header('Location: ' . ROOT_FOLDER . '/admin/dashboard.php');
    exit();
}
$connect = new database();

$studyId = null;
$studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
foreach ($studies as $studyIdIdx => $studyDetails) {
    if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
        $studyId = $studyIdIdx;
    }
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
<?php if ($studyId === null) { ?>
                <h1>No specific study</h1>
<?php } elseif (empty($studies[$studyId]['studyname'])) { ?>
                <h1>Study #<?php echo $studyId; ?></h1>
<?php } else { ?>
                <h1><?php echo $studies[$studyId]['studyname']; ?></h1>
<?php }?>
<?php include_once SERVER_ROOT . '/inc/messages.php';?>
                <ul id="dashboardActions">

                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/reindex.php" />Reindex all new images</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/edit.php" />Edit Study Properties</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/colour.php" />Begin Processing All reviewed images for colour information</a></li>

                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/wedges.php" />View/Download Colour Wedges</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/viewImages.php" />View Uploaded Images</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/addImages.php" />Upload Images to your study</a></li>

                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/report.php" />Download Report</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/graph.php" />Graph Study Information</a></li>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/admin/studies/run.php" />Run a particular study</a></li>
                </ul>
            </div>
        </div>
    </body>
</html>
