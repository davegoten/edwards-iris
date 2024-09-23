<?php
namespace EdwardsEyes;

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
            <div class="content relative">
                <h1>Edwards Iris Categorization System</h1>
                <?php include_once SERVER_ROOT . '/inc/messages.php';?>
                <h2 class="errors">The site is currently under maintenance mode, it will return shortly. Thank you for your patience</h2>
                <p style="text-align: center">
                    <img src="images/eyedrop.jpg" alt="Edwards Iris Categorization System" class="eyedrop"/>
                </p>
                <p>This web application was written to categorize iridial structure and gather colour data using user defined input on a large samples of irises.</p>
                <p>It was designed for use with the paper <em>"Iris pigmentation as a quantitative trait: variation in populations of European, East Asian and South Asian ancestry and association with candidate gene polymorphisms"</em>.</p>
                <p>To set up an account please <a href="mailto:david@davidcha.com">contact me</span>.</p>
            </div>
        </div>
    </body>
</html>
