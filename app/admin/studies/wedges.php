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
$imagePath = dirname(SERVER_ROOT) . '/html/images/studies/colours/' . $studyId . '/';

$pageNum = 1;
$perpage = 12;
if (!empty($_GET['page']) && intval($_GET['page']) > 0) {
    $pageNum = intval($_GET['page']);
}
if (!empty($_GET['per']) && intval($_GET['per']) > 0) {
    $perpage = intval($_GET['per']);
}
$files = glob($imagePath . '*Inner.png');
$pages = ceil(count($files) / $perpage);
$pageFiles = array_slice($files, ($pageNum-1) * $perpage, $perpage);
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
            .irisWedges {
                display: block;
                float: left;
                height: 200px;
                width: 20%;
                margin-right: 5px;
                margin-bottom: 5px;
                border: 1px #000000 solid;
                overflow: auto;
            }
            .irisWedges img {
                max-height: 200px;
                width: auto;
            }
            .irisWedges div {
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
        <?php if (count($files) > 0) { ?>
        <h2>Page <?php echo $pageNum;?> of <?php echo $pages;?></h2>
        <div>
            <?php foreach ($pageFiles as $innerImage) { ?>
                <div class="irisWedges">
                    <div>
                        <?php echo preg_replace('/(-\d+)*-Inner\.png$/i', '', basename($innerImage))?>
                    </div>
                    <img src="<?php echo ROOT_FOLDER;?>/images/studies/colours/<?php echo $studyId?>/<?php
                    echo preg_replace('/Inner\.png$/i', 'Outer.png', basename($innerImage));
                    ?>" alt="Outer Wedge" />
                    <img src="<?php echo ROOT_FOLDER;?>/images/studies/colours/<?php echo $studyId?>/<?php
                    echo basename($innerImage);?>" alt="Inner Wedge" />
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
                
                <div style="text-align: center">
                    <?php for ($i = 1; $i <= $pages; $i++) {?>
                        <a href="wedges.php?page=<?php echo $i; ?>"><?php
                        if ($i == $pageNum) { echo "<b>{$i}</b>"; } else { echo $i; } ?></a>
                    <?php } ?>
                </div>
                <input type="button" onclick="$.ajax({
                    type: 'POST',
                    url: '<?php echo ROOT_FOLDER;?>/js/process.php',
                    data: {
                        access: '<?php echo $_SESSION['sessionKey']; ?>',
                        action: 'download'
                    },
                    dataType   : 'json',
                    success: function (data) {
                        if(data.zip) {
                            top.location.href = data.zip;
                        } else {
                            alert('Could Not authenticate');
                        }
                    }
                    });" value="Download All" class="button"
                />
                <div class="clearfix"></div>
            </div>
        </div>
        <?php } else {?>
            <h2>No Colour data was found</h2>
        <?php } ?>
    </div>
</div>
</body>
</html>
