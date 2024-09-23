<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use EdwardsEyes\inc\required;
$required = new required();
$required->init();
$addPath = function ($path) {
    return dirname(__DIR__) . "/app/{$path}.php";
};


if (trim(strtolower(getenv('MAINTENANCE_MODE'))) === 'true') {
    include $addPath('maintenance');
    return;
}

$destinationParts = pathinfo(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$destination = implode('/', array_filter([ltrim($destinationParts['dirname'], '/'), $destinationParts['filename']]));

$existingFiles = [
    'admin/dashboard',
    'admin/index',
    'admin/studies',
    'admin/studies/graph2d',
    'admin/studies/graph3d',
    'admin/studies/index',
    'admin/users/index',
    'admin/users/add',
    'admin/users',
    'login',
    'logout',
    'admin/makepass',
    'admin/whichStudy',
    'admin/users/self',
    'admin/studies/edit',
    'admin/studies/run',
    'admin/studies/reindex',
    'admin/studies/colour',
    'admin/studies/wedges',
    'admin/studies/viewImages',
    'admin/studies/addImages',
    'admin/studies/report',
    'admin/studies/graph',
    'categorize',
    'instructions',
];

if (in_array($destination, $existingFiles, true)) {
    if (is_readable(dirname(__DIR__) . "/app/{$destination}.php") === true) {
        include_once $addPath($destination);
    } elseif (is_readable(dirname(__DIR__) . "/app/{$destination}/index.php") === true) {
        include_once $addPath("{$destination}/index");
    }
    return;
}
include_once $addPath('index');