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
                <p>Click the image to roate the graph roughly in the direction of where you clicked</p>
                <p>Mouse wheel can be used to zoom in and out</p>
                <p>Right click and save image to save a copy of your current view as an image</p>

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
                <script>
                $(document).ready(function () {
                    var canvas = $('#chart');
                    var dpi = <?php echo $dpi; ?>;
                    var scene = new THREE.Scene();
                    var renderer = new THREE.WebGLRenderer({ alpha: true, preserveDrawingBuffer: true });
                    var camera = new THREE.PerspectiveCamera(65, (canvas.width() *dpi)/ (canvas.height()*dpi), 0.1*dpi, 10000*dpi );
                    var xStep = <?php echo $step['x']; ?>;
                    var yStep = <?php echo $step['y'];?>;
                    var zStep = <?php echo $step['z'];?>;
                    var tickmarks = 10;//Math.ceil(*multiplier[i]) +1;
                    var meshx, meshy, meshz;
                    var threeDTicks = new Array();

                    function makeGrid(width, linewidth, depth, spaces, colour, majorColour, majorIndex1, majorIndex2, which) {
                        var grid = new THREE.Geometry();
                        var majorMaterial = new THREE.MeshBasicMaterial({ color: majorColour, side: THREE.DoubleSide});
                        var material = new THREE.MeshBasicMaterial({ color: colour, side: THREE.DoubleSide});
                        var materials = [material,majorMaterial];
                        var useMaterial;
                        var geometry;

                        for ( var i = - width; i <= width; i += spaces ) {
                            geometry = new THREE.Mesh(new THREE.BoxGeometry(width*2, depth, linewidth), material);
                            geometry2 = new THREE.Mesh(new THREE.BoxGeometry(width*2, depth, linewidth), material);
                            for ( var face in geometry.geometry.faces ) {
                                geometry.geometry.faces[face].materialIndex = 0;
                                geometry2.geometry.faces[face].materialIndex = 0;
                                if (i == majorIndex1) {
                                    geometry.geometry.faces[face].materialIndex = 1;
                                }
                                if (i == majorIndex2) {
                                    geometry2.geometry.faces[face].materialIndex = 1;
                                }
                            }
                            geometry.position.z = - width + i;

                            geometry2.position.x = i;
                            geometry2.position.z = -width;
                            geometry2.rotation.y = Math.PI/2;

                            geometry.updateMatrix();
                            geometry2.updateMatrix();

                            text = parseInt(getLabelText(which,'',i,tickmarks));
                            if (which == 'x' && 0 > parseInt(text)) {
                                text = 0;
                            }
                            ticks = new THREE.Mesh(
                                    new THREE.TextGeometry(text, {size: 6*dpi, font: "helvetiker", weight: "normal", style : "normal", material : 0, extrudeMaterial: 1}),
                                    new THREE.MeshFaceMaterial(
                                            [new THREE.MeshBasicMaterial({ color: 0x000000 }),
                                            new THREE.MeshBasicMaterial({ color: 0x555555 })]
                                    )
                            );
                            ticks.geometry.computeBoundingBox();
                            ticks.updateMatrix();
                            switch (which) {
                                case 'x':
                                    ticks.position.x = -width -10*dpi;
                                    ticks.position.y = i;
                                    ticks.position.z = width + 15*dpi;
                                    break;
                                case 'y':
                                    ticks.position.x = i;
                                    ticks.position.y = -width - 10*dpi;
                                    ticks.position.z = width + 15*dpi;
                                    break;
                                case 'z':
                                    ticks.position.x = width + 5*dpi;
                                    ticks.position.y = -width - 10*dpi;
                                    ticks.position.z = i;
                                    break;
                            }
                            ticks.lookAt(camera.position);
                            threeDTicks.push(ticks);

                            grid.merge(geometry.geometry, geometry.matrix);
                            grid.merge(geometry2.geometry, geometry2.matrix);
                        }

                        gridlines = new THREE.Mesh(grid, new THREE.MeshFaceMaterial(materials));
                        gridlines.castShadow=true;
                        return gridlines;
                    }


                    function addPlotAt(x, y, z, marker, colour, elem) {
                        size = 4 * dpi;
                        if (typeof(elem) === 'undefined') elem = scene;
                        switch(marker.toLowerCase()) {
                            case 'other':
                                var geometry = new THREE.DodecahedronGeometry(size, 0);
                                break;
                            case 'hispanic':
                                var geometry = new THREE.IcosahedronGeometry(size, 0);
                                break;
                            case 'african american':
                                var geometry = new THREE.OctahedronGeometry(size, 0);
                                break;
                            case 'south asian':
                                var geometry = new THREE.SphereGeometry( size/2, 32, 32 );
                                break;
                            case 'east asian':
                                var geometry = new THREE.TetrahedronGeometry( size, 0);
                                break;
                            case 'european':
                                var geometry = new THREE.BoxGeometry( size, size, size );
                                break;
                            default:
                                return;

                        }

                        var material = new THREE.MeshLambertMaterial( { color: 0xFFFFFF } );
                        material.color.setStyle( colour );
                        var dot = new THREE.Mesh( geometry, material );
                        dot.position.x = x;
                        dot.position.y = y;
                        dot.position.z = z;

                        elem.add( dot );
                    }

                    function checkRotation(pressed, newRotSpeed, newZoomSpeed){
                        var rotSpeed = 0.01;
                        var zoomSpeed = 3;
                        if (typeof newRotSpeed != 'undefined') {
                            rotSpeed = parseFloat(newRotSpeed);
                        }
                        if (typeof newZoomSpeed != 'undefined') {
                            newZoomSpeed = parseFloat(newZoomSpeed);
                        }
                        var x = camera.position.x,
                            y = camera.position.y,
                            z = camera.position.z;

                        if (pressed == "right"){
                            camera.position.x = x * Math.cos(rotSpeed) + z * Math.sin(rotSpeed);
                            camera.position.z = z * Math.cos(rotSpeed) - x * Math.sin(rotSpeed);
                        } else if (pressed == "left"){
                            camera.position.x = x * Math.cos(rotSpeed) - z * Math.sin(rotSpeed);
                            camera.position.z = z * Math.cos(rotSpeed) + x * Math.sin(rotSpeed);
                        } else if (pressed == "up"){
                            camera.position.y = y * Math.cos(rotSpeed) - z * Math.sin(rotSpeed);
                            camera.position.x = x * Math.cos(rotSpeed) + y * Math.sin(rotSpeed);
                            camera.position.z = z * Math.cos(rotSpeed) + y * Math.sin(rotSpeed);
                        } else if (pressed == "down"){
                            camera.position.y = y * Math.cos(rotSpeed) + z * Math.sin(rotSpeed);
                            camera.position.x = x * Math.cos(rotSpeed) - y * Math.sin(rotSpeed);
                            camera.position.z = z * Math.cos(rotSpeed) - y * Math.sin(rotSpeed);
                        } else if (pressed == "in"){
                            camera.fov = camera.fov - zoomSpeed;
                            camera.updateProjectionMatrix();
                        } else if (pressed == "out"){
                            camera.fov = camera.fov + zoomSpeed;
                            camera.updateProjectionMatrix();
                        }
                        meshx.lookAt(camera.position);
                        meshy.lookAt(camera.position);
                        meshz.lookAt(camera.position);
                        for (i in threeDTicks){
                            threeDTicks[i].lookAt(camera.position);
                        }
                        camera.lookAt(new THREE.Vector3(-30*dpi,-40*dpi,0));

                    }
                    function init() {
                        camera.position.set(150*dpi, 150*dpi, 150*dpi);
                        camera.lookAt(new THREE.Vector3(-30*dpi,-40*dpi,0));

                        renderer.setSize( canvas.width()*dpi, canvas.height()*dpi );
                        canvas.append( renderer.domElement );

                        canvas.children('canvas').css('width', canvas.width());
                        canvas.children('canvas').css('height', canvas.height());

                        var keyLight = new THREE.DirectionalLight(0xFFFFFF);
                        keyLight.position.x = -100*dpi;
                        keyLight.position.y = 20*dpi;
                        keyLight.position.z = 0*dpi;
                        keyLight.castShadow = true;
                        keyLight.shadowDarkness = 0.2;
                        keyLight.shadowCameraVisible = true;
                        keyLight.target.position.set( camera.position );
                        camera.add(keyLight);

                        var fillLight = new THREE.DirectionalLight(0xFFFFFF);
                        fillLight.position.x = 100*dpi;
                        fillLight.position.y = 20*dpi;
                        fillLight.position.z = -100*dpi;
                        fillLight.castShadow = true;
                        fillLight.shadowDarkness = 0.2;
                        fillLight.shadowCameraVisible = true;
                        fillLight.target.position.set( camera.position );
                        camera.add(fillLight);

                        // X-Axis
                        var gridHelper = makeGrid(100*dpi, 1*dpi, 3*dpi, 200/tickmarks*dpi, 0x000000, 0x000000, 0*dpi, 0*dpi,'x');
                        gridHelper.position.x = 0*dpi;
                        gridHelper.position.y = -100*dpi;
                        gridHelper.position.z = 100*dpi;
                        scene.add(gridHelper);
                        meshx = new THREE.Mesh(
                                new THREE.TextGeometry('a*', {size: 9*dpi, font: "helvetiker", weight: "normal", style : "normal", material : 0, extrudeMaterial: 1}),
                                new THREE.MeshFaceMaterial(
                                        [new THREE.MeshBasicMaterial({ color: 0x000000 }),
                                        new THREE.MeshBasicMaterial({ color: 0x555555 })]
                                )
                        );
                        meshx.geometry.computeBoundingBox();
                        meshx.updateMatrix();
                        meshx.position.x = 0*dpi;
                        meshx.position.y = -130*dpi;
                        meshx.position.z = 130*dpi;
                        meshx.lookAt(camera.position);
                        scene.add(meshx);

                        // Y-Axis
                        var gridHelper2 = makeGrid(100*dpi, 1*dpi, 3*dpi, 200/tickmarks*dpi, 0x000000, 0x000000, 0*dpi, 0*dpi,'y');
                        gridHelper2.position.x = 100*dpi;
                        gridHelper2.position.y = 0*dpi;
                        gridHelper2.position.z = -100*dpi;
                        gridHelper2.rotation.x = -Math.PI / 2;
                        gridHelper2.rotation.y = Math.PI / 2;
                        scene.add(gridHelper2);
                        meshy = new THREE.Mesh(
                                new THREE.TextGeometry('L*', {size: 9*dpi, font: "helvetiker", weight: "normal", style : "normal", material : 0, extrudeMaterial: 1}),
                                new THREE.MeshFaceMaterial(
                                        [new THREE.MeshBasicMaterial({ color: 0x000000 }),
                                        new THREE.MeshBasicMaterial({ color: 0x555555 })]
                                )
                        );
                        meshy.geometry.computeBoundingBox();
                        meshy.updateMatrix();
                        meshy.position.x = -120*dpi;
                        meshy.position.y = 0*dpi;
                        meshy.position.z = 160*dpi;
                        meshy.lookAt(camera.position);
                        scene.add(meshy);

                        var gridHelper3 = makeGrid(100*dpi, 1*dpi, 3*dpi, 200/tickmarks*dpi, 0x000000, 0x000000, 0*dpi, 0*dpi,'z');
                        gridHelper3.position.x = -100*dpi;
                        gridHelper3.position.y = 0*dpi;
                        gridHelper3.position.z = 100*dpi;
                        gridHelper3.rotation.z = Math.PI / 2;
                        scene.add(gridHelper3);
                        meshz = new THREE.Mesh(
                                new THREE.TextGeometry('b*', {size: 9*dpi, font: "helvetiker", weight: "normal", style : "normal", material : 0, extrudeMaterial: 1}),
                                new THREE.MeshFaceMaterial(
                                        [new THREE.MeshBasicMaterial({ color: 0x000000 }),
                                        new THREE.MeshBasicMaterial({ color: 0x555555 })]
                                )
                        );
                        meshz.geometry.computeBoundingBox();
                        meshz.updateMatrix();
                        meshz.position.x = 130*dpi;
                        meshz.position.y = -130*dpi;
                        meshz.position.z = 0*dpi;
                        meshz.lookAt(camera.position);
                        scene.add(meshz);
                        for (i in threeDTicks){
                            scene.add(threeDTicks[i]);
                        }
                        scene.add(camera);
                    }

                    $(document).on('keydown' , function(e) {
                        if($("input,textarea").is(":focus")){
                            return;
                        }
                        if (!e) { e = window.e; }
                        var code = e.keyCode;
                        if (e.charCode && code == 0) { code = e.charCode; }
                        switch(code) {
                            case 33: // Key Page Up.
                                checkRotation('in')
                                break;
                            case 34: // Key Page Down.
                                checkRotation('out')
                                break;
                            case 37: // Key left.
                                checkRotation('left')
                                break;
                            case 38: // Key up.
                                checkRotation('up')
                                break;
                            case 39: // Key right.
                                checkRotation('right')
                                break;
                            case 40: // Key down.
                                checkRotation('down')
                                break;
                        }
                        if (code >=37 && code <= 40 || code >=33 && code <= 34) {
                            e.preventDefault();
                            requestAnimationFrame( render );
                        }

                    });
                    function getRelativePos(value,max,min,offset) {
                        return (100 * (value-min) / (max-min) /* Percentage */
                                + offset /* offset by */);
                        //return (<?php echo $useGraph;?> * dpi * 2 * (value - min + offset) * multiplier / (max - min) + <?php echo 2* (100-$useGraph);?> * dpi) - 100 * dpi;
                    }

                    function render() {
                        renderer.render( scene, camera );
                    }
                    init();

                    var ancestryScene = [];
                    ancestryScene['other'] = new THREE.Object3D();
                    ancestryScene['hispanic'] = new THREE.Object3D();
                    ancestryScene['african american'] = new THREE.Object3D();
                    ancestryScene['east asian'] = new THREE.Object3D();
                    ancestryScene['south asian'] = new THREE.Object3D();
                    ancestryScene['european'] = new THREE.Object3D();
                    <?php foreach ($data as $key => $val) {?>

                        y = getRelativePos(<?php echo $val['total_l']?>,<?php echo $actualMaxMin['max']['total_l'];?>,<?php echo $actualMaxMin['min']['total_l'];?>,<?php echo $shift['x']; ?>) * 2 * dpi - 100 * dpi;
                        x = getRelativePos(<?php echo $val['total_a']?>,<?php echo $actualMaxMin['max']['total_a'];?>,<?php echo $actualMaxMin['min']['total_a'];?>,<?php echo $shift['y']; ?>) * 2 * dpi - 100 * dpi;
                        z = -getRelativePos(<?php echo $val['total_b']?>,<?php echo $actualMaxMin['max']['total_b'];?>,<?php echo $actualMaxMin['min']['total_b'];?>,<?php echo $shift['z']; ?>) * 2 * dpi + 100 * dpi;
                        rgb = rgbToHex(CILabtoRGB(<?php echo $val['total_l']?>,<?php echo $val['total_a']?>,<?php echo $val['total_b']?>));
                        addPlotAt(x, y, z, '<?php echo strtolower($val['ancestry']);?>', rgb, ancestryScene['<?php echo strtolower($val['ancestry']);?>']);
                    <?php } ?>
                    scene.add(ancestryScene['other'] );
                    scene.add(ancestryScene['hispanic'] );
                    scene.add(ancestryScene['african american'] );
                    scene.add(ancestryScene['east asian'] );
                    scene.add(ancestryScene['south asian'] );
                    scene.add(ancestryScene['european'] );
                    render();


                    function getLabelText(graphid, axis, stepidx, totalSteps) {
                        switch (graphid + axis) {
                            case 'xx': // a vs b* / a*
                                totalSteps++;
                                var min = <?php echo $actualMaxMin['min']['total_a'];?>;
                                var step = yStep * totalSteps/10;
                                break;
                            case 'xy': // a vs b* / b*
                                totalSteps++;
                                stepidx++;
                                var min = <?php echo $actualMaxMin['max']['total_b'];?>;
                                var step = -zStep * totalSteps/10;
                                break;
                            case 'yx': // L vs b* / L*
                                totalSteps++;
                                var min = <?php echo $actualMaxMin['min']['total_l'];?>;
                                var step = xStep * totalSteps/10;
                                break;
                            case 'yy': // L vs b* / b*
                                totalSteps++;
                                stepidx++;
                                var min = <?php echo $actualMaxMin['max']['total_b'];?>;
                                var step = -zStep * totalSteps/10;
                                break;
                            case 'zx':// L vs a* / L
                                totalSteps++;
                                var min = <?php echo $actualMaxMin['min']['total_l'];?>;
                                var step = xStep * totalSteps/10;
                                break;
                            case 'zy':// L vs a* / a*
                                totalSteps++;stepidx++;
                                var min = <?php echo $actualMaxMin['max']['total_a'];?>;
                                var step = -yStep * totalSteps/10;
                                break;
                            case 'x': // L
                                totalSteps +=2;
                                var min = <?php echo $actualMaxMin['min']['total_l'];?>;
                                var step = xStep;
                                stepidx = (stepidx + Math.pow(totalSteps,2) *dpi)/(dpi*totalSteps)-<?php echo 2*(100-$useGraph); ?>/totalSteps;
                                return min + step* stepidx/2;
                                break;
                            case 'y': // a*
                                totalSteps +=2;
                                var min = <?php echo $actualMaxMin['min']['total_a'];?>;
                                var step = yStep;
                                stepidx = (stepidx + Math.pow(totalSteps,2) *dpi)/(dpi*totalSteps)-<?php echo 2*(100-$useGraph); ?>/totalSteps;
                                return min + step* stepidx/2;
                                break;
                            case 'z': // b*
                                totalSteps +=2;
                                var max = <?php echo $actualMaxMin['max']['total_b'];?> - zStep;
                                var step = zStep;
                                stepidx = (stepidx + Math.pow(totalSteps,2) *dpi)/(dpi*totalSteps)-<?php echo 2*(100-$useGraph); ?>/totalSteps;
                                return max - step * stepidx / 2;
                                break;
                        }
                        return Math.floor(step * stepidx  + min  - Math.abs(step));
                    }
                    $('#chart').bind('mousewheel' , function (e) {
                        var delta = 0;
                        if (!e) {
                            e = window.event;
                        }
                        if (e.originalEvent.wheelDelta) {
                            delta = e.originalEvent.wheelDelta/120;
                        } else if (e.originalEvent.detail) {
                            delta = -e.originalEvent.detail/3;
                        } else if (e.detail) {
                            delta = -e.detail/3;
                        }
                        if (delta > 0) {
                            checkRotation('in',0.01,30);
                        } else if (delta < 0) {
                            checkRotation('out',0.01,30);
                        }
                        e.preventDefault();
                        requestAnimationFrame( render );
                    });

                    var isMobile   = 'ontouchstart' in document.documentElement;
                    var startX = $('#chart').width()/2;
                    var startY = $('#chart').height()/2;
                    var endX = 0;
                    var endY = 0;
                    var isDragging = false;

                    var offsetX = $('#chart').parent().offset().left;
                    var offsetY = $('#chart').parent().offset().top;

                    $(window).resize(function() {
                        offsetX = $('#chart').parent().offset().left;
                        offsetY = $('#chart').parent().offset().top;
                    });


                    function MouseDown(e) {
                        if (!e) { e = window.e; }
                        if(e.which == 1) {
                            e.preventDefault();
                            endX= e.pageX - offsetX;
                            endY= e.pageY - offsetY;
                            isDragging = true;
                            mouseRender();
                        }
                    }
                    function MouseMove(e) {
                        if (!e) { e = window.e; }
                        if(!isDragging){
                            return;
                        } else {
                            e.preventDefault();
                            endX = e.pageX - offsetX;
                            endY = e.pageY - offsetY;
                        }
                    }
                    function MouseUp(e) {
                        if (!e) { e = window.e; }
                        if(!isDragging){
                            return;
                        } else {
                            e.preventDefault();
                            isDragging=false;
                        }
                    }
                    function mouseRender() {
                        if (isDragging) {
                            if (endX > startX) {
                                checkRotation('left',0.01,30);
                            } else if (endX < startX) {
                                checkRotation('right',0.01,30);
                            }
                            if (endY > startY) {
                                checkRotation('down',0.01,30);
                            } else if (endY < startY) {
                                checkRotation('up',0.01,30);
                            }
                            requestAnimationFrame( render );
                            requestAnimationFrame(mouseRender);
                        }
                    }
                    function polygon(ctx, x, y, radius, sides, startAngle, anticlockwise) {

                        if (sides < 3) return;
                        var a = (Math.PI * 2)/sides;
                        a = anticlockwise?-a:a;
                        ctx.save();
                        ctx.translate(x,y);
                        ctx.rotate(startAngle);
                        ctx.moveTo(radius,0);
                        for (var i = 1; i < sides; i++) {
                            ctx.lineTo(radius*Math.cos(a*i),radius*Math.sin(a*i));
                        }
                        ctx.closePath();
                        ctx.restore();
                        }

                    if (isMobile) {
                        $('#chart').on('touchstart',  function (e) { MouseDown(e); });
                        $('#chart').on('touchmove',   function (e) { MouseMove(e); });
                        $('#chart').on('touchend',    function (e) { MouseUp(e); });
                        $('#chart').on('touchcancel', function (e) { MouseUp(e); });
                    } else {
                        $('#chart').mousedown(        function(e) { MouseDown(e); });
                        $('#chart').mousemove(        function(e) { MouseMove(e); });
                        $('#chart').mouseup(          function(e) { MouseUp(e); });
                        $('#chart').mouseout(         function(e) { MouseUp(e); });
                    }
                });
                </script>

                <div id="layers" class="clearfix">
                    <div id="chart" width="<?php echo CANVAS_WIDTH; ?>" height="<?php echo CANVAS_HEIGHT; ?>">
                    </div>

                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </body>
</html>
