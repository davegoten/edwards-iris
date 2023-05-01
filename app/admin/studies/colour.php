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

$irisData = $connect->getNextEyeColour($studyId);
if ($irisData == false) {
    header('Location: ' . ROOT_FOLDER . '/admin/studies');
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
<?php if ($studyId === null) { ?>
                <h1>No specific study</h1>
<?php } elseif (empty($studies[$studyId]['studyname'])) { ?>
                <h1>Study #<?php echo $studyId; ?></h1>
<?php } else { ?>
                <h1><?php echo $studies[$studyId]['studyname']; ?></h1>
<?php }?>
                <h2>Iris for #<?php echo $irisData['eyepoolid']?></h2>

                <canvas id="loki" width="<?php echo CANVAS_WIDTH; ?>" height="<?php echo CANVAS_HEIGHT; ?>"></canvas>
                <div id="workArea"></div>
                <script>
                $(document).ready(function () {
                    // Note in canvas 0 degrees in a circle is the point on the right most side (the Three O'Clock position)
                    // Create a new workspace named Loki
                    var canvas = $('#loki');
                    var context = canvas.get(0).getContext('2d');
                    var x = canvas.width()/2+17; // Unused
                    var y = canvas.height()/2+12; // Unused

                    // Create a new Image Object
                    var currIris   = new Image();

                    // Load Image from path in database
                    currIris.src = '<?php echo $imagePath . $irisData['filename'];?>';
                    // Wait for Image to load, then once it does preform calculations
                    currIris.onload = function (img) {
                        // Initialize Image variable
                        if (!img)
                            img         = this;

                        /**
                         * Calculate magnification if any.
                         * Note: that the canvas Loki is the same width and height as used in the study when gather information such
                         * that the magnification is the same here as it was when * the user defined the areas for consistency
                         */
                        var imageFactor = Math.min((canvas.width()/currIris.width), (canvas.height()/currIris.height));
                        var newWidth        = currIris.width * imageFactor;
                        var newHeight       = currIris.height * imageFactor;
                        var newLeft         = (canvas.width() - newWidth)/2;
                        var newTop          = (canvas.height() - newHeight)/2;

                        // degrees constant to covert degrees to radians
                        var degrees         = Math.PI/180;
                        // wedge size in degrees
                        var wedge           = 60;
                        // calculate slope, for use with image cropping later
                        var slope           = Math.tan((180+wedge/2)*degrees);

                        // Initialize object for final data to be saved in
                        var dataset         = {};
                        // Record basic information such as which study id, and which image id
                        dataset['studyid'] = <?php echo $studyId; ?>;
                        dataset['eyepoolid'] = <?php echo $irisData['eyepoolid']; ?>;
                        // Draw image with magnification factors included
                        context.drawImage(currIris,newLeft,newTop,newWidth,newHeight);

                        // Start a new path by first moving to iris centre point (Ix,Iy)
                        context.beginPath();
                        context.globalCompositeOperation = 'destination-in';
                        context.moveTo(<?php echo $irisData['iris_x'];?>, <?php echo $irisData['iris_y'];?>);
                        <?php if(!empty($studies[$studyId]['eye_side']) && trim($studies[$studyId]['eye_side'] == 'left')) {?>
                        // move out to, then draw arc from iris centre point, with radius iris radius at ((360 - wedge/2) * degrees) [330 degrees] (close to 2 o'clock position)
                        // continue drawing the arc to ((wedge /2) * degrees) [30 degrees] (close to 5 o'clock position)
                        context.arc(<?php echo $irisData['iris_x'];?>, <?php echo $irisData['iris_y'];?>, <?php echo $irisData['iris_r'];?>, (360 - wedge/2)*degrees, (wedge/2)*degrees);
                        <?php } else {?>
                        // move out to, then draw arc from iris centre point, with radius iris radius at ((180 - wedge/2) * degrees) [150 degrees] (close to 8 o'clock position)
                        // continue drawing the arc to ((180 + wedge /2) * degrees) [210 degrees] (close to 10 o'clock position)
                        context.arc(<?php echo $irisData['iris_x'];?>, <?php echo $irisData['iris_y'];?>, <?php echo $irisData['iris_r'];?>, (180 - wedge/2)*degrees, (180 + wedge/2)*degrees);
                        <?php } ?>
                        // move back in to the centre of the iris to complete the pie wedge shape and cut out all pixels in the image outside of the wedge
                        context.lineTo(<?php echo $irisData['iris_x'];?>, <?php echo $irisData['iris_y'];?>);
                        context.fill();

                        // Start another path still using the pie wedge from above
                        context.beginPath();
                        context.globalCompositeOperation = 'destination-out';
                        // draw a circle from the centre of the pupil (Px,Py) [Note this is not the same centre as the iris as usually they are not the same]
                        context.arc(<?php echo $irisData['pupil_x'];?>, <?php echo $irisData['pupil_y'];?>, <?php echo $irisData['pupil_r'];?>+2, 0, Math.PI*2);
                        // cut out the circle from the pie to leave a segment of a donut
                        context.fill();

                        // Create a new workspace for the cropped composite donut segment, including the same dimensions as the first workspace Loki
                        save = $('<canvas />');
                        save.get(0).width = canvas.width();
                        save.get(0).height = canvas.height();
                        save.get(0).getContext('2d').drawImage(canvas.get(0),0,0,canvas.width(),canvas.height());

                        // Calculate the bottom left corner of a cropped image by using formula slope * (2 * centre of iris x coord - radius of iris - 1 for space) + centre of iris Y coord
                        var startY = slope*<?php echo ($irisData['iris_x'] - $irisData['iris_r'] - 1) - $irisData['iris_x'];?> + <?php echo $irisData['iris_y']; ?>;
                        // Calculate height as 2 * the difference between the bottom left corner and the iris centre Y coord
                        var endY = 2*(<?php echo $irisData['iris_y'];?> - startY);

                        <?php if(!empty($studies[$studyId]['eye_side']) && trim($studies[$studyId]['eye_side'] == 'left')) {?>
                        // Calculate the left most x by using centre of iris X coord
                        var startX = <?php echo $irisData['iris_x'];?>;
                        // Record the width by using the iris radius + 1 for space
                        var endX = <?php echo $irisData['iris_r'] + 1; ?>;

                        // Calculate the left most X coord for the collarette by iris centre x - 1 for space
                        var startXc =  <?php echo $irisData['pupil_x'];?>;
                        // Calculate the width by setting it to collarette radius
                        var endXc = <?php echo $irisData['collarette_r'] + 1?>;
                        // Use the same Y values for collarette despite it being much smaller
                        <?php } else {?>
                        // Calculate the left most x by using centre of iris X coord, and subtracting radius - 1 for space
                        var startX = <?php echo $irisData['iris_x'] - $irisData['iris_r'] - 1;?>;
                        // Record the width by using the iris radius
                        var endX = <?php echo $irisData['iris_r']?>;

                        // Calculate the left most X coord for the collarette by iris centre x - collarette raidus - 1 for space
                        var startXc =  <?php echo $irisData['iris_x'] - $irisData['collarette_r'] - 1 - abs($irisData['iris_x'] - $irisData['pupil_x']);?>;
                        // Calculate the width by setting it to collarette radius
                        var endXc = <?php echo $irisData['collarette_r']?>;
                        // Use the same Y values for collarette despite it being much smaller
                        <?php } ?>
                        // Start another path starting at centre of pupil (Px,Py)
                        context.beginPath();
                        context.globalCompositeOperation = 'destination-out';
                        // draw a circle from the centre of the pupil (Px,Py) [Note this is not the same centre as the iris as usually they are not the same]
                        context.arc(<?php echo $irisData['pupil_x'];?>, <?php echo $irisData['pupil_y'];?>, <?php echo $irisData['collarette_r'];?>, 0, Math.PI*2);
                        // cut out the circle from the pie to leave a segment of a donut
                        context.fill();

                        // Create a buffer space to save the new image in
                        bufferO = $('<canvas />');
                        // Give it the calculated width and height above
                        bufferO.get(0).width = endX;
                        bufferO.get(0).height = endY;
                        // copy from the the current image all the data from a box bounded by the calculated values and redraw into the smaller box of that size
                        bufferO.get(0).getContext('2d').drawImage(canvas.get(0),startX,startY,endX,endY,0,0,endX,endY);
                        //canvas.after(bufferO);

                        // save image as "<original-file-name>-<UserId>-Outer.png" for later use
                        $.ajax({
                            type: 'POST',
                            url: '/js/saveColorImage.php',
                            data: '{"imageData":"' + bufferO.get(0).toDataURL('image/png').replace('data:image/png;base64,','')
                            + '","filename" : "<?php echo $irisData['filename'];?>-<?php echo $userId;?>-Outer.png", "access" : "<?php echo $_SESSION['sessionKey']; ?>", "study": "<?php echo $studyId;?>"}',
                            contentType: 'application/json; charset=utf-8',
                            dataType:'json',
                            success: function (m) {
                            }
                        });

                        // Create a pixel counter
                         var imgD = null;
                        // Create an object with values for red, blue, green and transparent
                        var colors = {'red':0,'blue':0,'green':0,'transparent':0};
                        // Create a count of the number of pixels that 'counted' as colour data
                        var count = 0;

                        // Loop through every pixel in the image within the same cropped boundaries used to create the png
                        for (i = startX; i < +startX+endX; i++) {
                             for (j = startY; j < startY+endY; j++) {
                                 // select a 1 pixel by 1 pixel area at a time
                                 imgD = context.getImageData(i,j,1,1);
                                 // Make sure the image is fully opaque to 'count'
                                 if (imgD.data[3] == 255) {
                                        // record the pixel's red, green, blue value counts independently
                                        colors.red += imgD.data[0];
                                        colors.green += imgD.data[1];
                                        colors.blue += imgD.data[2];
                                        // Increment the pixel count by one
                                        count++;
                                 } else {
                                        // if the pixel is not fully opaque count it as a transparent pixel instead as partially transparent pixels produced by poor anti-aliasing around the edges would bias the colour counts to be overly light
                                        colors.transparent++;
                                 }
                             }
                        }

                        // once the count is complete, calculate the cielab values by converting RGB to CILab using values: total red/pixels counted, total green/pixels counted, total blue/pixels counted from the outer zone
                        var cielab1 = RGBtoCILab((colors['red']/count),(colors['green']/count),(colors['blue']/count));
                        // Record the number of pixels counted, RGB and LAB values of the outer Iris in final dataset values
                        dataset['Osample'] = count;
                        dataset['Or'] = (colors['red']/count);
                        dataset['Og'] = (colors['green']/count);
                        dataset['Obl'] = (colors['blue']/count);
                        dataset['Ol'] = cielab1['L'];
                        dataset['Oa'] = cielab1['a'];
                        dataset['Ob'] = cielab1['b'];

                        //Reload the original donut segment
                        context.globalCompositeOperation = 'destination-over';
                        context.drawImage(save.get(0),0,0,canvas.width(),canvas.height());
                        // Start another path starting at centre of pupil (Px,Py)
                        context.beginPath();
                        context.globalCompositeOperation = 'destination-in';
                        // draw a circle with radius of the collarette
                        context.arc(<?php echo $irisData['pupil_x'];?>, <?php echo $irisData['pupil_y'];?>, <?php echo $irisData['collarette_r'];?>, 0, Math.PI*2);
                        // remove everything outside the circle from the donut segment
                        context.fill();

                        // Create a buffer space to save the new image in
                        bufferI = $('<canvas />');
                        // Give it the calculated width and height above for the smaller collarette
                        bufferI.get(0).width = endXc;
                        bufferI.get(0).height = endY;
                        // copy from the the current image all the data from a box bounded by the calculated values and redraw into the smaller box of that size
                        bufferI.get(0).getContext('2d').drawImage(canvas.get(0),startXc,startY,endXc,endY,0,0,endXc,endY);
                        //canvas.after(bufferI);

                        // save image as "<original-file-name>-<UserId>-Inner.png" for later use
                        $.ajax({
                            type: 'POST',
                            url: '/js/saveColorImage.php',
                            data: '{"imageData":"' + bufferI.get(0).toDataURL('image/png').replace('data:image/png;base64,','')
                            + '","filename" : "<?php echo $irisData['filename'];?>-<?php echo $userId;?>-Inner.png", "access" : "<?php echo $_SESSION['sessionKey']; ?>", "study": "<?php echo $studyId;?>"}',
                            contentType: 'application/json; charset=utf-8',
                            dataType:'json',
                            success: function (m) {
                            }
                        });

                        // Create another object with values for red, blue, green and transparent
                        var colors2 = {'red':0,'blue':0,'green':0,'transparent':0};
                        // Create another count of the number of pixels that 'counted' as colour data
                        var count2 = 0;
                        // Loop through every pixel in the image within the same cropped boundaries used to create the 2nd png
                        for (i = startXc; i < +startXc+endXc; i++) {
                            for (j = startY; j < startY+endY; j++) {
                                // select a 1 pixel by 1 pixel area at a time
                                imgD = context.getImageData(i,j,1,1);
                                // Make sure the image is fully opaque to 'count'
                                if (imgD.data[3] == 255) {
                                    // record the pixel's red, green, blue value counts independently
                                    colors2.red += imgD.data[0];
                                    colors2.green += imgD.data[1];
                                    colors2.blue += imgD.data[2];
                                    // Increment the pixel count by one
                                    count2++;
                                } else {
                                    // if the pixel is not fully opaque count it as a transparent pixel instead as partially transparent pixels produced by poor anti-aliasing around the edges would bias the colour counts to be overly light
                                    colors2.transparent++;
                                }
                            }
                        }

                        // once the count is complete, calculate the cielab values by converting RGB to CILab using values: total red/pixels counted, total green/pixels counted, total blue/pixels counted from the inner zone
                        var cielab2 = RGBtoCILab((colors2['red']/count2),(colors2['green']/count2),(colors2['blue']/count2));
                        // also, calculate the cielab values by converting RGB to CILab using values: total red/pixels counted, total green/pixels counted, total blue/pixels counted from both zones together
                        var cielab3 = RGBtoCILab(
                                ((colors['red']+colors2['red'])/(count+count2)),
                                ((colors['green']+colors2['green'])/(count+count2)),
                                ((colors['blue']+colors2['blue'])/(count+count2)));

                        // Record the number of pixels counted, RGB and LAB values of the inner Iris in final dataset values
                        dataset['Isample'] = count2;
                        dataset['Ir'] = (colors2['red']/count2);
                        dataset['Ig'] = (colors2['green']/count2);
                        dataset['Ibl'] = (colors2['blue']/count2);
                        dataset['Tr'] = ((colors['red']+colors2['red'])/(count+count2));
                        dataset['Tg'] = ((colors['green']+colors2['green'])/(count+count2));
                        dataset['Tbl'] = ((colors['blue']+colors2['blue'])/(count+count2));

                        // Record the number of pixels counted, RGB and LAB values of the total Iris in final dataset values
                        dataset['Tsample'] = count+count2;
                        dataset['Il'] = cielab2['L'];
                        dataset['Ia'] = cielab2['a'];
                        dataset['Ib'] = cielab2['b'];
                        dataset['Tl'] = cielab3['L'];
                        dataset['Ta'] = cielab3['a'];
                        dataset['Tb'] = cielab3['b'];

                        // Record the deltaE between the inner and outer zones
                        dataset['deltae'] = deltaE(cielab1['L'],cielab1['a'],cielab1['b'],cielab2['L'],cielab2['a'],cielab2['b']);

                        // Record identity values
                        dataset['access'] = '<?php echo $_SESSION['sessionKey']; ?>';
                        dataset['action'] = 'colour';
                        dataset['i'] = <?php echo $userId; ?>;

                        // Save all calculated data points in the database and when complete reload the page with another image, unless there are no new images to load
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo ROOT_FOLDER;?>/js/process.php',
                            data: dataset,
                            dataType   : 'json',
                            success: function (data) {
                                if(data['success'] == true) {
                                    location.reload();
                                } else {
                                    window.location.href = '/admin/dashboard.php';
                                }
                            }
                        });
                        return;
                    }
                });



                </script>
            </div>
        </div>
    </body>
</html>
