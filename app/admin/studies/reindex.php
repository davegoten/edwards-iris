<?php
namespace EdwardsEyes\admin\studies;

use EdwardsEyes\inc\database;
use EdwardsEyes\isItAnImage;


$accessLevel = intval($_SESSION['userinfo']['access']);

if ($accessLevel < array_search('coordinator', ACL_RANKS)) {
    header('Location: ' . ROOT_FOLDER . '/admin/dashboard.php');
    exit();
}

$connect = new database();
$isItAnImage = new isItAnImage('');
$studyId = null;
$studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
foreach ($studies as $studyIdIdx => $studyDetails) {
    if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
        $studyId = $studyIdIdx;
    }
}

if (empty($studyId) || intval($studyId) == 0) {
    $studyId = 'sample';
}


$imagesReindexed = 0;

$imagePath = dirname(SERVER_ROOT) . '/html/images/studies/';
$current = 'structures/';
$masterList = array();
$colourList = array();
$imagesIndexed = 0;

if (is_readable($imagePath . $current . $studyId)) {
    $path = $imagePath . $current . $studyId;
    $isItAnImage->setPath($path);
    $masterList = scandir($path);
    $masterList = array_filter($masterList, array($isItAnImage, "test"));
}
$imagesInStudy = count($masterList);

$current = 'colours/';
if (is_readable($imagePath . $current . $studyId)) {
    $path = $imagePath . $current . $studyId;
    $isItAnImage->setPath($path);
    $colourList = scandir($path);
    $colourList = array_filter($masterList, array($isItAnImage, "test"));
    foreach ($masterList as $exists) {
        if (!empty($exists['filename']) && in_array($exists['filename'], $colourList)) {
            unset($colourList[array_search($exists['filename'], $colourList)]);
        }
    }
}
$imagesInColour = count($colourList);

if ($imagesInStudy > 0) {
    $res = $connect->getStudy($studyId, $accessLevel);
    $imagesIndexed = count($res);
    foreach ($res as $exists) {
        if (in_array($exists['filename'], $masterList)) {
            unset($masterList[array_search($exists['filename'], $masterList)]);
        }
    }
}


if (!empty($masterList)) {
    $imagesReindexed = $connect->indexStudy($studyId, $masterList);
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
<?php if ($studyId === 'sample') { ?>
                <h1>Reindexing Completed</h1>
<?php } elseif (empty($studies[$studyId]['studyname'])) { ?>
                <h1>Reindexing Completed - Study #<?php echo $studyId; ?></h1>
<?php } else { ?>
                <h1>Reindexing Completed - <?php echo $studies[$studyId]['studyname']; ?></h1>
<?php }?>
<?php include_once SERVER_ROOT . '/inc/messages.php';?>
                <table>
                    <tr>
                        <th>#</th>
                        <th>Statistic</th>
                        <th>Number</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>Number of Images found</td>
                        <td><?php echo $imagesInStudy; ?></td>
                    </tr><?php /*
                    <tr>
                        <td>2</td>
                        <td>Number of Images found with colour data</td>
                        <td><?php echo $imagesInColour; ?></td>
                    </tr>*/?>
                    <tr>
                        <td>2</td>
                        <td>Number of Images Already Indexed</td>
                        <td><?php echo $imagesIndexed; ?></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Number of Images Added to the study</td>
                        <td><?php echo ($imagesReindexed)?($imagesInStudy-$imagesIndexed):0; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
