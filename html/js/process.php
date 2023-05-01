<?php
session_start();
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
use EdwardsEyes\inc\required;
use EdwardsEyes\inc\database;
use EdwardsEyes\inc\answerKey;

(new required)->init();

$debug = true;
$process = (!empty($_POST['action']))?strtolower($_POST['action']):null;
if ($process == 'contact' || !empty($_POST['access']) && $_SESSION['sessionKey'] == $_POST['access']) {
    $connect = new database();

    switch ($process) {
        case 'login':
            $doWhatNow = $connect->login($_POST['username'], $_POST['password']);
            switch ($doWhatNow) {
                case 'reset':
                    header('Location: ' . ROOT_FOLDER . '/admin/makepass.php');
                    exit();
                    break;
                case 'success':
                    header('Location: ' . ROOT_FOLDER . '/admin/dashboard.php');
                    exit();
                    break;
                case 'fail':
                case 'banned':
                case 'unknown':
                default:
                    header('Location: ' . ROOT_FOLDER . '/login.php');
                    exit();
            }
            break;
        case 'create password':
            $userId = $_SESSION['userinfo']['id'];
            if (!empty($_SESSION['userinfo']['id'])) {
                if ($_SESSION['userinfo']['password'] == 'autenticated') {
                    header('Location: ' . ROOT_FOLDER . '/admin/dashboard.php');
                    exit();
                } elseif ($_POST['password'] !== $_POST['password']) {
                    header('Location: ' . ROOT_FOLDER . '/admin/makepass.php');
                    exit();
                } else if (!preg_match('/.{6,}/i', $_POST['password'])) {
                    header('Location: ' . ROOT_FOLDER . '/admin/makepass.php');
                    exit();
                } else {
                    if ($connect->editUser($userId, array('password' => $_POST['password']))) {
                        $_SESSION['userinfo']['password'] = 'autenticated';
                        header('Location: ' . ROOT_FOLDER . '/admin/dashboard.php');
                        exit();
                    } else {
                        header('Location: ' . ROOT_FOLDER . '/admin/makepass.php');
                        exit();
                    }
                }
            } else {
                header('Location: ' . ROOT_FOLDER . '/login.php');
                exit();
            }
            break;
        case 'save':
            if (!empty($_SESSION['userinfo']['id']) && !empty($_SESSION['studyinfo']['id']) && !empty($_SESSION['studyinfo']['eyepoolid'])) {
                $parsed = new answerKey($_SESSION['userinfo']['id'], $_SESSION['studyinfo']['id'], $_SESSION['studyinfo']['eyepoolid']);
                $parsed->setAnswers($_POST);
                if ($connect->saveData($parsed)) {
                    echo json_encode(array('success'=>true));
                    exit();
                } else {
                    $error = array('success'=>false, 'message' => 'Unable to save data, please refresh and try again');
                }
            } else {
                $error = array('success'=>false, 'message' => 'No Iris selected');
            }
            header('Content-Type: application/json');
            echo json_encode($error);
            exit();
            break;
        case 'colour':
            if (!empty($_SESSION['userinfo']['id'])) {
                header('Content-Type: application/json');
                if ($complete = $connect->saveColourData($_SESSION['userinfo']['id'], $_POST)) {
                    echo json_encode(array('success'=>true));
                } else {
                    echo json_encode(array('success'=>false));
                }
                exit();
            }
            break;
        case 'download':
            if (!empty($_SESSION['userinfo']['id'])) {
                $studyId = null;
                $studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
                foreach ($studies as $studyIdIdx => $studyDetails) {
                    if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
                        $studyId = $studyIdIdx;
                    }
                }
                $userId = intval($_SESSION['userinfo']['id']);
                if (!empty($studyId)) {
                    $imagePath = dirname(SERVER_ROOT) . '/html/images/studies/colours/' . $studyId . '/';

                    $zipname = $imagePath . 'wedges.zip';
                    if (is_readable($zipname)) {
                        unlink($zipname);
                    }
                    $zip = new ZipArchive();
                    $res = $zip->open($zipname, ZipArchive::CREATE | ZIPARCHIVE::OVERWRITE);
                    if ($res == true) {
                        $files = glob($imagePath . '*.png');
                        foreach ($files as $file) {
                            $zip->addFile($file, basename($file));
                        }
                        $zip->close();

                        header('Content-Type: application/json');
                        $zipLocation = '/' . implode(
                            '/', 
                            array_diff(
                                explode('/', $imagePath), 
                                explode('/', dirname(SERVER_ROOT) . '/' . 'html')
                            )
                        );
                        echo json_encode(['zip' => ROOT_FOLDER . $zipLocation . "/wedges.zip", 'dir' => dirname(__DIR__) . $imagePath]);
                        exit();
                    }
                }
                echo json_encode(['success' => false]);;
                exit();
            }
            break;
        case 'enableiris':
            if (!empty($_SESSION['userinfo']['id'])) {
                $studyId = null;
                $studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
                foreach ($studies as $studyIdIdx => $studyDetails) {
                    if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
                        $studyId = $studyIdIdx;
                    }
                }
                $userId = intval($_SESSION['userinfo']['id']);
                if (!empty($studyId)) {
                    $filename = $_POST['filename'];
                    $value = ($_POST['value'] == 'true')?'Y':'N';
                    if ($connect->updateFilenameByFilename($studyId, $filename, $value) === true) {
                        echo json_encode(['success' => true]);
                        exit();
                    }
                }
            }
            echo json_encode(['success' => false]);;
            break;
        case 'uploadiris':
            /*
            if (!empty($_SESSION['userinfo']['id'])) {
                $studyId = null;
                $studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);
                foreach ($studies as $studyIdIdx => $studyDetails) {
                    if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
                        $studyId = $studyIdIdx;
                    }
                }
                $userId = intval($_SESSION['userinfo']['id']);
                if (!empty($studyId)) {
                    $filename = $_FILES['files'];
                    echo json_encode($_FILES);
                    echo json_encode($_POST);
                }
            }*/
            break;
        case 'updateuser':
            $errors = array();
            $userId = intval($_SESSION['userinfo']['id']);
            $userName = null;
            $currentPass = null;
            $newPass = null;
            $email = '';
            $contactable = 'N';

            if (!empty($_POST['user']) && !preg_match('/[^-_a-z 0-9]/i', $_POST['user'])) {
                $userName = $_POST['user'];
            }

            if (!empty($_POST['oldpass'])) {
                $currentPass = $_POST['oldpass'];
            } else {
                $errors[] = 'You must provide your old password';
            }

            if (!empty($_POST['pass']) && !empty($_POST['pass2']) && $_POST['pass'] == $_POST['pass2']) {
                $newPass = $_POST['pass'];
            } elseif (empty($_POST['pass'])) {
                $newPass = null;
            } else {
                $errors[] = 'Passwords do not match';
            }

            if (!empty($_POST['email']) && preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i', $_POST['email'])) {
                $email = $_POST['email'];
            } elseif (empty($_POST['email'])) {
                $email = '';
            } else {
                $errors[] = 'Please provide a valid email';
            }

            if (!empty($_POST['contactable']) && $_POST['contactable'] == 'Y') {
                $contactable = 'Y';
            } else {
                $contactable = 'N';
            }

            if (!empty($_SESSION['userinfo']['id']) && !empty($_POST['uid']) && $_POST['uid'] != $_SESSION['userinfo']['id'] && !empty($_SESSION['userinfo']['access']) && $_SESSION['userinfo']['access'] >= array_search('coordinator', ACL_RANKS)) {
                // Admin Change
                $userId = intval($_POST['uid']);
            } elseif (!empty($_SESSION['userinfo']['id'])) {
                // Self Change
                $userId = intval($_SESSION['userinfo']['id']);
            } else {
                $errors[] = 'You are not authorized to make these changes';
            }
            if (empty($errors)) {
                $check = $connect->getUser($userId, $_SESSION['userinfo']['access'], $currentPass);
                if (!empty($check)) {
                    $parms = array(
                        'username' => $userName,
                        'password' => $newPass,
                        'email' => $email,
                        'contactable' => $contactable,
                    );
                    $parms = array_filter($parms);

                    $update = $connect->editUser($userId, $parms);

                    if (array_keys($update) == array_keys($parms)) {
                        $_SESSION['message'][] = 'Successfully saved';
                    } else {
                        $_SESSION['message'][] = 'Could not save';
                        foreach (array_diff(array_keys($parms), array_keys($update)) as $error) {
                            switch ($error) {
                                case 'username':
                                    $_SESSION['message'][] = 'Invalid Username';
                                    break;
                                case 'password':
                                    $_SESSION['message'][] = 'Invalid Password';
                                    break;
                                case 'email':
                                    $_SESSION['message'][] = 'Invalid Email';
                                    break;
                                case 'contactable':
                                    $_SESSION['message'][] = 'Invalid Contactable';
                                    break;
                            }
                        }
                    }
                } else {
                    $_SESSION['message'][] = 'Could not find specified user';
                }
            } else {
                $_SESSION['message'] = $errors;
            }
            header('Location: ' . ROOT_FOLDER . '/admin/users/self.php');
            exit();
            break;
        case 'updatestudy':
            if (!empty($_SESSION['userinfo']['id']) && !empty($_SESSION['userinfo']['access']) && $_SESSION['userinfo']['access'] >= array_search('coordinator', ACL_RANKS)) {
                $errors = array();
                if (!empty($_POST['studyid'])) {
                    $studyid = intval($_POST['studyid']);
                }
                if (!empty($_POST['studyname']) && !preg_match('/[^-_,a-z 0-9]/i', $_POST['studyname'])) {
                    $studyname = $_POST['studyname'];
                } elseif (empty($_POST['studyname'])) {
                    $errors[] = 'Study name must be provided.';
                } else {
                    $errors[] = 'Invalid study name. Names can contain letters, numbers, dashes, underscore, commas and spaces only.';
                }

                if (!empty($_POST['coordinator']) && !empty($_SESSION['userinfo']['access']) && $_SESSION['userinfo']['access'] >= array_search('principle investigator', ACL_RANKS)) {
                    $owner = intval($_POST['coordinator']);
                } else {
                    $owner = null;
                }

                if (!empty($_POST['running']) && $_POST['running'] == 'Y') {
                    $running = 'Y';
                } else {
                    $running = 'N';
                }

                if (!empty($_POST['ethnicities']) && !preg_match('/[^a-z, ]/i', $_POST['ethnicities'])) {
                    $types = array_unique(array_map(function ($s) {
                        return strtolower(trim($s));
                    }, explode(',', $_POST['ethnicities'])));
                    sort($types);
                } else {
                    $types = array('other');
                }
                if (!empty($_POST['eye_side']) && $_POST['eye_side'] == 'left') {
                    $eye_side = 'left';
                } else {
                    $eye_side = 'right';
                }

                if (empty($errors)) {
                    if ($connect->saveStudy($studyid, $studyname, $owner, $running, $types, $eye_side) !== false) {
                        $_SESSION['message'][] = 'Successfully saved';
                    } else {
                        $_SESSION['message'][] = 'Could not save';
                    }
                } else {
                    $_SESSION['message'] = $errors;
                }
            } else {
                $_SESSION['message'][] = 'You are not authorized to make these changes';
            }
            header('Location: ' . ROOT_FOLDER . '/admin/studies/edit.php');
            exit();
            break;
        case 'updateuserstudy':
            if (!empty($_SESSION['userinfo']['id']) && !empty($_SESSION['userinfo']['access']) && $_SESSION['userinfo']['access'] >= array_search('coordinator', ACL_RANKS)) {
                if ($connect->editUser($_SESSION['userinfo']['id'], array('running' => $_POST['studyId']))) {
                    $_SESSION['message'][] = 'Successfully saved';
                } else {
                    $_SESSION['message'][] = 'Could not save';
                }
            }
            header('Location: ' . ROOT_FOLDER . '/admin/studies/run.php');
            exit();
            break;
        case 'contact':
            $error = false;
            $to = 'davegoten@gmail.com';
            $subject = 'Iris - contact ( '. date('F j Y') .' )';
            $name = htmlspecialchars($_POST['name']);
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $comment = htmlspecialchars($_POST['comment']);
            if (empty($name)) {
                $_SESSION['message'][] = 'Please avoid special characters in your name; or perhaps use an easy to pronounce nickname.';
                $error = true;
            }
            if ($email === false) {
                $_SESSION['message'][] = 'Please enter a valid email so I can get back to you.';
                $error = true;
            }
            if (empty($comment)) {
                $_SESSION['message'][] = 'Please avoid html tags and special characters in your comment; I read plain text just fine.';
                $error = true;
            }

            if (!$error) {
                $header = "From: davidcha@davidcha.ca\r\nReply-To: {$email}\r\nX-Mailer: PHP/" .phpversion();
                $sent = mail($to, $subject, "{$name} <{$email}>\n\n" . $comment . "\n\nOn: " . date('F j Y H:i:s'), $header);
                if ($sent === true) {
                    $_SESSION['message'][] = 'Thank you, your email was successfully sent';
                } else {
                    $_SESSION['message'][] = 'Sorry, your email could not be sent';
                }
            }
            header('Location: ' . ROOT_FOLDER);
            exit();
            break;
        default:
        ?>
<?php
    } // End Switch
} // End Access Check
unset($_SESSION['sessionKey']);
unset($_SESSION['userinfo']);
session_unset();
$_SESSION['message'][] = "Sorry, something went wrong and you've been logged out. Please login again";
header('Location: ' . ROOT_FOLDER);