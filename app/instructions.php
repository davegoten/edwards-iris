<?php
namespace EdwardsEyes;


?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Instructions | <?php echo SITE_TITLE; ?></title>
        <link rel="stylesheet" href="<?php echo ROOT_FOLDER;?>/css/style.php" type="text/css" />
        <script src="<?php echo ROOT_FOLDER;?>/js/jquery.min.js"></script>
        <script src="<?php echo ROOT_FOLDER;?>/js/script.php"></script>
<?php
include_once SERVER_ROOT . '/inc/analytics.php';
?>
    </head>
    <body>
        <div class="wrapper">
            <div class="content relative">
                <?php include_once SERVER_ROOT . '/inc/nav.php';?>
                <h1>Edwards Iris Categorization System - Instructions</h1>
                <h2>Initializing a study</h2>
                <p>At this time setting up a new study is done manually by the server admin. For the purposes of this guide we'll assume that your study has been set up, with you as the Coordinator of your study and your images have been uploaded into your study.</p>
                <p>We begin by entering the "Manage Studies" section from the dashboard, and select "Run a particular study". This will allow you to set a study as active. The title of the page should change to whichever study is currently active.</p>
                <p>Return to the Manage studies menu, and select "Reindex all new images". This loads all you images into your study so that they maybe categorized. A couple of number should appear, including how many images are already part of the study, how many of those images are found, and how many new images were added to the study.</p>
                <p>Your study is now ready to categorize</p>
                <p>If you'd like to add more image to your study, have the admin upload your images to whichever study you'd like, and reindex the study to add your new images</p>
                <h2>General Work Flow</h2>
                <p>After the study is setup and running, a participant (or yourself) can go and participate in the study you have access to. A random eye from your study will be displayed with instructions and overlayed images to help you along. Follow the instructions as best you can. When you click save at the end, that iris' information will be saved and will not appear again. The process will repeat until all irises from the study are exhausted.</p>
                <p>At any time the Coordinater can login, and download the data in a CSV. They can also process colour information for each completed eye, and the graphs will populate with any iris that has had it's colour readings taken.</p>
                <p>On requset the images can be deleted from the server by contacting the admin, once deleted there wont be any backups so be sure you really want to delete the images.</p>
                <p>Analyzed colour wedges can also be provided and/or deleted on request to the server admin</p>
                <h2>How do I identify a feature</h2>
                <p>The general methods and in depth descriptions of each of the features being categoized by this system can be found in the paper <a href="https://royalsocietypublishing.org/doi/10.1098/rsos.150424" target="_BLANK">Analysis of iris surface features in populations of diverse ancestry</a>.</p>
                <p>It is recommended you familiarize yourself with at least the figures found there as they describe the features in detail.</p>
                <p>Once features are identified, you'll need to interpret the data based on the column headers.</p>
                <h2>Adding a participant</h2>
                <p>You can invite a participant to the study on the dashboard under "Manage Coordinators &amp; participants"</p>
                <p>You can add a participant at any level you wish as long as it is lower than your own level. For example a Coordinator can add a Participant, but a Participant can't add another Participant.</p>
                <p>To invite a participant to categorize your study for you, you will have to contact the server Admin at this time</p>
                <p>The participant, once set up can attempt to login with the username you created. They will be asked to set a password the first time they log in.</p>
                <p>A participant's Dashboard only includes the Participate in a study section at this time</p>
                <p>Once the server admin has added your participant to your study, they can select from all the studies they have been invited to categorize and begin immedately</p>
                <p>A sample study with one stock photo eye purchased for this use, has been provided to practice on. You can try to explain any stuctures yourself and experiment with all the questions there.</p>
                <p>Once the participant is comfortable categorizing an iris, you can go back to the participate in a study menu to select your actual study.</p>
                <p>Once an eye has been fully categorized by a participant the same eye will not appear again. A counter is shown on the Participate in a study menu with the number of eyes categorized over the total number of eyes in the study.</p>
                <h2>Downloading the RAW csv report</h2>
                <p>The section marked "Download Report" in your manage my studies section will compile your data into a CSV formatted file. The max size the file can be is 255M.</p>
                <p>This file will include all sorts of RAW numbers including the reviewer who's data is represented. If you have already parsed all your colour data then this will also be included in the file.</p>
                <h2>Processing colour information</h2>
                <p>This is a long, automated task which may require you to step away from the computer.</p>
                <p>Once the iris has been categorized, an automated task will use the information that was defined by you to slice out a portion of the iris.</p>
                <p>The script will load the original image and cut out a wedge from the centre of the iris to one side. It will then slice the wedge along the edge of the collarette. Finally it will take both images, tally up any completed colour information, and define averages for the 2 wedges. It will also calculate the DeltaE between the two average colours at the same time.</p>
                <p>As there is a lot of pixel counting going on at this stage your browser may stall and a dialog may popup asking if you'd like to kill or stop this window from running. Do not kill or close this page as it will stop analyzing your iris.</p>
                <p>Once all the eyes are analyzed it will send you back to the Manage my study menu. Clicking the link again will simply return you to this menu, until new iris information is recorded without colour information.</p>
                <p>You may request to have these wedges zipped and sent to you, by asking the system admin</p>
                <h2>Graphs</h2>
                <p>The graphing feature is still somewhat experimental at this time. It will take all the colour data that you've completely analyzed in the previous section and attempt to put it on several graphs. The 2D version provides some large high-res images that you can interact with your mouse.</p>
                <p>The output images were made much larger so that you could convert them to a higher resolution photo for publication purposes relatively easily. A Save link is hidden below and you can right click and save target as, or open the link and save that image to your documents or desktop for additional editing with another editor such as photoshop, GIMP, Word or paint</p>
                <p>If the image is too large to view in your browser use your Brower's built in Zoom feature to Zoom out with CTRL + - (ctrl and minus key) or hold CTRL and mouse wheel down to zoom out. CTRL + 0 (Zero) will return your browser to the normal zoom level</p>
                <p>Each 2D plot point can be moused over for more details and participant information, but the image you download will not have any of that</p>
                <p>Downloading and Saving the 3D graph is much the same as the 2D versions. If you have a lot of data then there is a chance that the image may not be able to display. Unfortunately there's little that can be done about that. The image also make use of WebGL requires that your browser has hardware acceleration enabled.</p>
                <p>The plot points here wont tell you any participant inforamtion, but you can left click (or arrow keys) to rotate the entire graph, you can mouse wheel or page up and down to Zoom in. And finally you can right click and save the image when the angle is just right for your needs</p>
                <p>Please note that the 3D graph make use of shaders to give it a more 3D look. The colours that are represented are not as accurate for that reason. But they are plotted relavtive to each other and the overall image should be relatively acceptable. Again here the actual image has been enlarged and shrunk down for your view and publicizing pleasure. The image you download will be somewhat larger again so you can put it into your favourite image post processor as needed.</p>
                <h2>Donations</h2>
                <p>Please note that this product is being given to you as-is with some support when I have free time. I hope that you'll enjoy using this tool enough that you'd consider donating to keep the page hosted for a while longer. </p>
                <p>The site was created for one purpose and one purpose alone, to categorize eye structre and colour for Melissa Edwards. But due to popular demand, and requests by the reviewers the tool was made public so that others may use it as well. I hope that it works for you as well as it has worked for us.</p>
            </div>
        </div>
    </body>
</html>
