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
if (!empty($_GET['user']) && intval($_GET['user']) > 0) {
    $userId = intval($_GET['user']);
}
$data = $connect->getColourData($studyId, null);

$dpi = 4;
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
                <h1>Graphing Tool - Blank</h1>
<?php } elseif (empty($studies[$studyId]['studyname'])) { ?>
                <h1>Graphing Tool - Study #<?php echo $studyId; ?></h1>
<?php } else { ?>
                <h1>Graphing Tool - <?php echo $studies[$studyId]['studyname']; ?></h1>
<?php }

include_once SERVER_ROOT . '/inc/messages.php';

if (!empty($userInfo['username'])) {
?>
                <h2>Data By: <?php echo $userInfo['username'];?></h2>
<?php
}

?>


                <div>
                <ul id="dashboardActions">
                    <li><a href="graph2d.php">2D Graphs</a></li>
                    <li><a href="graph3d.php">3D Graph</a></li>
                </ul>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </body>
</html>
