<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use EdwardsEyes\inc\required;
use EdwardsEyes\inc\database;

(new required)->init();

$accessLevel = intval($_SESSION['userinfo']['access']);
if ($accessLevel < array_search('coordinator', ACL_RANKS)) {
    echo json_encode(['jquery-upload-file-error' => 'Permission denied']);
    exit();
} else {
    $connect = new database();
    $studyId = null;
    $studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
    foreach ($studies as $studyIdIdx => $studyDetails) {
        if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
            $studyId = $studyIdIdx;
        }
    }
    $path = dirname(SERVER_ROOT) . '/html/images/studies/structures/' . $studyId . '/';
    if (!is_readable($path)) {
        mkdir($path);
    }
    if (isset($_FILES["ys"])) {
        $ret = array();
        $error = $_FILES["ys"]["error"];
        if (!is_array($_FILES["ys"]["name"])) //single file
        {
            $fileName = $_FILES["ys"]["name"];
            move_uploaded_file($_FILES["ys"]["tmp_name"], $path . $fileName);
            $ret[] = $fileName;
        } else  //Multiple files, file[]
        {
            $fileCount = count($_FILES["ys"]["name"]);
            for ($i = 0; $i < $fileCount; $i++) {
                $fileName = $_FILES["ys"]["name"][$i];
                move_uploaded_file($_FILES["ys"]["tmp_name"][$i], $path . $fileName);
                $ret[] = $fileName;
            }
        }
        echo json_encode($ret);
        exit();
    }
}
echo json_encode(['jquery-upload-file-error' => 'Permission denied']);