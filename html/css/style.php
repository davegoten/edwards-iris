<?php
session_cache_limiter('must-revalidate');
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
use EdwardsEyes\inc\required;
(new required)->init();

header('Content-type: text/css');
header("X-Content-Type-Options: nosniff");
// Dave's IDE Hack to correctly colour this document
if (false) {?><style><?php }?>@CHARSET "ISO-8859-1";

<?php /* General Site Styles */ ?>
body {
    background-color: #CCCCCC;
    font-family: calibri, arial, sans-serif;
}
h1 {
    text-align: center;
    font-size: 36pt;
    margin: 0 0 24px 0;
}
.wrapper {
    padding: 0;
    width: <?php echo SITE_WIDTH; ?>px;
    margin: 0 auto;
    background-color: #FFFFFF;
}
.content {
    padding: 10px 25px;
    margin: 0;
    min-height: 800px;
}
.content.relative {
    position: relative;
}
.spacer {
    height: 80px;
}
.bottom {
    position: absolute;
    bottom: 10px;
    right: 10px;
}
.eyedrop {
    border-radius: 45px;
    border: 15px ridge #40230D;
    border-bottom-color: #4E5E6B;
    border-left-color: #4E5E6B;
}
#contactToggle {
    text-decoration: underline;
    cursor: pointer;
}
#contctme {
    width: 290px;
    margin: 0 auto;
    display: none;
}
#contctme label {
    width: 125px;
    float: left;
    clear: both;
    margin: 5px 0;
}
#contctme input[type="text"],#contctme input[type="email"], #contctme input[type="submit"], #contctme textarea{
    width: 150px;
    float: right;
    margin: 5px 0;
}

.clearfix:after {
    content: " "; /* Older browser do not support empty content */
    visibility: hidden;
    display: block;
    height: 0;
    clear: both;
}
<?php /* Instruction Styles */ ?>
.instructions div {
    display: none;
}

<?php /* Cavas Styles */ ?>
#layers {
    position: relative;
    width: <?php echo CANVAS_WIDTH; ?>px;
    height: <?php echo CANVAS_HEIGHT; ?>px;
    float: right;
}
#layers canvas {
    position: absolute;
    padding: 0;
    margin: 0;
    cursor: pointer;
    top: 0;
    left: 0;
}
#sauron {
    background-repeat: no-repeat;
    background-size: contain;
    background-position: center;
    z-index: 1;
}
#mymap {
    z-index: 5;
}
#inspector {
    display: none;
    position: absolute;
    z-index: 9;
    border: 3px #000000 solid;
    top: 0;
    left: 0;
    border-radius: 125px;
}
#blackgate {
    z-index: 10;
}

<?php /* Tabular Data styles */ ?>
#dataset, #debug {
    display: none;
}
#plotPoints {
    display: block;
    position: absolute;
    top: 18px;
    float: left;
    max-height: <?php echo CANVAS_HEIGHT; ?>px;
    overflow-y: scroll;
    width: <?php echo SITE_WIDTH - CANVAS_WIDTH - 55; ?>px;
}
table tr:nth-child(2n) {
    background-color: #CCCCCC;
}
table tr:nth-child(2n+1) {
    background-color: #DDDDDD;
}
table tr:nth-child(n+2):hover {
    background-color: #00FFFF;
}
table tr td, table tr th {
    padding: 4px 10px;
}
table tr td:first-child {
    text-align: center;
}

.pseudoLink {
    text-decoration: none;
    cursor: pointer;
}
.pseudoLink:hover {
    text-decoration: underline;
}

<?php /* Buttons and navigation */ ?>
.nav {
    display: block;
    text-align: right;
}
.nav a {
    font-size: 8pt;
}
#navigation {
    padding-top: 15px;
}
.button {
    color: #FFFFFF;
    font-size: 14pt;
    font-family: sans-serif;
    border: 2px #1e57FF solid;
    padding : 8px 27px;
    border-radius: 8px;
    background: #7db9e8; /* Old browsers */
    background: linear-gradient(to bottom,  #7db9e8 0%,#1e5799 100%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=0 ); /* IE6-9 */
    float: right;
    cursor: pointer;
    margin-left: 15px;
}
.button:hover {
    background: #1e5799; /* Old browsers */
    background: linear-gradient(to bottom,  #1e5799 0%,#7db9e8 100%); /* W3C */
}

#errors, .errors {
    color: #FF0000;
}
ul.errors {
    list-style: none;
}

<?php /* Admin Area */?>
#login {
    width: 400px;
    margin: 200px auto;
    border: 3px solid #000000;
    border-radius: 9px;
    padding: 40px;
}
#login input, #login select, #login label, #login span {
    display: block;
    margin-bottom: 5px;
    padding: 5px;
}
#login label {
    width: 124px;
    float: left;
    clear: both;
    white-space: nowrap;
}
#login input, #login span, #login select {
    border: 1px #A9A9A9 solid;
    width: 60%;
    float: right;
    box-sizing: content-box;
}
#login span input.innerButton {
    margin: 0;
    width: 40%;
    padding: 2px;
}

.required:before {
    content: "*"; /* Older browser do not support empty content */
    font-size: 8pt;
    display: inline;
    color: #FF0000;
    vertical-align: super;
}


#dashboardActions {
    width: 700px;
    margin: 0 auto;
}

#dashboardActions li {
    float: left;
    list-style-type: none;
    margin: 10px;
    padding: 25px;
    border: 3px #777 outset;
    border-radius: 20px;
    width: 140px;
    text-align: center;
}
#dashboardActions li:nth-child(3n+1) {
    clear: left;
}
#dashboardActions li.last-child {
    clear: both;
    margin-top:  100px;
    margin-left: 226px;
}
#dashboardActions li:hover {
    border-color: #09DC06;
    background: #7db9e8; /* Old browsers */
    background: linear-gradient(to bottom, #7db9e8 0%,#1e5799 100%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=0 ); /* IE6-9 */
}
#dashboardActions li a {
    display: block;
    font-weight: bold;
    color: #000;
    text-decoration: none;
    min-height: 57px
}
#dashboardActions li:hover a {
    color: #FFF;
    text-decoration: underline;
}

<?php if (false) {?></style><?php }?>
