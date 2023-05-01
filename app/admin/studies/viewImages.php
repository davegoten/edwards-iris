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
$userId = intval($_SESSION['userinfo']['id']);
if (!empty($_GET['user']) && intval($_GET['user']) > 0) {
    $userId = intval($_GET['user']);
}
$imagePath = dirname(SERVER_ROOT) . '/html/images/studies/structures/' . $studyId . '/';


$pageNum = 1;
$perpage = 12;
if (!empty($_GET['page']) && intval($_GET['page']) > 0) {
    $pageNum = intval($_GET['page']);
}
if (!empty($_GET['per']) && intval($_GET['per']) > 0) {
    $perpage = intval($_GET['per']);
}

$files = array_merge(
    glob(
        $imagePath .
        '*.[pP][nN][gG]',
    ),
    glob(
        $imagePath .
        '*.[jJ][pP][eE][gG]',
    ),
    glob(
        $imagePath .
        '*.[jJ][pP][gG]',
    ),
    glob(
        $imagePath .
        '*.[gG][iI][fF]',
    ),
    glob(
        $imagePath .
        '*.[tT][iI][fF]',
    ),
    glob(
        $imagePath .
        '*.[tT][iI][fF][fF]',
    ),
);

$pages = ceil(count($files) / $perpage);
$pageFiles = array_slice($files, ($pageNum-1) * $perpage, $perpage);
$enabled = $connect->getEnabledByFilename($studyId, array_map('basename', $pageFiles));
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
<style>
    .iris {
        display: block;
        float: left;
        height: 200px;
        width: 20%;
        margin-right: 5px;
        margin-bottom: 5px;
        border: 1px #000000 solid;
        overflow: auto;
    }
    .iris img {
        height: auto;
        max-width: 100%;
    }
    .iris div.title {
        text-align: center;
        font-weight: bold;
        background-color: #EEEEEE;
        border-bottom: 1px #000000 solid;
    }
</style>
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

        <h2>Page <?php echo $pageNum;?> of <?php echo $pages;?></h2>
        <div><?php foreach ($pageFiles as $image) {
            $niceName = preg_replace('/\s/','-', $image);
            ?>
                <div class="iris">
                    <div class="title">
                        <?php echo basename($image);?>
                    </div>
                    <img src="<?php echo ROOT_FOLDER;?>/images/studies/structures/<?php echo $studyId?>/<?php
                    echo basename($image);?>" alt="Iris - <?php
                    echo basename($image);?>" />
                    <?php if (!empty($enabled[basename($image)])) {?>
                    <label for="enabled-<?php echo $niceName;?>">
                        Enabled
                        <input id="enabled-<?php echo $niceName;?>" type="checkbox"<?php
                        if ($enabled[basename($image)] == 'Y') {
                            echo ' checked="checked"';
                        }
                        ?> onchange="return $.ajax({
                                type: 'POST',
                                url: '<?php echo ROOT_FOLDER;?>/js/process.php',
                                data: {
                                    access: '<?php echo $_SESSION['sessionKey']; ?>',
                                    action: 'enableiris',
                                    filename: '<?php echo basename($image);?>',
                                    value: $(this).is(':checked')
                                },
                                dataType   : 'json',
                                success: function (data) {
                                    if(data.success > 0) {
                                        return true;
                                    } else {
                                        alert('Could Not authenticate');
                                        return false;
                                    }
                                }
                            });" />
                    </label>
                    <?php } else { ?>
                        Not Indexed
                    <?php }?>
                    <div class="clearfix"></div>
                </div>
            <?php } ?>

            <div class="clearfix"></div>
            <div>
                <label for="page">View Page:
                    <select id="page" onchange="window.location = window.location.origin + window.location.pathname + '?page=' + $(this).val()">
                        <?php for ($i = 1; $i <= $pages; $i++) {?>
                            <option value="<?php echo $i; ?>"<?php
                            if ($i == $pageNum) { ?> selected="selected"<?php } ?>>
                                <?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </label>
            </div>
            <div style="text-align: center">
                <?php for ($i = 1; $i <= $pages; $i++) {?>
                    <a href="viewImages.php?page=<?php echo $i; ?>"><?php
                    if ($i == $pageNum) { echo "<b>{$i}</b>"; } else { echo $i; } ?></a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>