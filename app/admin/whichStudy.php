<?php
namespace EdwardsEyes\admin;

use EdwardsEyes\inc\database;

$userId = intval($_SESSION['userinfo']['id']);
$accessLevel = intval($_SESSION['userinfo']['access']);

$connect = new database();
$userData = $connect->getUser($userId, $accessLevel);
$studyData = $connect->getStudiesFor($userId);
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
                <h1>Select a study to participate in</h1>
                <ul id="dashboardActions">
                <?php
                foreach ($studyData as $studyId => $data) {
                    if ($studyId != 0 && $data['running'] == 'Y') {?>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/categorize.php?id=<?php echo $studyId;
                    ?>"><?php echo $data['studyname'];?></a>(<?php
                    echo (isset($data['plotted']))?$data['plotted']:'0';
                    ?>/<?php
                    echo (isset($data['pool']))?$data['pool']:'0';
                    ?>)</li>
                <?php
                    }
                }
                ?>
                    <li><a href="<?php echo ROOT_FOLDER; ?>/categorize.php?id=0">Sample Study</a> (Practice)</li>
                </ul>
            </div>
        </div>
    </body>
</html>
