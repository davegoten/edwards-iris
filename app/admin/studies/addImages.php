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
$imagePath = ROOT_FOLDER . '/images/studies/structures/' . $studyId . '/';
$sizeLimit = min(ini_get('upload_max_filesize'), ini_get('post_max_size'));
?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo SITE_TITLE; ?></title>
    <link rel="stylesheet" href="<?php echo ROOT_FOLDER;?>/css/style.php" type="text/css" />
    <link rel="stylesheet" href="<?php echo ROOT_FOLDER;?>/css/uploadfile.css" type="text/css" />
    <script src="<?php echo ROOT_FOLDER;?>/js/jquery.min.js"></script>
    <script src="<?php echo ROOT_FOLDER;?>/js/script.php"></script>
    <script src="<?php echo ROOT_FOLDER;?>/js/jquery.uploadfile.min.js"></script>
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
        <?php if (!empty($studyId)) { ?>
            <h2><strong>WARNING: This will overwrite files if they already exist</strong></h2>
            <p>
                This will not reset the data in your data, only the image will be replaced. This could cause issues
                later down the line. Be sure you don't accidentally overwrite already existing images.
            </p>
            <p>
                Drag and drop files into the area below, or click upload. The maximum file size is <?php
            echo min(ini_get('upload_max_filesize'),ini_get('post_max_size'));
                ?> and will accept jpg, png and gif files only
            </p>
            <div id="fileuploader">Upload</div>
            <script>
                $(document).ready(function()
                {
                    $("#fileuploader").uploadFile({
                        url:"/js/saveImage.php",
                        fileName:"ys",
                        multiple:true,
                        dragDrop:true,
                        sequential:true,
                        allowedTypes: "jpg,jpeg,png,gif",
                        acceptFiles: "image/",
                        maxFileSize: "<?php echo $sizeLimit; ?>",
                        dragDropStr: "Drag Drop",
                        abortStr:"Abort",
                        cancelStr:"Cancel",
                        extErrorStr:"Please upload these filetypes only:",
                        sizeErrorStr:"Images must be less than:",
                        uploadErrorStr:"upload error"
                    });
                    $("#startUpload").click(function() {
                        uploadObj.startUpload();
                    });
                });

            </script>
        <?php }?>
    </div>
</div>
</body>
</html>
