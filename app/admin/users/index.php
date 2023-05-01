<?php
namespace EdwardsEyes\admin\users;

use EdwardsEyes\inc\database;

$connect = new database();

$participants = $connect->getParticipants();

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

                <style>
                table {
                    float: left;
                    width: 30%;
                    margin: 0px 1.5% 20px 1.5%;
                }
                table tr td {
                    padding: 15px 25px;
                }
                table tr td:first-child {
                    text-align: left;
                }
                table tr td label {
                    display: block;
                    float: left;
                    clear: left;
                    width: 30%;
                }
                </style>
                <table>
                    <tr>
                        <th>Add a new User for my study</th>
                    </tr>
                    <tr>
                        <td>
                        <form action="/admin/users/add.php" method="post">
                            <label for="userName">Username</label>
                            <input type="text" name="userName" id="userName" /><br />
                            <label for="level">Access :</label>
                            <select id="level" name="level">
<?php
foreach (ACL_RANKS as $level => $title) {
    if ((intval($_SESSION['userinfo']['access']) > $level || $level == 0) && is_numeric($level)) {
?>
                                <option value="<?php echo $level ?>"><?php echo ucwords($title) ;?></option>
<?php
    }
}
?>
                            </select><br />
                            <input type="hidden" name="access" value="<?php echo $_SESSION['sessionKey'];?>" />
                            <input type="submit" name="add" id="add" value="Add" />
                        </form>
                        </td>
                    </tr>

                </table>
                <?php
                foreach ($participants as $user) {
                    $thisUsersStudies = array();
                    foreach ((array)$user['studies'] as $s) {
                        if ($s['status'] == 'Y') {
                            $thisUsersStudies[] = $s['studyid'];
                        }
                    }
                    if (
                       $user['userid'] == $_SESSION['userinfo']['id'] ||
                       $_SESSION['userinfo']['access'] >= 99 ||
                       in_array($studyId, $thisUsersStudies) &&
                       $_SESSION['userinfo']['access'] >= $user['access']
                    ) {
                ?>
                <table>
                    <tr>
                        <th><?php
                        $userTitle = ucwords($user['username']) ." (" . ucwords(ACL_RANKS[intval($user['access'])]) .")";
                        if ($user['banned'] === 'Y') {
                            $userTitle = "<strike>" . $userTitle ."</strike>";
                        }
                        echo $userTitle;
                    ?></th>
                    </tr>
                    <tr>
                        <td><?php
                        if ($studyId <= 0) {
                            echo "You're not running a study at this time";
                        } elseif (!empty($user['studies'][$studyId])) {
                            echo 'User is part of my study';
                        } elseif (empty($user['email'])) {
                            echo 'Contact information is not available';
                        } elseif ($user['contactable'] == 'Y') {
                            echo 'Contact user to participate in my study';
                        } else {
                            echo 'User does not wish to be contacted';
                        }
                    ?></td>
                    </tr>

                </table>
<?php
                    }
                }
?>
            </div>
        </div>
    </body>
</html>
