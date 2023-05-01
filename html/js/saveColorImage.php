<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
use EdwardsEyes\inc\required;
(new required)->init();

$post = file_get_contents('php://input');
$vars = json_decode($post);
if ($_SESSION['sessionKey'] === $vars->access) {
    $path = dirname(SERVER_ROOT) . '/html/images/studies/colours/' . $vars->study;
    if (!is_readable($path)) {
        mkdir($path);
    }
    if (file_put_contents($path . '/' . $vars->filename, base64_decode($vars->imageData))) {
        echo json_encode(array('success' => true));
        exit();
    }
}
echo json_encode(array('success' => false));