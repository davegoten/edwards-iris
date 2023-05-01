<?php
namespace EdwardsEyes\admin\users;

use EdwardsEyes\inc\database;

$accessLevel = intval($_SESSION['userinfo']['access']);

$connect = new database();
$studyId = null;
$studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
foreach ($studies as $studyIdIdx => $studyDetails) {
    if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
        $studyId = $studyIdIdx;
    }
}

if ($accessLevel < array_search('coordinator', ACL_RANKS)) {
    header('Location: ' . ROOT_FOLDER . '/admin/dashboard.php');
    exit();
}
if (!empty($_SESSION['sessionKey'])
        && !empty($_POST['access'])
        && !empty($_POST['userName'])
        && (!empty($_POST['level'])
        || $_POST['level'] == 0)
        && $_SESSION['sessionKey'] == $_POST['access']
        && $_POST['level'] <= $accessLevel
) {
    $newId = $connect->addUser($_POST['userName'], $_POST['level'], $studyId);
    if ($newId === false) {
        // Failed
    }
}
header('Location: ' . ROOT_FOLDER . '/admin/users/');
exit();
