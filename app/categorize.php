<?php
namespace EdwardsEyes;

use EdwardsEyes\inc\database;

unset($_SESSION['studyinfo']);

if (!empty($_SESSION['userinfo']['currentStudy']) && !isset($_GET['id'])) {
    $studyId = intval($_SESSION['userinfo']['currentStudy']);
} elseif (empty($_GET['id']) || intval($_GET['id']) == 0) {
    $_SESSION['userinfo']['currentStudy'] = 'sample';
    $studyId = 'sample';
} else {
    $studyId = intval($_GET['id']);
    $_SESSION['userinfo']['currentStudy'] = intval($_GET['id']);
}
$connect = new database();
$irisData = $connect->getIris($_SESSION['userinfo']['id'], $studyId);
$studyData = $connect->getStudiesFor($_SESSION['userinfo']['id']);

if ($irisData == 'complete') {
    header('Location: ' . ROOT_FOLDER . '/admin/whichStudy.php');
    exit();
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
                <h1>Categorizing #<?php echo $irisData;?></h1>
                <section class="instructions">
<?php
switch ($studyData[$studyId]['additional_data']) {
    case 'cincinnati':
    case 'colour_only':
        $stage = array(
            1 => 'Is there significant obstruction of your view of the Iris?',
            2 => 'Drag and Drop the middle of the crosshairs in the image to find the exact <strong>centre of the Iris</strong> in the photograph below then click <strong>Continue</strong>',
            3 => 'Drag and Drop the middle of the crosshairs in the image to find the exact <strong>centre of the Pupil</strong> in the photograph below then click <strong>Continue</strong>',
            4 => 'Drag the edge of the circle out to contain the <strong>Iris</strong>, make a best fit circle then click <strong>Continue</strong>',
            5 => 'Drag the edge of the circle out to contain the <strong>Pupil</strong>, make a best fit circle then click <strong>Continue</strong>',
            6 => 'Drag the edge of the circle out to contain the <strong>collarette</strong>, make a best fit circle then click <strong>Continue</strong>',
            13=> 'This iris is fully categorized, do you wish to save your work now?'
        );
        break;
    case 'structure_only':
        $stage = array(
            1 => 'Is there significant obstruction of your view of the Iris?',
            2 => 'Drag and Drop the middle of the crosshairs in the image to find the exact <strong>centre of the Iris</strong> in the photograph below then click <strong>Continue</strong>',
            3 => 'Drag and Drop the middle of the crosshairs in the image to find the exact <strong>centre of the Pupil</strong> in the photograph below then click <strong>Continue</strong>',
            4 => 'Drag the edge of the circle out to contain the <strong>Iris</strong>, make a best fit circle then click <strong>Continue</strong>',
            5 => 'Drag the edge of the circle out to contain the <strong>Pupil</strong>, make a best fit circle then click <strong>Continue</strong>',
            6 => 'Drag the edge of the circle out to contain the <strong>collarette</strong>, make a best fit circle then click <strong>Continue</strong>',
            7 => "Click the <strong>Freckles and Nevi</strong>. <ul><li><strong>Left Click</strong> if it <strong>fits in</strong> the circle completely</li>"
              .  "<li><strong>Right Click</strong> if it <strong>doesn't fit in</strong> the circle completely</li>"
              .  "<li><strong>Change or delete</strong> any mistakes in the list below the image</li></ul>",
            8 => 'Rotate the line until it touches the left or right end point of the <strong>Contraction Furrows</strong>.<br /> Then answer the questions below?',
            9 => 'Rotate the line until it touches the left or right end point of the <strong>Wolfflin Nodules</strong>.<br /> Then answer the questions below?',
            10=> 'Mark the outer most edge of each <strong>Crypt</strong> you see. You can edit the sizes (Large or Small) of each Crypt below the image afterwards',
            13=> 'This iris is fully categorized, do you wish to save your work now?'
        );
        break;
    case 'pigmentation_study':
        // No Break
    case 'both':
        // No Break
    default:
        $stage = array(
            1 => 'Is there significant obstruction of your view of the Iris?',
            2 => 'Drag and Drop the middle of the crosshairs in the image to find the exact <strong>centre of the Iris</strong> in the photograph below then click <strong>Continue</strong>',
            3 => 'Drag and Drop the middle of the crosshairs in the image to find the exact <strong>centre of the Pupil</strong> in the photograph below then click <strong>Continue</strong>',
            4 => 'Drag the edge of the circle out to contain the <strong>Iris</strong>, make a best fit circle then click <strong>Continue</strong>',
            5 => 'Drag the edge of the circle out to contain the <strong>Pupil</strong>, make a best fit circle then click <strong>Continue</strong>',
            6 => 'Drag the edge of the circle out to contain the <strong>collarette</strong>, make a best fit circle then click <strong>Continue</strong>',
            7 => "Click the <strong>Freckles and Nevi</strong>. <ul><li><strong>Left Click</strong> if it <strong>fits in</strong> the circle completely</li>"
              .  "<li><strong>Right Click</strong> if it <strong>doesn't fit in</strong> the circle completely</li>"
              .  "<li><strong>Change or delete</strong> any mistakes in the list below the image</li></ul>",
            8 => 'Rotate the line until it touches the left or right end point of the <strong>Contraction Furrows</strong>.<br /> Then answer the questions below?',
            9 => 'Rotate the line until it touches the left or right end point of the <strong>Wolfflin Nodules</strong>.<br /> Then answer the questions below?',
            10=> 'Mark the outer most edge of each <strong>Crypt</strong> you see. You can edit the sizes (Large or Small) of each Crypt below the image afterwards',
            11=> 'Is there a pigmented ring on the sclera?',
            12=> 'Is there spotting on the sclera?',
            13=> 'This iris is fully categorized, do you wish to save your work now?'
        );
}
foreach ($stage as $stageNum => $instructions) {?>

                    <div id="<?php echo $stageNum;?>" class="steps step<?php echo $stageNum;?>">
                        <h2>Step <?php echo $stageNum;?></h2>
                        <p><?php echo $instructions;?></p>
                    </div>
<?php } ?>

                </section>
                <script>

                </script>

                <div id="layers" class="clearfix">
                    <canvas id="sauron" width="<?php echo CANVAS_WIDTH; ?>" height="<?php echo CANVAS_HEIGHT; ?>">
                    </canvas>
                    <canvas id="blackgate" width="<?php echo CANVAS_WIDTH; ?>" height="<?php echo CANVAS_HEIGHT; ?>">
                    </canvas>

                </div>
                <div class="clearfix"></div>
                <table id="plotPoints">
                </table>
                <div id="errors" class="clearfix"></div>
                <div id="navigation" class="clearfix">
                    <input id="continue" type="button" value="Continue &raquo;" class="button" />
                    <input id="back" type="button" value="&laquo; Back" class="button clearfix" />
                </div>
                <table id="dataset">
                    <tr>
                        <td>Dataset</td>
                        <td id="stepsResult">{}</td>
                    </tr>
                </table>
                <div id="debug"></div>
                <canvas id="inspector" width="250" height="250">
                </canvas>
            </div>
        </div>
    </body>
</html>
