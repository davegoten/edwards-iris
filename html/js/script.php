<?php
session_cache_limiter('must-revalidate');
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
use EdwardsEyes\inc\required;
(new required)->init();

header('Content-type: application/x-javascript');
header("X-Content-Type-Options: nosniff");

$image = ROOT_FOLDER . '/images/studies/structures/'
       . @$_SESSION['studyinfo']['id'] . '/'
       . @$_SESSION['studyinfo']['filename'];
// @todo: Add Obstruction question, Y/N
// Original Picture 3043 / 2036   => 1200 / 803

// Dave's IDE Hack to correctly colour this document
if (false) {
?>
<script>
<?php
}
?>

$(document).ready(function () {
    // Environment set up - IDs for elements found in the HTML
    var tempSpace  = $('#blackgate');
    if (!tempSpace.length) {
        return;
    }

    var workspace  = tempSpace.get(0).getContext('2d');

    var irisSpace  = $('#sauron');
    var irisLayer  = irisSpace.get(0).getContext('2d');

    var zoomSpace  = $('#inspector');
    var magnifier  = zoomSpace.get(0).getContext("2d");

    var magNeeded  = [7,8,10,11];
    var pupilCent  = [3,5,6];
    var tabulate   = new Array();
    tabulate[7]    = 'greenGables';
    tabulate[10]    = 'milkyway';

    var slippery   = new Array();
    slippery[8]    = {'X':0, 'Y':0};
    slippery[9]    = {'X':0, 'Y':0};

    var questions  = new Array();
    questions[1]   = [
                        {'Question':'Is there significant obstruction of your view of the Iris?', 'Answer':true,    'response':null},
                     ];
    questions[8]   = [
                        {'Question':'Do you see any Contraction Furrows on both sides of the line?', 'Answer':true,    'response':null},
                        {'Question':'Are there any Contraction Furrows in the alpha quadrant?',     'Answer':'alpha', 'response':null},
                        {'Question':'Are there any Contraction Furrows in the beta quadrant?',      'Answer':'beta',  'response':null},
                        {'Question':'Are there any Contraction Furrows in the delta quadrant?',     'Answer':'delta', 'response':null},
                        {'Question':'Are there any Contraction Furrows in the gamma quadrant?',     'Answer':'gamma', 'response':null}
                     ];
    questions[9]   = [
                        {'Question':'Do you see any Wolfflin Nodules on both sides of the line?', 'Answer':true,    'response':null},
                        {'Question':'Are there any Wolfflin Nodules in the alpha quadrant?',     'Answer':'alpha', 'response':null},
                        {'Question':'Are there any Wolfflin Nodules in the beta quadrant?',      'Answer':'beta',  'response':null},
                        {'Question':'Are there any Wolfflin Nodules in the delta quadrant?',     'Answer':'delta', 'response':null},
                        {'Question':'Are there any Wolfflin Nodules in the gamma quadrant?',     'Answer':'gamma', 'response':null}
                     ];
    questions[11]   = [
                        {'Question':'Is there a pigmented ring on the sclera?', 'Answer':true,    'response':null},
                     ];
    questions[12]   = [
                        {'Question':'Is there spotting on the sclera?', 'Answer':true,    'response':null},
                     ];

    var isDragging = false;

    var isMobile   = 'ontouchstart' in document.documentElement;
    var firstLoad  = true;

    // Define the backgound image, the iris to categorize
    /**
     * Note could have done this a lot simplier with a simple CSS background image, but worked out as it helped with the magnifier
     */
    var currIris   = new Image();
    currIris.src = '<?php echo $image;?>';
    currIris.onload = function (img) {
        if (!img)
            img         = this;
        var imageFactor = Math.min((irisSpace.width()/currIris.width), (irisSpace.height()/currIris.height));
        var newWidth        = currIris.width * imageFactor;
        var newHeight       = currIris.height * imageFactor;
        var newLeft         = (irisSpace.width() - newWidth)/2;
        var newTop          = (irisSpace.height() - newHeight)/2;

        irisLayer.drawImage(currIris,newLeft,newTop,newWidth,newHeight);
    }

    // Initialization
    var greenGables = []; // Freckles container
    var milkyway    = []; // Universe of Crypts
    var stepUsesAnne= 7;
    var stepUsesAll = 10;

    var offsetX     = tempSpace.parent().offset().left;
    var offsetY     = tempSpace.parent().offset().top;
    var centreX     = tempSpace.width()/2;
    var centreY     = tempSpace.height()/2;
    var pupilCentreX= centreX;
    var pupilCentreY= centreY;
    var irisR       = 200;
    var collaretteR = 100;
    var pupilR      = 80;
    var twoPi       = Math.PI * 2;
    var degrees     = Math.PI / 180;
    var step        = 1;



    /**
     * Shorthand method to clear a canvas
     *
     * @param context a '2d' canvas context
     */
    function clearCanvas(context) {
        context.clearRect(0,0,tempSpace.width(),tempSpace.height());
    }

    /**
     * Quick way to get Mouse coordinates
     *
     * @param e Event
     * @return JSON Object
     */
    function getPosition(e) {
        var x, y;
        offsetX = tempSpace.parent().offset().left;
        offsetY = tempSpace.parent().offset().top;
        if (isMobile) {
            var x = e.originalEvent.touches[0].pageX;
            var y = e.originalEvent.touches[0].pageY;
        } else if (e.pageX != undefined && e.pageY != undefined) {
            x = e.pageX;
            y = e.pageY;
        } else {
            x = e.clientX;
            y = e.clientY;
        }
        return { 'X': x - offsetX, 'Y': y - offsetY };
    }

    /**
     * Draws a cross on the context canvas with a dot in the middle
     *
     * @param context  '2d' canvas context
     * @param coords   JSON Object eg: {'X': x, 'Y':y}
     * @param moreData JSON Object Override values for {'lineWidth':1, 'lineColour':'#hexstr', 'centreColour':'#hexstr', 'lineLength':200}
     */
    function drawCrossHairs(context, coords, moreData) {
        var centreRadius = 100;
        var lineLength   = 500;
        var lineWidth    = 1;
        var lineColour   = '#FF0000';
        var centreColour = '#000000';
        var centrePupil  = false;

        X = coords.X;
        Y = coords.Y;

        for (var c in moreData) {
            switch (c) {
                case "lineWidth":
                    lineWidth = moreData[c];
                    break;
                case "lineColour":
                    lineColour = moreData[c];
                    break;
                case "centreColour":
                    centreColour = moreData[c];
                    break;
                case "lineLength":
                    lineLength = moreData[c];
                    break;
                case "centreRadius":
                    centreRadius = moreData[c];
                    break;
                case 'centrePupil':
                    centrePupil = moreData[c];
                    break;
            }
        }

        clearCanvas(workspace);

        context.beginPath();
        context.lineWidth = lineWidth;
        context.strokeStyle = lineColour;
        context.lineCap = 'round';

        context.moveTo(X - lineLength/2, Y);
        context.lineTo(X + lineLength/2, Y);

        context.moveTo(X, Y - lineLength/2);
        context.lineTo(X, Y + lineLength/2);
        context.stroke();

        context.beginPath();
        context.arc(X, Y, centreRadius, 0, twoPi, false);
        context.fillStyle = centreColour;
        context.fill();
        context.stroke();

        if (centrePupil) {
            pupilCentreX = X;
            pupilCentreY = Y;
            updateDataSet('{"pupilCentrePoint":{"x":'+X+',"y":'+Y+'}}');
        } else {
            centreX = X;
            centreY = Y;
            updateDataSet('{"centrePoint":{"x":'+X+',"y":'+Y+'}}');
        }
    }

    /**
     * Draws an unfilled circle in context
     *
     * @param context  '2d' canvas context
     * @param coords   JSON Object eg: {'X': x, 'Y':y}
     * @param moreData JSON Object Override values for {'lineWidth':1, 'lineColour':'#hexstr', 'irisRadius':1, 'collaretteRaddius':1, 'pupilRadius':1}
     */
    function drawCircle(context, coords, moreData) {
        var lineWidth          = 1;
        var lineColour         = '#FF0000';
        var isIris             = 'iris';
        var highlight          = false;

        X = coords.X;
        Y = coords.Y;

        for (var c in moreData) {
            switch (c) {
                case "lineWidth":
                    lineWidth = moreData[c];
                    break;
                case "lineColour":
                    lineColour = moreData[c];
                    break;
                case "irisRadius":
                    isIris = 'iris';
                    break;
                case "collaretteRadius":
                    isIris = 'collarette';
                    break;
                case "pupilRadius":
                    isIris = 'pupil';
                    break;
                case "highlight":
                    highlight = moreData[c];
            }
        }
        if (isIris =='pupil' || isIris =='collarette') {
            cX = pupilCentreX;
            cY = pupilCentreY;
        } else {
            cX = centreX;
            cY = centreY;
        }
        if (X == cX && Y == cY) {
            if (isIris =='iris') {
                radius = irisR;
            } else if (isIris =='pupil') {
                radius = pupilR;
            } else {
                radius = collaretteR;
            }
        } else {
            radius = Math.sqrt( Math.pow((cX-X), 2) + Math.pow((cY-Y), 2) )/1;
        }

        radius = radius;
        if (!highlight){
            clearCanvas(workspace);
        }
        context.beginPath();
        context.lineWidth = lineWidth;
        context.strokeStyle = lineColour;
        context.arc(cX, cY, radius, 0, twoPi, false);
        context.stroke();
        if (!highlight){
            if (isIris == 'iris') {
                irisR = radius;
                updateDataSet('{"irisRadius":{"r":'+radius+'}}');
            } else if (isIris =='pupil') {
                pupilR = radius;
                updateDataSet('{"pupilRadius":{"r":'+radius+'}}');
            } else  {
                collaretteR = radius;
                updateDataSet('{"collaretteRadius":{"r":'+radius+'}}');
            }
        }

    }

    /**
     * Create a curve that is centred on the mouse, opens towards a defined fixed point.
     *
     * @param context      '2d' canvas context
     * @param originCoords JSON Object eg: {'X': x, 'Y':y}
     * @param coords       JSON Object eg: {'X': x, 'Y':y}
     * @param moreData     JSON Object Override values for {'fixedPos':1, 'pointColour':'#hexstr', 'radius':15, 'curveDeg':75}
     */
    function createSunflowerPetal(context, originCoords, coords, more) {
        var origin      = originCoords;
        var plotR       = {X:0, Y:0}
        var radius      = 15;
        var curveDeg    = 65;
        var pointColour = "#0000FF";
        var quadrant    = 'gamma';
        var distance    = 0;
        var fixedPos    = false;
        var lineWidth   = 3;

        for (var c in more) {
            switch (c) {
                case "fixedPos":
                    fixedPos = more[c];
                    break;
                case "pointColour":
                    pointColour = more[c];
                    break;
                case "radius":
                    radius = more[c];
                    break;
                case "curveDeg":
                    curveDeg = more[c];
                    break;
                case "lineWidth":
                    lineWidth = more[c];
                    break;
            }
        }
        /* Based on the Star Trek Galxy Map
         *
         *          |
         *   gamma  |  delta
         *          |
         * ---------+---------
         *          |
         *   alpha  |  beta
         *          |
         */

        if (coords.X >= originCoords.X && coords.Y <= originCoords.Y) {
            quadrant = 'delta';
        } else if(coords.X >= originCoords.X && coords.Y >= originCoords.Y) {
            quadrant = 'beta';
        } else if (coords.X < originCoords.X && coords.Y >= originCoords.Y) {
            quadrant = 'alpha';
        }

        var deltaY = coords.Y-originCoords.Y;
        var deltaX = coords.X-originCoords.X;

        // Prevent Divide by 0 if slope is infinte
        if (deltaX == 0) {
            distance = deltaY;
            plotR.X    = originCoords.X;
            switch(quadrant) {
                case 'beta':
                case 'alpha':
                    plotR.Y    = coords.Y - radius;
                    break;
                case 'delta':
                case 'gamma':
                default:
                    plotR.Y    = coords.Y + radius;
                    break;
            }
        } else {
            /**     _______________________
             * d = √(x2 - x1)² + (y2 - y1)²
             */
            distance = Math.sqrt(Math.pow(deltaX,2)+Math.pow(deltaY,2));
            // m = (y2 - y1) / (x2 - x1)
            slope    = deltaY / deltaX;
            delta    = radius / (Math.sqrt(1 + Math.pow(slope,2)));

            // Coordinates are slightly differnt based on relative position form the origin
            switch(quadrant) {
                case 'delta':
                    plotR.Y    = coords.Y - slope * delta;
                    plotR.X    = coords.X - delta;
                    break;
                case 'beta':
                    plotR.Y    = coords.Y - slope * delta;
                    plotR.X    = coords.X - delta;
                    break;
                case 'alpha':
                    plotR.Y    = coords.Y + slope * delta;
                    plotR.X    = coords.X + delta;

                    break;
                case 'gamma':
                default:
                    plotR.Y    = coords.Y + slope * delta;
                    plotR.X    = coords.X + delta;
                    break;
            }
        }

        // If the fixed Position is provided, the rotation is applied, but the point is always draw at the same spot
        // For use with the magnifier layer
        if (fixedPos) {
            plotR.X = fixedPos.X;
            plotR.Y = fixedPos.Y;
        }

        // The starting position is actually the position that should line up with the mouse position, on the curve.
        startAngle = Math.acos(deltaX / distance);

        context.beginPath();
        context.lineCap = 'round';
        context.lineWidth   = lineWidth;
        context.strokeStyle = pointColour;
        if (quadrant == 'delta' || quadrant == 'gamma') startAngle = -startAngle;

        context.arc(plotR.X, plotR.Y, radius, startAngle - (curveDeg * degrees /2 ), startAngle + (curveDeg * degrees / 2), false);

        context.stroke();
    }

    /**
     * Not so simple way to define the line between small and large. As decided upon, the boundary that is 50% between the edge of the Iris
     * and the edge of the collarette define bigness. However both circles have potentially different origin points. Thus the boundry line
     * becomes an elipse. This function simply caluclates the distances from both origin points, and finds the distance to the 50% marker
     *
     * @param coords   JSON Object eg: {'X': x, 'Y':y}
     * @return int     -2 for inside the pupil, -2 for inside the collarette, 0 for outside the iris, 1 for small, 2 for large
     */
    function isItSmall(coords) {
        /**     _______________________
         * d = √(x2 - x1)² + (y2 - y1)²
         */
        var dCentres         = Math.sqrt(Math.pow(centreX - pupilCentreX,2)+Math.pow(centreY - pupilCentreY,2));
        var irisRadius       = irisR;
        var collaretteRadius = collaretteR;
        var pupilRadius      = pupilR;

        var dPointIris       = Math.sqrt(Math.pow(centreX - coords.X,2)+Math.pow(centreY - coords.Y,2));
        var dPointPupil      = Math.sqrt(Math.pow(pupilCentreX - coords.X,2)+Math.pow(pupilCentreY - coords.Y,2));
        /**
         * Ɵ = arccos((dCentres)² + (dPointPupil)² - (dCentres)² / (2 * dCentres * dPointPupil))
         */
        var sharedAngle      = Math.acos((Math.pow(dCentres,2) + Math.pow(dPointPupil,2) - Math.pow(dPointIris,2)) / (2 * dCentres * dPointPupil));
        /**
         * γ = arcsin(dCentres * sine(sharedAngle) / irisRadius)
         */
        var nextAngle        = Math.asin(dCentres * Math.sin(sharedAngle) / irisRadius);
        // β = 180 - Ɵ - γ
        var lastAngle        = Math.PI - sharedAngle - nextAngle;
        // x = | sin(β) * irisRadius / sin(Ɵ)) |
        var lengthIris       = Math.abs(Math.sin(lastAngle) * irisRadius / Math.sin(sharedAngle)) - collaretteRadius;
        var lengthPoint      = dPointPupil - collaretteRadius;


        if (dPointPupil < pupilRadius) {
            // Inside the Pupil
            return -2;
        } else if (dPointPupil < collaretteRadius) {
            // Inside the collarette
            return -1;
        } else if (dPointIris > irisRadius) {
            // Outside the Iris
            return 2;
        } else if (lengthPoint > lengthIris/2) {
            // Between the Iris and the collarette, but on the outer 50% away from the pupil
            return 1;
        } else {
            // Between the Iris and the collarette, but on the inner 50% close to the pupil
            return 0;
        }
    }

    /**
     * Draws a simple point at a selected spot, used in nevi and crpyts
     *
     * @param context      '2d' canvas context
     * @param coords       JSON Object eg: {'X': x, 'Y':y}
     * @param moreData     JSON Object Override values for {'fixedPos':1, 'pointColour':'#hexstr', 'radius':15, 'curveDeg':75}
     */
    function drawPoint(context, coords, moreData) {
        var typeOfPoint = 'ring';
        var pointColour = '#FF0000';
        var dotSize     = 15;
        var clicks      = 'left';
        var freckle     = '';
        var highlight   = false;

        x = coords.X;
        y = coords.Y;
        for (var c in moreData) {
            switch (c) {
                case "typeOfPoint":
                    typeOfPoint = moreData[c];
                    break;
                case "pointColour":
                    pointColour = moreData[c];
                    break;
                case "dotSize":
                    dotSize = moreData[c];
                    break;
                case "clicks":
                    clicks = moreData[c];
                    break;
                case "highlight":
                    highlight = moreData[c];
                    break;
            }
        }
        if (typeOfPoint == 'arc') {
            var checkedSize = isItSmall(coords);
            context.strokeStyle = pointColour;
            if (checkedSize == 0) {
                clicks = 'fsmall';
            }
            if (checkedSize >= 0 && checkedSize <= 1) {
                context.beginPath();
                context.lineCap = 'round';
                context.lineWidth = 3;

                createSunflowerPetal(workspace,{'X':pupilCentreX, 'Y':pupilCentreY},coords,moreData);
            } else {
                // Clicked in illegal area
                return;
            }
        } else {
            var checkedSize = isItSmall(coords);
            if (checkedSize >= -1 && checkedSize <= 1) {
                context.beginPath();
                context.lineCap = 'round';
                context.lineWidth = 3;

                context.strokeStyle = pointColour;
                context.arc(x, y, dotSize/2, 0, twoPi, false);
                } else {
                // Clicked in illegal area
                return;
            }
        }
        context.stroke();

        if (!highlight) {
             freckle = $.parseJSON('{"x":' + x + ', "y":' + y + ', "size":"' + clicks + '"}');
             if (typeOfPoint == 'arc') {
                 milkyway.push(freckle);
                 updateDataSet('{"cryptPlot": ' + JSON.stringify(milkyway) + '}');
                 updatePlotPoints(freckle);
             } else {
                 greenGables.push(freckle);
                 updateDataSet('{"neviPlot": ' + JSON.stringify(greenGables) + '}');
                 updatePlotPoints(freckle);
             }
        }
    }



    function initSteps(stepNumber) {
        var table = $('#plotPoints');
        var tableRows = $('#plotPoints tr').length;
        var headings  = table.children();

        var dataSet = questions[stepNumber];
        if (headings.length <= 0) {
            headings = $('<tr></tr>');
            for (key in dataSet[0]) {
                if (key != 'response') {
                    headings.append($('<th></th>').html(key.toUpperCase()));
                }
            }
            table.append(headings);
            tableRows++;
        }
        drawGalaxy = function drawGalaxy() {
            var quadrant = $(this).closest('tr').attr('data-quadrant');
            if (['alpha','beta','delta','gamma'].indexOf(quadrant) < 0) {
                return;
            }
            workspace.save();
            workspace.globalAlpha = 0.6;
            workspace.fillStyle = "#000000";
            workspace.fillRect(0,0,tempSpace.width(),tempSpace.height());
            workspace.restore();

            workspace.fillStyle = "#0000FF";
            workspace.font = "bold 32px Arial";
            workspace.textAlign = "right";
            workspace.fillText("α - Alpha", centreX - 15, centreY + 40);
            workspace.fillStyle = "#006600";
            workspace.fillText("δ - Gamma", centreX - 15, centreY - 20);
            workspace.textAlign = "left";
            workspace.fillStyle = "#660066";
            workspace.fillText("β - Beta",  centreX + 15, centreY + 40);
            workspace.fillStyle = "#FF0000";
            workspace.fillText("γ - Delta", centreX + 15, centreY - 20);



            workspace.save();
            workspace.globalCompositeOperation = "destination-out";
            workspace.fillStyle = "#FFFFFF";
            switch (quadrant) {
                case 'gamma' :
                    workspace.fillRect(0,0,centreX,centreY);
                    break;
                case 'delta' :
                    workspace.fillRect(centreX,0,tempSpace.width(),centreY);
                    break;
                case 'beta' :
                    workspace.fillRect(centreX,centreY,tempSpace.width(),tempSpace.height());
                    break;
                case 'alpha' :
                    workspace.fillRect(0,centreY,centreX,tempSpace.height());
                    break;
                default:
                    return;
            }

            workspace.restore();

            workspace.save();
            workspace.fillStyle = "#FFFFFF";
            workspace.beginPath();
            workspace.globalCompositeOperation = "destination-in";
            workspace.arc(centreX,centreY,irisR,0,twoPi,true);
            workspace.fill();
            workspace.restore();
        }
        for (key1 in dataSet) {
                // Begin populating each row with the delete function
                data = $('<tr></tr>')
                    .attr('id','row' + tableRows)
                    .mouseenter(drawGalaxy)//"drawGalaxy('" + dataSet[key1]['Answer'] + "');")
                    .attr('data-quadrant',dataSet[key1]['Answer'])
                    .on('mouseout', function () {
                        clearCanvas(workspace);
                        firstLoad = true;
                        doActionBasedOnStep(true,{ 'X': centreX, 'Y': centreY });
                    });
                 for (key in dataSet[key1]) {
                     if (key == 'response') {
                     } else if (key == 'Answer') {
                        var selector = [
                            $('<input />')
                                .attr('type','radio')
                                .attr('name','sizeRadio['+tableRows+']')
                                .attr('id','largeRadio['+tableRows+']')
                                .attr('value','yes')
                                .change(function () {
                                    var i = parseInt($(this).attr('id').replace("largeRadio[", "").replace("]", "")) - 1;
                                    questions[step][i]['response'] = $(this).val();
                                })
                                .attr('checked',(dataSet[key1]['response'] == 'yes')),
                            $('<label />')
                                .html('Yes')
                                .attr('for','largeRadio['+ tableRows +']'),
                            $('<br />'),
                            $('<input />')
                                .attr('type','radio')
                                .attr('name','sizeRadio['+ tableRows +']')
                                .attr('id','smallRadio['+ tableRows +']')
                                .attr('value','no')
                                .change(function () {
                                    var i = parseInt($(this).attr('id').replace("smallRadio[", "").replace("]", "")) - 1;
                                    questions[step][i]['response'] = $(this).val();
                                })
                                .attr('checked',(dataSet[key1]['response'] == 'no')),
                            $('<label />')
                                .html('No')
                                .attr('for','smallRadio['+tableRows+']')
                        ];
                        data.append($('<td></td>').append(selector));
                    } else {
                        data.append($('<td></td>').html(dataSet[key1][key]));
                    }
                }
                // Add the row to the column
                table.append(data);
                tableRows++;
        }
        // Make the table line up with the canvas
        table.css('top',tempSpace.offset().top + 'px');
    }


    /**
     * Create a tabular form for all the recorded clicks, with remove and editing cabibilities
     *
     * @param originCoords JSON Object eg: {'X': x, 'Y':y}
     */
    function updatePlotPoints(dataSet) {
        var table = $('#plotPoints');
        var tableRows = $('#plotPoints tr').length;
        var headings = table.children();
        var data;

        if (typeof tabulate[step] == 'undefined') {
            // Only used in above defined steps
            return;
        }
        var whichplot = tabulate[step];

        // Method for removing exising points in tabular data
        var removeMethod = function removePoint(data) {
            var index = data.data.index;
            var dataset = data.data.plot;
            if (dataset == 'greenGables') {
                greenGables.splice(index-1,1);
                redrawPoints(workspace, {'clearFirst': true, 'highlight':true});
                for (var i in greenGables) {
                     updatePlotPoints(greenGables[i]);
                }
            } else if (dataset == 'milkyway') {
                milkyway.splice(index-1,1);
                redrawPoints(workspace, {'clearFirst': true, 'highlight':true});
                for (var i in milkyway) {
                     updatePlotPoints(milkyway[i]);
                }
            }
            return false;
        }

        // Method for making allowable changes in tabular data
        var editMethod = function editPoint(data) {
            var index = data.data.index;
            var dataset = data.data.plot;
            var newValue = data.data.value;

            if (dataset == 'greenGables') {
                greenGables[index-1].size = newValue;
                redrawPoints(workspace, {'clearFirst': true, 'highlight':true});
                for (var i in greenGables) {
                     updatePlotPoints(greenGables[i]);
                }
            } else if (dataset == 'milkyway') {
                milkyway[index-1].size = newValue;
                redrawPoints(workspace, {'clearFirst': true, 'highlight':true});
                for (var i in milkyway) {
                     updatePlotPoints(milkyway[i]);
                }
            }
            return false;
        }

        // Build Tabular data from the first row, include a Delete Column and headings
        if (headings.length <= 0) {
            headings = $('<tr></tr>');
            headings.append($('<th></th>').html('Delete Point'));
            for (key in dataSet) {
                headings.append($('<th></th>').html(key.toUpperCase()));
            }
            table.append(headings);
            tableRows++;
        }
        // Begin populating each row with the delete function
        data = $('<tr></tr>')
            .attr('id','row' + tableRows)
            .on('mouseover',function () { highlightPoint(tableRows); })
            .on('mouseout', function () {
                redrawPoints(workspace, {'clearFirst': false, 'highlight':true});
            });
        data.append($('<td></td>').append(
            $('<a />')
                .html('X')
                .addClass('pseudoLink')
                .click({"plot":whichplot,"index":tableRows},removeMethod)
        ));

        // Loop through all passed data, special cases have been added for left/right and fsmall elements
        for (key in dataSet) {
            if (key == 'size') {
                if (dataSet[key] == 'fsmall') {
                    var selector = [
                        $('<input />')
                            .attr('type','radio')
                            .attr('name','sizeRadio['+ tableRows +']')
                            .attr('id','smallRadio['+ tableRows +']')
                            .attr('value','small')
                            .attr('checked',true),
                        $('<label />')
                            .html('Small')
                            .attr('for','smallRadio['+tableRows+']')
                    ];
                } else {
                    var selector = [
                        $('<input />')
                            .attr('type','radio')
                            .attr('name','sizeRadio['+tableRows+']')
                            .attr('id','largeRadio['+tableRows+']')
                            .attr('value','large')
                            .change({"plot":whichplot,"index":tableRows,'value':'right'},editMethod)
                            .attr('checked',(dataSet[key] == 'right')),
                        $('<label />')
                            .html('Large')
                            .attr('for','largeRadio['+ tableRows +']'),
                        $('<br />'),
                        $('<input />')
                            .attr('type','radio')
                            .attr('name','sizeRadio['+ tableRows +']')
                            .attr('id','smallRadio['+ tableRows +']')
                            .attr('value','small')
                            .change({"plot":whichplot,"index":tableRows,'value':'left'},editMethod)
                            .attr('checked',(dataSet[key] == 'left')),
                        $('<label />')
                            .html('Small')
                            .attr('for','smallRadio['+tableRows+']')
                    ];
                }
                data.append($('<td></td>').append(selector));
            } else if (['x','y'].indexOf(key) >= 0) {
                data.append($('<td></td>').html(parseInt(dataSet[key])));
            } else {
                data.append($('<td></td>').html(dataSet[key]));
            }
        }

        // Add the row to the column
        table.append(data);

        // Make the table line up with the canvas
        table.css('top',tempSpace.offset().top + 'px');
    }

    /**
     * Predfined ways to highlight elements by redrawing the points on top without added additional elements
     *
     * @param index int Value of key for dataStructure[index + 1]
     */
    function highlightPoint(index) {
        if (step == stepUsesAnne && typeof greenGables[index-1] != 'undefined') {
            if (greenGables[index-1].size == 'right') {
                more = {'typeOfPoint':'ring','pointColour':'#FFFF00','dotSize':25,'clicks':'right','highlight':true};
            } else {
                more = {'typeOfPoint':'ring','pointColour':'#FFFF00','dotSize':15,'clicks':'left','highlight':true};
            }
            thisCoords = { 'X': greenGables[index-1].x, 'Y': greenGables[index-1].y };
            drawPoint(workspace, thisCoords, more);
        } else if (step == stepUsesAll && typeof milkyway[index-1] != 'undefined') {
            if (milkyway[index-1].size == 'right') {
                more = {'typeOfPoint':'arc','pointColour':'#FFFF00','clicks':'right','highlight':true};
            } else {
                more = {'typeOfPoint':'arc','pointColour':'#FFFF00','clicks':'left','highlight':true};
            }
            thisCoords = { 'X': milkyway[index-1].x, 'Y': milkyway[index-1].y };
            drawPoint(workspace, thisCoords, more);
        }

    }

    /**
     * Redraws the entire plot points
     *
     * @param context      '2d' canvas context
     * @param coords       JSON Object eg: {'X': x, 'Y':y}
     * @param moreData     JSON Object Override values for {'clearFirst':bool, 'highlight':bool, 'thisStep':Int}
     */
    function redrawPoints(context, moreData) {
        var clearFirst = true;
        var highlight  = false;
        var thisStep   = step;

        for (var c in moreData) {
            switch (c) {
                case "clearFirst":
                    clearFirst = moreData[c];
                    break;
                case "highlight":
                    highlight = moreData[c];
                    break;
                case "thisStep":
                    thisStep =  moreData[c];
            }
        }

        if (clearFirst) {
            $('#plotPoints').html('');
            clearCanvas(context);
        }

        if (thisStep == stepUsesAnne) {
            for (var i in greenGables) {
                if (greenGables[i].size == 'right') {
                    more = {'typeOfPoint':'ring','pointColour':'#0000FF','dotSize':25,'clicks':'right','highlight':highlight};
                } else {
                    more = {'typeOfPoint':'ring','pointColour':'#FF0000','dotSize':15,'clicks':'left','highlight':highlight};
                }
                thisCoords = { 'X': greenGables[i].x, 'Y': greenGables[i].y };
                drawPoint(context, thisCoords, more);
            }
        } else if (thisStep == stepUsesAll) {
            for (var i in milkyway) {
                if (milkyway[i].size == 'right') {
                    more = {'typeOfPoint':'arc','clicks':'right','highlight':highlight};
                } else {
                    more = {'typeOfPoint':'arc','clicks':'left','highlight':highlight};
                }
                thisCoords = { 'X': milkyway[i].x, 'Y': milkyway[i].y };
                drawPoint(context, thisCoords, more);

            }
        }
    }



    /**
     * Draws a ruler that rotates around the Origin
     *
     * @param context      '2d' canvas context
     * @param coords       JSON Object eg: {'X': x, 'Y':y}
     * @param moreData     JSON Object Override values for {'colour':'#hexstr', 'length':200, 'width':3}
     */
    function drawMidLine(context, coords, moreData) {
        var colour = '#FF0000';
        var length = 400;
        var width  = 2;

        for (var c in moreData) {
            switch (c) {
                case "colour":
                    colour = moreData[c];
                    break;
                case "length":
                    length = moreData[c];
                    break;
                case "width":
                    width = moreData[c];
                    break;
            }
        }

        if(firstLoad) {
            coords.X = slippery[step].X;
            coords.Y = slippery[step].Y;
        }
        if (!firstLoad && !isDragging){
            return;
        }
        firstLoad = false;
        var deltaY = coords.Y-centreY;
        var deltaX = coords.X-centreX;

        /**     _______________________
         * d = √(x2 - x1)² + (y2 - y1)²
         */
        var radius = Math.sqrt(Math.pow(deltaX,2)+Math.pow(deltaY,2));
        var plot   = {startX:0, endX:0, startY:0, endY:0}

        if (deltaX == 0) {
            // Handle divide by 0, slope of infinity
            plot.startY   = centreY + length;
            plot.endY     = centreY - length;
            plot.startX   = centreX;
            plot.endX     = centreX;
        } else {
            slope  =  deltaY / deltaX;
            delta = length / (Math.sqrt(1 + Math.pow(slope,2)));
            plot.startX = centreX + delta;
            plot.startY = centreY + slope * delta;
            plot.endX   = centreX - delta;
            plot.endY   = centreY - slope * delta;
        }
        slippery[step].X = coords.X;
        slippery[step].Y = coords.Y;

        clearCanvas(context);
        context.beginPath();
        context.lineCap     = 'round';
        context.lineWidth   = width;
        context.strokeStyle = colour;
        context.moveTo(plot.startX,plot.startY);
        context.lineTo(plot.endX,plot.endY);
        context.stroke();
    }

    /**
     * Create a hover Zoom insetad of a mouse on a specific canvas with an optional circle in the middle for sizing
     *
     * @param coords   JSON Object eg: {'X': x, 'Y':y}
     * @param moreData JSON Object Override values for {'factor':1.5, 'neviSize':25}
     */
    function zoomMouse(coords, moreData) {
        var factor         = 10;
        var width          = tempSpace.outerWidth();
        var height         = tempSpace.outerHeight();
        var neviSize       = 12;
        var lineWidth      = 2;
        var lineColour     = '#FF00FF';
        var arcSize        = 0;

        // Hide mouse pointer for further clarity
        tempSpace.css('cursor','none');

        for (var c in moreData) {
            switch (c) {
                case "factor":
                    factor = moreData[c];
                    break;
                case "neviSize":
                    neviSize = moreData[c];
                case "arcSize":
                    arcSize = moreData[c];
            }
        }
        X = coords.X;
        Y = coords.Y;

        newWidth        = tempSpace.width() * factor;
        newHeight       = tempSpace.height() * factor;
        newL            = -X * factor + zoomSpace.width()/2;
        newT            = -Y * factor + zoomSpace.height()/2;

        clearCanvas(magnifier);
        magnifier.drawImage(currIris, newL, newT, newWidth, newHeight);
        if (arcSize > 0) {
            magnifier.beginPath();
            magnifier.lineWidth = lineWidth;
            magnifier.strokeStyle = lineColour;
            magnifier.moveTo((zoomSpace.width()-neviSize)/2,zoomSpace.height()/2);
            magnifier.lineTo((zoomSpace.width()+neviSize)/2,zoomSpace.height()/2);
            magnifier.stroke();

            magnifier.beginPath();
            magnifier.moveTo(zoomSpace.width()/2,(zoomSpace.height()-neviSize)/2);
            magnifier.lineTo(zoomSpace.width()/2,(zoomSpace.height()+neviSize)/2);
            magnifier.stroke();
        } else if (neviSize > 0) {
            magnifier.beginPath();
            magnifier.lineWidth = lineWidth;
            magnifier.strokeStyle = lineColour;
            magnifier.arc(zoomSpace.width()/2, zoomSpace.height()/2, neviSize, 0, twoPi, false);
            magnifier.stroke();
        }
    }

    /**
     * Saves the data in a temproaray location in memory to be exported to a database at a later date
     *
     * @param dataset  JSON Object eg: {'variable': 'values'}
     * @todo  Make this update the database instead of temporary data so that users can stop/save/return
     */

    function updateDataSet(dataset) {
        return;
        oldDataSet = $.parseJSON($('#stepsResult').html());
        newDataSet = $.parseJSON(dataset);
        endData = $.extend(oldDataSet,newDataSet);
        $('#stepsResult').html(JSON.stringify(endData));
    }

    /**
     * Every step and click basically calls this function, it is responsible
     * for determining how to translate mouse click and keypresses into useful
     * actions
     *
     * @param e        Event Mouse/Keyboard Event object
     * @param coords   JSON Object eg: {'X': x, 'Y':y} overrides event driven coordinates
     */
    function doActionBasedOnStep(){
        e = arguments[0];
        if (!e) { e = window.e; }

        manual = arguments[1];
        if (e === true && manual) {
            coords = manual;
        } else {
            coords = getPosition(e);
        }
        moreData = {};
        $('#plotPoints').html('');

        switch (step) {
            case 1:
            initSteps(step);
            break;
            case 2:
                moreData = {'lineWidth':4, 'lineLength':500};
                drawCrossHairs(workspace, coords, moreData);
                break;
            case 3:
                moreData = {'lineWidth':4, 'lineLength':200, 'centreRadius':20,'centrePupil': true};
                drawCrossHairs(workspace, coords, moreData);
                break;
            case 4:
                moreData = {'lineWidth':4,'irisRadius':1};
                drawCircle(workspace, coords, moreData);
                break;
            case 5:
                moreData = {'lineWidth':4,'lineColour':'#FF00FF','pupilRadius':1};
                drawCircle(workspace, coords, moreData);
                break;
            case 6:
                moreData = {'lineWidth':4,'lineColour':'#0000FF','collaretteRadius':1};
                drawCircle(workspace, coords, moreData);
                break;
            case 7:
                if ( $('#plotPoints').children().length <= 0) {
                    redrawPoints(workspace, {'clearFirst': true, 'highlight': true});
                    for (var i in greenGables) {
                         updatePlotPoints(greenGables[i]);
                    }
                }
                if (e.type == 'mouseup' || e.type == 'touchstart') {
                    switch (e.which) {
                        case 3:
                            moreData = {'typeOfPoint':'ring','pointColour':'#0000FF','dotSize':25,'clicks':'right'};
                            break;
                        case 1:
                        case 2:
                        default:
                            moreData = {'typeOfPoint':'ring','pointColour':'#FF0000','dotSize':15,'clicks':'left'};
                    }
                    drawPoint(workspace, coords, moreData);
                }
                break;
            case 8:
                moreData = {'colour':'#FF0000', 'length':400, 'width':2};
                drawMidLine(workspace, coords, moreData);
                initSteps(step);
                break;
            case 9:
                moreData = {'colour':'#00FF00','length':400, 'width':2};
                drawMidLine(workspace, coords, moreData);
                initSteps(step);
                break;
            case 10:
                if ( $('#plotPoints').children().length <= 0) {
                    redrawPoints(workspace, {'clearFirst': true, 'highlight': true});
                    for (var i in milkyway) {
                         updatePlotPoints(milkyway[i]);
                    }

                }
                if (e.type == 'mouseup' || e.type == 'touchstart') {
                    switch (e.which) {
                        case 3:
                            moreData = {'typeOfPoint':'arc','clicks':'left'};
                            break;
                        case 1:
                        case 2:
                        default:
                            moreData = {'typeOfPoint':'arc','clicks':'right'};
                    }
                    drawPoint(workspace, coords, moreData);
                }
                break;
            case 11:
            case 12:
                initSteps(step);
                moreData = moreData = {'lineWidth':4, 'lineLength':0, 'centreRadius':irisR};
                drawCrossHairs(workspace, {'X':centreX,'Y':centreY}, moreData);
                break;
            case 13:
                moreData = {'lineWidth':4,'irisRadius':1,'highlight':true};
                coords = {'X':centreX, 'Y': centreY};
                drawCircle(workspace, coords, moreData);
                moreData = {'lineWidth':4,'lineColour':'#FF00FF','pupilRadius':1,'highlight':true};
                coords = {'X':pupilCentreX, 'Y': pupilCentreY};
                drawCircle(workspace, coords, moreData);
                moreData = {'lineWidth':4,'lineColour':'#0000FF','collaretteRadius':1,'highlight':true};
                drawCircle(workspace, coords, moreData);
                if ( $('#plotPoints').children().length <= 0) {
                    redrawPoints(workspace, {'clearFirst': false, 'highlight': true, thisStep: 7});
                    redrawPoints(workspace, {'clearFirst': false, 'highlight': true, thisStep: 10});
                }
                break;
            default:
                break;
        }
    }

    /**
     * Methods to be triggered when certain event occur
     */
    /**
     * On Mouse Down - Triggers the smart method doActionBasedOnStep and sets the dragging flag
     *
     * @param e Mouse Event Object
     */
    function MouseDown(e) {
        if (!e) { e = window.e; }
        e.preventDefault();
        doActionBasedOnStep(e);
        isDragging = true;
    }
    /**
     * On Mouse Move - Triggers the smart method doActionBasedOnStep if dragging flag is set (ex. When mouse is held down)
     *                 Also, if the magnifier canvas is visible, because presuably its needed on this step, make it
     *                 follow the mouse around and redraw it to the correct position
     *
     * @param e Mouse Event Object
     */

    function MouseMove(e) {
        if (!e) { e = window.e; }
        e.preventDefault();
        if (zoomSpace.is(':visible')) {
            if ([7,10].indexOf(step) < 0) {
                more = {'factor':1.5, 'neviSize': 0}
            } else if (step == stepUsesAll) {
                more = {'factor':1.5, 'arcSize': 35}
            } else {
                more = {'factor':1.5}
            }
            zoomMouse(getPosition(e), more);

            zoomSpace.css({
                left:  e.pageX -(zoomSpace.outerWidth() / 2),
                top:   e.pageY -(zoomSpace.outerHeight() / 2)
            });
        }
        if(!isDragging){
            return;
        } else {
            doActionBasedOnStep(e);
        }
    }
    /**
     * On Mouse Up - Triggers the smart method doActionBasedOnStep and clears the dragging flag
     *
     * @param e Mouse Event Object
     */

    function MouseUp(e) {
        if (!e) { e = window.e; }
        e.preventDefault();
        if(!isDragging){
            return;
        } else {
            isDragging=false;
            doActionBasedOnStep(e);
        }
    }
    /**
     * On Mouse Hover - Checks if the magnifier is needed for this step and shows it when needed
     *
     * @param e Mouse Event Object
     */

    function MouseHover(e) {
        step = parseInt($('.instructions div.steps:visible').first().attr('id'));
        if (magNeeded.indexOf(step) >= 0) {
            zoomSpace.show();
        }
    }

    // Set up Input Listeners on Mouse and Keyboard Events Disables the right click menu on the canvas also
    if (isMobile) {
        tempSpace.on('touchstart',  function (e) { MouseDown(e); });
        tempSpace.on('touchmove',   function (e) { MouseMove(e); });
        tempSpace.on('touchend',    function (e) { MouseUp(e); });
        tempSpace.on('touchcancel', function (e) { MouseUp(e); });
    } else {
        tempSpace.hover(            function(e) { MouseHover(e); });
        tempSpace.mousedown(        function(e) { MouseDown(e); });
        tempSpace.mousemove(        function(e) { MouseMove(e); });
        tempSpace.mouseup(          function(e) { MouseUp(e);   });
        tempSpace.mouseout(         function(e) { MouseUp(e); zoomSpace.hide(); $('canvas').css('cursor','pointer');});
        tempSpace.on("contextmenu", function(e) { e.preventDefault(); });
    }

    // Keyboard Events
    document.onkeydown = function(e) {
        if (!e) { e = window.e; }
        var increment = 1;

        var cX;
        var cy;
        var code = e.keyCode;
        if (e.charCode && code == 0) { code = e.charCode; }

        switch(step) {
            case 2:
                cX = centreX;
                cY = centreY;
                switch(code) {
                    case 37: // Key left.
                        doActionBasedOnStep(true,{'X':cX - increment, 'Y':cY});
                        break;
                    case 38: // Key up.
                        doActionBasedOnStep(true,{'X':cX, 'Y':cY - increment});
                        break;
                    case 39: // Key right.
                        doActionBasedOnStep(true,{'X':cX + increment, 'Y':cY});
                        break;
                    case 40: // Key down.
                        doActionBasedOnStep(true,{'X':cX, 'Y':cY + increment});
                        break;
                }
                if (code >=37 && code <= 40) {
                    e.preventDefault();
                }
                break;
            case 3:
                cX = pupilCentreX;
                cY = pupilCentreY;
                switch(code) {
                    case 37: // Key left.
                        doActionBasedOnStep(true,{'X':cX - increment, 'Y':cY});
                        break;
                    case 38: // Key up.
                        doActionBasedOnStep(true,{'X':cX, 'Y':cY - increment});
                        break;
                    case 39: // Key right.
                        doActionBasedOnStep(true,{'X':cX + increment, 'Y':cY});
                        break;
                    case 40: // Key down.
                        doActionBasedOnStep(true,{'X':cX, 'Y':cY + increment});
                        break;
                }
                if (code >=37 && code <= 40) {
                    e.preventDefault();
                }
                break;
        }
        if (code == 89 || code == 78 || code == 8) {
            if (code == 89 || code == 78) {
                $("input:radio:visible").not(':checked').each(function () {
                    value = (code == 89)?'yes':'no';
                    name = $(this).attr('name');
                    if ($("[name='"+name+"']:checked").val() == undefined) {
                        $("[name='"+name+"'][value='"+value+"']").click();
                        return false;
                    }
                });
            } else if (code == 8) {
                $($("input:radio:visible:checked").get().reverse()).each(function () {
                    name = $(this).attr('name');
                    if ($("[name='"+name+"']:checked")) {
                        $(this).prop('checked',false);
                        $(this).attr('checked',false);
                        return false;
                    }
                });
            }
            e.preventDefault();
        }

    };

    function validateStep() {
        var valid = true;
        var errors = new Array();
        $('#errors').html('');

        switch(step) {
            case 8:
            case 9:
                var crosses = (questions[step][0].response == 'yes');
                var found  = 0;
                for (var i in questions[step]) {
                    if (i != 0 && questions[step][i].response == 'yes') {
                        found++;
                    }
                }
                if (!crosses && found >= 4) {
                    errors.push("The combination of answers you've selected is Mathematically Impossible, too many quadrants are 'yes'");
                    valid = false;
                } else if (crosses && found <= 1) {
                    errors.push("The combination of answers you've selected is Mathematically Impossible, too few quadrants are 'yes'");
                    valid = false;
                }
            case 1:
            case 11:
            case 12:
                for (var i in questions[step]) {
                    if (questions[step][i].response == null) {

                        valid = false;
                        errors.push("Please answer the question: \"" + questions[step][i].Question + "\"");
                    }
                }
                break;
            case 13:
                // do Ajax Save and if good refresh Page, else return false

                var answerKey = {
                    'action'            : 'save',
                    'obstructed'        : questions[1],
                    'iris'              : {'x':centreX, 'y':centreY, 'r':irisR},
                    'pupil'             : {'x':pupilCentreX, 'y':pupilCentreY, 'r':pupilR},
                    'collarette'        : {'r':collaretteR},
                    'nevi'              : greenGables,
                    'contractionFurrows': $.extend(slippery[8],questions[8]),
                    'wolfflinNodules'   : $.extend(slippery[9],questions[9]),
                    'crypts'            : milkyway,
                    'scleraRing'        : questions[11],
                    'scleraSpots'       : questions[12],
                    'access'            : '<?php echo $_SESSION['sessionKey']; ?>'
                };


                $.ajax({
                    type       : 'POST',
                    url        : '<?php echo ROOT_FOLDER;?>/js/process.php',
                    data       : answerKey,
                    dataType   : 'json',
                    beforeSend : function() {
                        $('#navigation').hide();
                    },
                    success    : function (data) {
                        if(data['success'] == true) {
                            if (confirm("Your data has been saved!\nPress OK to continue")) {
                                location.reload();
                            } else {
                                window.location.href = '<?php echo ROOT_FOLDER ?>/admin/dashboard.php';
                            }
                        } else {
                            $('#navigation').show();
                            alert('Your data could not be saved, please go back and check your work again.');
                        }
                    }
                });

                valid = false;
                break;
            default:
                valid = true;
        }

        if (errors.length > 0) {
            var list = $("<ul />");
            for (var i in errors) {
                list.append($('<li />').html(errors[i]));
            }
            $('#errors').html(list);
        }



        return valid;
    }
    /**
     * Watch windows sizing because the drawing depends in the offsets
     */

    $(window).resize(function() {
        offsetX = tempSpace.parent().offset().left;
        offsetY = tempSpace.parent().offset().top;
    });

    /**
     * Set up click listener to progress to the previous and next stages
     */
    $('#continue').click(function () {
        var valid = validateStep();
        if (valid === true) {
            step = parseInt($('.instructions div.steps:visible').first().attr('id'));
            if ($('.instructions div.steps:visible').first().next('.steps').length > 0) {
                nextstep = parseInt($('.instructions div.steps:visible').first().next('.steps').attr("class").toString().replace("steps ", "").replace("step", "").split(' '));
            } else {
                nextstep = 1;
            }
            $('.steps').hide();
            clearCanvas(workspace);
            $('.step' + nextstep).show();
            step = parseInt($('.instructions div.steps:visible').first().attr('id'));
            firstLoad = true;
            if (pupilCent.indexOf(step) >= 0) {
                doActionBasedOnStep(true,{ 'X': pupilCentreX, 'Y': pupilCentreY });
            } else {
                doActionBasedOnStep(true,{ 'X': centreX, 'Y': centreY });
            }
        } else {
            // Message to fully complete
        }
        });
    $('#back').click(function () {
            step = parseInt($('.instructions div.steps:visible').first().attr('id'));
            if ($('.instructions div.steps:visible').first().prev('.steps').length > 0) {
                prevstep = parseInt($('.instructions div.steps:visible').first().prev('.steps').attr("class").toString().replace("steps ", "").replace("step", "").split(' '));
            } else {
                prevstep = 1;
            }
            $('.steps').hide();
            clearCanvas(workspace);
            $('.step' + prevstep).show();
            step = parseInt($('.instructions div.steps:visible').first().attr('id'));
            firstLoad = true;
            if (pupilCent.indexOf(step) >= 0) {
                doActionBasedOnStep(true,{ 'X': pupilCentreX, 'Y': pupilCentreY });
            } else {
                doActionBasedOnStep(true,{ 'X': centreX, 'Y': centreY });
            }
        });
    $('.button').click(function () {
        if (step == 1) {
            $('#back').hide();
        } else {
            $('#back').show();
        }
        if (step == 13) {
            $('#continue').val('Save »');
        } else {
            $('#continue').val("Continue »");
        }

    });

    // Initialize the page with a new crosshair - Should be [step1]
    $('.step' + step).show();
    if (step == 1) {
        $('#back').hide();
    } else {
        $('#back').show();
    }
    doActionBasedOnStep(true,{ X: centreX, Y: centreY });
});


<?php include_once('colours.js');?>


<?php
if (false) {
?>
</script>
<?php
}
