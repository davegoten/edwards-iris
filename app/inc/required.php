<?php
namespace EdwardsEyes\inc;

class required {
    public function init(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        !defined('USR') && define('USR' , getenv('MYSQL_ROOT_USER') ?: 'root');
        !defined('PWD') && define('PWD' , getenv('MYSQL_ROOT_PASSWORD') ?: 'root');
        !defined('HOST') &&  define('HOST', getenv('HOST') ?: "sqlite");
        !defined('DATABASE_PORT') && define('DATABASE_PORT', getenv('DATABASE_PORT') ?: 3306);
        !defined('DB') &&  define('DB'  , "edwardseyes");
        !defined('SALT') && define('SALT', getenv('SALT_PHRASE') ?: 'salt');
        !defined('ACL_RANKS') && define('ACL_RANKS', [
            0      => 'participant',
            10     => 'coordinator',
            20     => 'principle investigator',
            99     => 'super admin'
        ]);

        if (empty($_SESSION['sessionKey'])) {
            $_SESSION['sessionKey'] = md5('theOneKeyToRuleAllForNow' . time());
        }
        define('SITE_TITLE', 'Edwards Iris Classification System');
        define('CANVAS_WIDTH', 800);
        define('CANVAS_HEIGHT', 536);

        define('ROOT_FOLDER', 'http://'.$_SERVER['HTTP_HOST']);
        define('SERVER_ROOT', dirname(__DIR__));

        define('SITE_WIDTH', 1180);
        $destinationParts = pathinfo(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $tempVal = trim(($destinationParts['dirname'] ?? '') . '/' .  ($destinationParts['basename'] ?? 'index.php'), ' \n\r\t\v\x00/');
        if (!empty($tempVal) && !in_array(
            $tempVal,
            [
                'login.php',
                'admin/makepass.php',
                'css/style.php',
                'js/script.php',
                'js/process.php',
                'logout.php',
                'index.php',
                'login',
                'admin/makepass',
                'css/style',
                'js/script',
                'js/process',
                'logout',
                'index',
                'favicon.ico'
            ],
            true
        )) {
            $this->checkAuth();
        }
    }
    
    public function checkAuth(): void
    {
        if (empty($_SESSION['userinfo']['agent']) || empty($_SESSION['userinfo']['timeout'])) {
            unset($_SESSION['sessionKey']);
            unset($_SESSION['userinfo']);
            session_unset();
            $_SESSION['message'][] = 'Please sign in to access this content';
            header('Location: ' . ROOT_FOLDER . '/login.php');
            exit();
        } elseif (!empty($_SESSION['userinfo']['agent']) && md5($_SERVER['HTTP_USER_AGENT']) != $_SESSION['userinfo']['agent']) {
            unset($_SESSION['sessionKey']);
            unset($_SESSION['userinfo']);
            session_unset();
            $_SESSION['message'][] = 'You were logged out. Please sign in again';
            header('Location: ' . ROOT_FOLDER . '/login.php');
            exit();
        } elseif (!empty($_SESSION['userinfo']['timeout']) && time() > $_SESSION['userinfo']['timeout']) {
            unset($_SESSION['sessionKey']);
            unset($_SESSION['userinfo']);
            session_unset();
            $_SESSION['message'][] = 'You were logged out due to inactivity. Please sign in again';
            header('Location: ' . ROOT_FOLDER . '/login.php');
            exit();
        } elseif (time() <= $_SESSION['userinfo']['timeout'] && md5($_SERVER['HTTP_USER_AGENT']) == $_SESSION['userinfo']['agent'] && empty($_SESSION['userinfo']['password'])) {
            header('Location: ' . ROOT_FOLDER . '/admin/makepass.php');
            exit();
        } elseif (time() <= $_SESSION['userinfo']['timeout'] && md5($_SERVER['HTTP_USER_AGENT']) == $_SESSION['userinfo']['agent']) {
            $_SESSION['userinfo']['timeout']  = strtotime("+30 minutes");
        }
    }

    public static function pr($message) {
        $stdout = fopen('php://stdout', 'w');
        fwrite($stdout, print_r($message, true));
        print_r($message);
    }
}