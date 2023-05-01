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
        <script src="<?php echo ROOT_FOLDER;?>/js/three.min.js"></script>
        <script src="<?php echo ROOT_FOLDER;?>/js/helvetiker_regular.typeface.js"></script>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <style>
        #layers {
            float: none;
            margin: 0 auto;
        }
        #chart {
            border: 1px #000 solid;
            width: <?php echo CANVAS_WIDTH; ?>px;
            height: <?php echo CANVAS_HEIGHT; ?>px;
            background-color: #FFFFFF;
        }
        .chart {
            display: block;
            margin: 0 auto;
            margin-top: 15px;
            float: none;
            border: 1px #000 solid;
            width:<?php echo CANVAS_WIDTH; ?>px;
            background-color: #FFFFFF;
        }
        .chart svg {
            width:<?php echo CANVAS_WIDTH; ?>px;
        }
        .chart svg g:first-child {
            text-align:center;
        }
        .google-visualization-tooltip {
            text-align: center;
            padding: 8px !important;
            padding-bottom: 0px !important;
            border: 1px #000000 solid !important;
            border-radius: 5px !important;
            font-size: 16pt !important;
        }
        </style>
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
                <p>Note: these images are overly large to satisfy some publication's quality requirements. </p>
                <p>Please right click the save link below each graph to save the high quality image.</p>
                <p>To view the full image, mouse over the image.</p>

                <?php
                $useGraph = 80;
                $maxMin = array(
                    'min' => array('total_l' => PHP_INT_MAX, 'total_a' => PHP_INT_MAX, 'total_b' => PHP_INT_MAX),
                    'max' => array('total_l' => 0, 'total_a' => 0, 'total_b' => 0)
                );

                foreach ($data as $idx => $datum) {
                    foreach ($datum as $key => $val) {
                        if (isset($maxMin['min'][$key])) {
                            $maxMin['min'][$key] = min($maxMin['min'][$key], $val);
                        } else {
                            $maxMin['min'][$key] = $val;
                        }
                        if (isset($maxMin['max'][$key])) {
                            $maxMin['max'][$key] = max($maxMin['max'][$key], $val);
                        } else {
                            $maxMin['max'][$key] = $val;
                        }
                    }
                }
                $actualMaxMin = $maxMin;


                $step['x'] = round(10*($maxMin['max']['total_l'] - $maxMin['min']['total_l']) / $useGraph);
                $step['y'] = round(10*($maxMin['max']['total_a'] - $maxMin['min']['total_a']) / $useGraph);
                $step['z'] = round(10*($maxMin['max']['total_b'] - $maxMin['min']['total_b']) / $useGraph);
                $shift['x'] = $maxMin['min']['total_l'] - round($actualMaxMin['min']['total_l']) + $step['x'];
                $shift['y'] = $maxMin['min']['total_a'] - round($actualMaxMin['min']['total_a']) + $step['y'];
                $shift['z'] = $maxMin['min']['total_b'] - round($actualMaxMin['min']['total_b']) + $step['z'];

                // Add some buffer space on each side
                $actualMaxMin['min']['total_l'] -= $step['x'];
                $actualMaxMin['min']['total_a'] -= $step['y'];
                $actualMaxMin['min']['total_b'] -= $step['z'];
                $actualMaxMin['max']['total_l'] += $step['x'];
                $actualMaxMin['max']['total_a'] += $step['y'];
                $actualMaxMin['max']['total_b'] += $step['z'];


                ?>
                <?php
                    $pointDescriptByAncestry = array(
                        'other' => 'shape-type: polygon; shape-sides: 10; stroke-width: 1; stroke-color: #00FF00;',
                        'hispanic' => 'shape-type: polygon; shape-sides: 6; stroke-width: 1; stroke-color: #000000;',
                        'african american' => 'shape-type: polygon; shape-sides: 5; stroke-width: 1; stroke-color: #AAAAAA;',
                        'south asian' => 'shape-type: circle; stroke-width: 1; stroke-color: #0000FF;',
                        'east asian' => 'shape-type: triangle; stroke-width: 1; stroke-color: #FF00FF;',
                        'european' => 'shape-type: square; stroke-width: 1; stroke-color: #FF0000;',
                    );

//                    if ($studyId == 1) {
//                        $lowerParticipantBoundry = 0;
//                        $ranges = array(1800,PHP_INT_MAX);
//                    } else {
                        $lowerParticipantBoundry = 0;
                        $ranges = array(PHP_INT_MAX);
//                    }

                    $graphs = array(
                        'L* vs a*' => array(
                            'x' => 'L',
                            'y' => 'a'
                        ),
                        'L* vs b*' => array(
                            'x' => 'L',
                            'y' => 'b'
                        ),
                        'a* vs b*' => array(
                            'x' => 'a',
                            'y' => 'b'
                        ),
                    );
                ?>
<?php
$chartIndex = 2;
foreach ($ranges as $upperParticipantBoundy) {
    foreach ($graphs as $title => $axies) {
?>
                <script type="text/javascript">

                    google.load("visualization", "1", {packages:["corechart"]});
                    google.setOnLoadCallback(drawChart);
                    function drawChart() {
                        var data = new google.visualization.DataTable();
                        data.addColumn('number', '<?php echo $axies['x'];?>*');
                        data.addColumn('number', '<?php echo $axies['y'];?>*');
                        data.addColumn({ type: 'string', role: 'style' });
                        data.addColumn({ type: 'string', role: 'tooltip', p:{'html':true} });
                        data.addRows([
                            <?php
                            foreach ($data as $key => $val) {
                                if (!in_array(strtolower($val['ancestry']), array_keys($pointDescriptByAncestry))) {
                                    $val['ancestry'] = 'Other';
                                }
                            ?>[<?php
                                echo $val['total_'.strtolower($axies['x'])];
                                ?>, <?php
                                echo $val['total_'.strtolower($axies['y'])]?>, 'point { <?php
                                echo $pointDescriptByAncestry[strtolower($val['ancestry'])];?>fill-color: ' + rgbToHex(CILabtoRGB(<?php
                                echo $val['total_l']?>,<?php
                                echo $val['total_a']?>,<?php
                                echo $val['total_b']?>))+ ' }', '<em>#<?php echo $val['participant'];?></em><br /><strong><?php echo $val['ancestry'];?></strong><br />[<?php
                                echo round($val['total_l'], 2);?>, <?php echo round($val['total_a'], 2);?>, <?php
                                echo round($val['total_b'], 2);?>]']<?php
                                if (end($data) != $val) {
                                    echo ",";
                                }
                            }
                            ?>
                        ]);
                        options = {
                            title: '<?php echo $title;?>',
                            legend : 'none',
                            width: <?php echo $dpi/2*CANVAS_WIDTH; ?>,
                            height: <?php echo $dpi/2*CANVAS_HEIGHT; ?>,
                            pointSize: <?php echo $dpi/2*8; ?>,
                            backgroundColor: { fill:'transparent' },
                            vAxis: {
                                title: "<?php echo $axies['y']?>*",
                                titleTextStyle: {
                                    color: "#000000"
                                },
                                gridlines : {
                                    count:10
                                },
                                format: '#.##'
                            },
                            hAxis: {
                                title: "<?php echo $axies['x']?>*",
                                titleTextStyle: {
                                    color: "#000000"
                                },
                                gridlines: {
                                    count:10
                                },
                                format: '#.##'
                            },
                            tooltip: {isHtml: true}
                        };
                        var chart = new google.visualization.ScatterChart(document.getElementById('chart<?php echo $chartIndex;?>'));
                        google.visualization.events.addListener(chart, 'ready', function () {
                            $('#chart<?php echo $chartIndex;?>').css('overflow','hidden');
                            $('#chart<?php echo $chartIndex;?> svg').css('overflow','visible');
                            $('#chart<?php echo $chartIndex;?> svg').attr('viewBox','0 0 ' + $('#chart<?php echo $chartIndex;?> svg').attr('width') + ' ' + $('#chart<?php echo $chartIndex;?> svg').attr('height'));
                            $('#chart<?php echo $chartIndex;?>').after('<a href="' + chart.getImageURI() + '" target="_BLANK">Save</a>');
                            $('#chart<?php echo $chartIndex;?>').hover(function () {
                                    $(this).css('overflow','visible');
                                    $(this).css('width',$('#chart<?php echo $chartIndex;?> svg').attr('width'));
                                },function () {
                                    $(this).css('overflow','hidden');
                                    $(this).css('width',<?php echo CANVAS_WIDTH; ?>);
                            });

                        });
                        $("text:contains('<?php echo $title;?>')").attr({'x':'<?php echo $dpi/2*CANVAS_WIDTH / 2; ?>px', 'text-anchor':'middle'});
                        chart.draw(data, options);


                    }
                </script>
<?php
        $chartIndex++;
    }
    $lowerParticipantBoundry = $upperParticipantBoundy;
} ?>
<?php for ($i = 2; $i < $chartIndex; $i++) {?>
                <div id="chart<?php echo $i?>" class="chart"></div>
<?php } ?>
                <div class="clearfix"></div>
            </div>
        </div>
    </body>
</html>
