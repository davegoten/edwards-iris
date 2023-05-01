<?php
namespace EdwardsEyes\inc;

use DateTime;
use PDO;
use Exception;
use EdwardsEyes\inc\answerKey;

class database
{
    /** @var PDO $connection */
    private $connection;
    /** @var bool $debug */
    private $debug;
    /** @var array $aclRanks */
    private $aclRanks;
    /** @var str $algorithm */
    private $algorithm;
    /** @var string $salt */
    private $salt;
    /** @var array $dataSet */
    private $dataSet;
    /** @var array $uservalid */
    private $uservalid;
    /** @var string $error */
    private $error;

    public function __construct()
    {
        $debug = true;
        $this->aclRanks = ACL_RANKS;
        $this->uservalid = array(
                'username' => '/[a-z0-9_-]/i',
                'password' => '/.{6,}/i',
                'email' => '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i',
                'contactable'   => '/(Y|N)/',
                'access'   => '/(' . implode('|', array_keys($this->aclRanks)) . ')/',
                'banned'   => '/(Y|N)/',
                'running'  => '/[0-9]/'
        );

        $this->salt = SALT;
        if (HOST ==='sqlite') {
            try {
                $this->connection = new PDO(
                    'sqlite:/var/lib/mysql/edwards_eyes.db',
                    '', 
                    '',     
                    [PDO::ATTR_PERSISTENT => true]
                );
            } catch (Exception $e) {
                if ($debug) {
                    die('Connect Error (' . $e->getCode() . ') ' . $e->getMessage());
                } else {
                    die('Connect Error ( Could not connect ) ');
                }
            }
        } else {
            try {
                $this->connection = new PDO(
                    sprintf("mysql:host=%s;port=%d;dbname=%s", HOST, DATABASE_PORT, DB),
                    USR, 
                    PWD
                );
            } catch (Exception $e) {
                try {
                    $this->connection = new PDO('mysql:host=' . HOST . ';', USR, PWD);
                    $this->connection->query("CREATE DATABASE IF NOT EXISTS " . DB);
                } catch (Exception $ex) {
                    if ($debug) {
                        die('Schema Error (' . $ex->getCode() . ') ' . $ex->getMessage());
                    } else {
                        die('Schema Error ( Could create schema )');
                    }
                }
                $this->connection = null;
                try {
                    $this->connection = new PDO('mysql:host=' . HOST . ';dbname=' . DB, USR, PWD);
                } catch (Exception $ex) {
                    if ($debug) {
                        die('Default Schema Error (' . $ex->getCode() . ') ' . $ex->getMessage());
                    } else {
                        die('Default Schema Error ( Could create schema ) ');
                    }
                }
            }
        }
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->initTables();
        $res = $this->connection->prepare(
            "SELECT 1 FROM `user` WHERE `username` = :username LIMIT 1"
        );
        $res->execute([':username' => getenv('DEFAULT_USER')]);
        if (empty($res->fetch(PDO::FETCH_OBJ))) {
            $this->addUser(getenv('DEFAULT_USER'), 99);
        }
    }

    public function __destruct()
    {
        $this->connection = null;
    }

    public function login($username, $password): string
    {
        $sqlStatement = "SELECT * FROM `user` WHERE `username` = :username LIMIT 1;";
        $query = $this->connection->prepare($sqlStatement);
        if ($query->execute([':username' => $username]) === true) {
            $result = $query->fetch(PDO::FETCH_OBJ);
            if (!empty($result)) {
                if (strtoupper($result->banned) == 'Y') {
                    return 'banned';
                } elseif (empty($result->password)) {
                    $_SESSION['userinfo']['id']       = $result->userid;
                    $_SESSION['userinfo']['access']   = $result->access;
                    $_SESSION['userinfo']['timeout']  = strtotime("+30 minutes");
                    $_SESSION['userinfo']['agent']    = md5($_SERVER['HTTP_USER_AGENT']);
                    return 'reset';
                } elseif ($result->password == $this->encryptMe($password)) {
                    $_SESSION['userinfo']['id']       = $result->userid;
                    $_SESSION['userinfo']['access']   = $result->access;
                    $_SESSION['userinfo']['password'] = 'autenticated';
                    $_SESSION['userinfo']['timeout']  = strtotime("+30 minutes");
                    $_SESSION['userinfo']['agent']    = md5($_SERVER['HTTP_USER_AGENT']);
                    return 'success';
                }
                return 'fail';
            } else {
                return 'unknown';
            }
        }
        return 'other';
    }

    public function addUser(
        string $user, 
        int $level = 0,
        ?int $study = null, 
        ?string $email = null, 
        string $contactable = 'N'
    ): mixed {
        $userId = false;
        $fields = array();
        $check = $this->connection->prepare('SELECT * FROM `user` WHERE `username` = :user;');
        $check->execute([':user' => strtolower($user)]);
        $result = $check->fetch(PDO::FETCH_OBJ);

        if (!empty($result)) {
            return false;
        }
        $sql = "INSERT INTO `user` (";

        foreach ($this->uservalid as $key => $validation) {
            if ($key === 'username' && preg_match($validation, $user)) {
                $fields[$key] = $user;
            } elseif ($email !== null && $key === 'email' && preg_match($validation, $email)) {
                $fields[$key] = $email;
            } elseif ($email !== null && $key === 'contactable' && preg_match($validation, $contactable)) {
                $fields[$key] = $contactable;
            } elseif ($level !== null && $key === 'access' && preg_match($validation, $level)) {
                $fields[$key] = $level;
            }
        }
        $fields = array_filter($fields);
        if (!empty($fields['username']) && count($fields) > 0) {
            $sql .= '`'.implode('`, `', array_keys($fields)) . '`';
            $sql .= ') VALUES (:';
            $sql .= implode(', :', array_keys($fields));
        }
        $add = $this->connection->prepare($sql . ');');
        $add = $add->execute($fields);
        if (!empty($add)) {
            $userId = $this->connection->lastInsertId();
        } else {
            return false;
        }

        if ($study !== null) {
            $study = intval($study);
            $time = date("Y-m-d H:i:s");
            $studysql = "INSERT INTO `userstudy` (`studyid`, `userid`, `invitetime`, `status`) VALUES "
                . "(:study, :userId, :time, 'Y');";
            $studyQuery = $this->connection->prepare($studysql);
            $studyQuery->execute([
                ':study' => $study,
                ':userId' => $userId,
                ':time' => $time
            ]);
        }

        return $userId;
    }

    public function getNextEyeColour(mixed $studyId): mixed
    {
        $sql = "
            SELECT
                *
            FROM
                `eyepool` as E
            JOIN
                `eyeplot` as P
            ON
                E.`eyepoolid` = P.`eyepoolid`
            AND
                E.`studyid` = P.`studyid`
            WHERE
                E.`eyepoolid` NOT IN (SELECT
                    `eyepoolid`
                FROM
                    `colourplot`
                WHERE
                    `studyid` = :studyId)
                AND E.`useeye` = 'Y'
                AND E.`studyid` = :studyId
            LIMIT 1";
        $query = $this->connection->prepare($sql);
        $query->execute([':studyId' => $studyId]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function editUser(int $userId, array $params): array
    {
        $fields = array();
        $error = array();
        if (!empty($params['username'])) {
            $check = $this->connection->prepare(
                "SELECT * FROM `user` WHERE `username` = :username AND `userid` != :userId;"
            );
            $check->execute(['username' => $params['username'], 'userId' => $userId]);
            if (!empty($check->fetch(PDO::FETCH_OBJ))) {
                $error[] = 'username';
            }
        }

        $sql = "UPDATE `user` SET ";
        foreach ($this->uservalid as $key => $validation) {
            if (isset($params[$key]) && $params[$key] !== null && preg_match($validation, $params[$key])) {
                $sql .= "`{$key}` = :{$key}, ";
                if ($key == 'password') {
                    $fields[$key] = $this->encryptMe($params[$key]);
                } elseif ($key == 'username' && !in_array($key, $error)) {
                    $fields[$key] = strtolower($params[$key]);
                } elseif (in_array($key, ['access', 'running'])) {
                    $fields[$key] = intval($params[$key]);
                } else {
                    $fields[$key] = $params[$key];
                }
            } elseif (!empty($params[$key]) && !preg_match($validation, $params[$key])) {
                $error[] = $key;
            }
        }
        if (!empty($error)) {
            foreach ($error as $k) {
                unset($fields[$k]);
            }
            return $fields;
        }
        $sql = rtrim($sql, ', ');
        if (count($fields) > 0) {
            $sql .= " WHERE userid={$userId};";
            $query = $this->connection->prepare($sql);
            if ($query->execute($fields) === true) {
                return $fields;
            }
        }
        return array();
    }
    
    public function deleteUser($userId)
    {
    }

    public function getUser(int $userId, ?int $accessLevel = null, ?string $currentPass = null): ?array
    {
        $sql = "SELECT * FROM `user` WHERE `userid` = :userId";
        $params = [':userId' => $userId];
        if (!empty($accessLevel)) {
            $sql .= " AND `access` <= :accessLevel";
            $params[':accessLevel'] = $accessLevel;
        }
        if (!empty($currentPass)) {
            $sql .= " AND `password` = :currentPass";
            $params[':currentPass'] = $this->encryptMe($currentPass);
        }
        $sql .= " LIMIT 1;";
        $query = $this->connection->prepare($sql);
        $query->execute($params);
        if ($return = $query->fetch(PDO::FETCH_ASSOC)) {
            unset($return['password']);
            return $return;
        }
        return null;
    }

    public function getUsersByRank(string $rank): array
    {
        $rankToUse = array_search(strtolower($rank), $this->aclRanks);
        if ($rankToUse !== null && $_SESSION['userinfo']['access'] >= array_search('coordinator', $this->aclRanks)) {
            $sql = "SELECT userid, username FROM `user` WHERE `access` >= :rankToUse AND `access` <= :access AND `banned` != 'Y';";
            $query = $this->connection->prepare($sql);
            if ($query->execute([':rankToUse' => $rankToUse, ':access' => $_SESSION['userinfo']['access']])) {
                $return = array();
                while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
                    $return[$data['userid']] = $data['username'];
                }
                $return = array_filter($return);
                return $return;
            }
        }
        return array();
    }

    public function getStudiesFor(int $userId): array
    {
        $sql = "SELECT
                st.`studyid` AS studyidnum,
                usr.`running` AS coordinating,
                st.*,
                std.*,
                plots.*,
                completed.*
            FROM
                `user` AS usr
            LEFT JOIN
                `userstudy` AS std
            ON
                std.`userid` = usr.`userid`
            LEFT JOIN
                `study` AS st
            ON
                st.`studyid` = std.`studyid`
            LEFT JOIN
                (SELECT
                    `studyid`, COUNT(*) AS pool
                FROM
                    `eyepool`
                GROUP BY `studyid`) AS plots
            ON
                plots.`studyid` = std.`studyid`
            LEFT JOIN
                (SELECT
                    `studyid`, COUNT(*) AS plotted
                FROM
                    `eyeplot`
                WHERE
                    `userid` = :userId
                GROUP BY `studyid`) AS completed
            ON
                completed.`studyid` = std.`studyid`
            WHERE
                usr.`userid` = :userId;";
            $query = $this->connection->prepare($sql);
        if ($query->execute([':userId' => $userId])) {
            $return = array();
            while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
                if (empty($data['studyidnum'])) {
                    $data['studyidnum'] = 'sample';
                }
                $return[$data['studyidnum']] = array_merge((array)@$return[$data['studyidnum']], $data);
            }
            $return = array_filter($return);
            uasort($return, function ($a, $b) {
                return $a['studyid'] > $b['studyid'];
            });
            return $return;
        }
        return array();
    }

    private function getExtraDataForStudy(mixed $studyId, string $what = 'additional_data'): ?string
    {
        switch ($what) {
            case 'types':
            case 'additional_data':
                break;
            default:
                $what = 'additional_data';
        }
        $sql = "SELECT `{$what}` FROM `study` WHERE `studyid` = :studyId;";
        $query = $this->connection->prepare($sql);
        if ($query->execute([':studyId' => $studyId])) {
            $data = $query->fetch(PDO::FETCH_ASSOC);
            switch ($what) {
                case 'types':
                    return array_map('trim', explode(',', $data['types']));
                    break;
                case 'additional_data':
                default:
                    return $data['additional_data'];
            }
        }
        return null;
    }

    public function resetPassword(int $userId): bool
    {
        $sql = "UPDATE `user` SET `previous_password` = `password`, `password` = null WHERE `userid` = :userId;";
        $query = $this->connection->prepare($sql);
        return $query->execute([':userId' => $userId]);
    }

    public function getIris(int $userId, mixed $studyId): mixed
    {
        $_SESSION['studyinfo']['id'] = 'samples';
        $_SESSION['studyinfo']['eyepoolid'] = 1;
        $_SESSION['studyinfo']['filename'] = 'Blue-Iris.jpg';

        if ($studyId !== 'sample' && intval($studyId) > 0) {
            // Query DB
            $studyId = $studyId;
            $userId = $userId;
            $randomFunction = HOST === 'sqlite' ? 'RANDOM()' : 'RAND()';
            $sql = "
                SELECT
                    eyepoolid, filename
                FROM
                    `eyepool`
                WHERE
                    `studyid` = :studyId
                AND
                    `eyepoolid`
                NOT IN
                    (SELECT
                        `eyepoolid`
                    FROM
                        `eyeplot`
                    WHERE
                        `studyid` = :studyId
                    AND
                        `userid` = :userId
                )
                AND
                    `useeye` = 'Y'
                ORDER BY
                    {$randomFunction}
                LIMIT 1;";
            $query = $this->connection->prepare($sql);
            if ($query->execute([':studyId' => intval($studyId), ':userId' => $userId])) {
                $_SESSION['studyinfo']['id'] = $studyId;
                $_SESSION['studyinfo']['eyepoolid'] = 'complete';
                $_SESSION['studyinfo']['filename'] = 'null';
                $obj = $query->fetch(PDO::FETCH_OBJ);
                if (!empty($obj)) {
                    $_SESSION['studyinfo']['id'] = $studyId;
                    $_SESSION['studyinfo']['eyepoolid'] = $obj->eyepoolid;
                    $_SESSION['studyinfo']['filename'] = $obj->filename;
                }
            }
        }

        return $_SESSION['studyinfo']['eyepoolid'];
    }

    public function getStudy(mixed $studyid, int $access): array
    {
        $return = array();
        $params = [':studyid' => intval($studyid)];

        $sql = "
            SELECT
                *
            FROM
                `eyepool` as IP
            JOIN
                `study` as S
            ON
                S.`studyid` = IP.`studyid`
            WHERE
                IP.`studyid` = :studyid";

        if (intval($access) < array_search('developer', $this->aclRanks)) {
            $params[':userId'] = intval($_SESSION['userinfo']['id']);
            $sql .= "
            AND
                S.`coordinatorid` = :userId;";
        }

        $query = $this->connection->prepare($sql);
        if ($query->execute($params)) {
            $return = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        return array_filter($return);
    }


    public function getStudyData(mixed $studyid): array
    {
        $sql ="SELECT
            S.studyid,
            S.coordinatorid,
            S.studyname,
            S.additional_data,
            S.types,
            S.running,
            U.userid,
            U.username,
            U.email,
            U.access,
            U.banned,
            U.running as curstudy
        FROM
            `study` AS S
                LEFT JOIN
            `user` AS U ON S.`coordinatorid` = U.`userid`
        WHERE
            `studyid` = :studyId
        LIMIT 1;";
        $return = array();
        $query = $this->connection->prepare($sql);
        if ($query->execute([':studyId' => $studyid])) {
            while ($res = $query->fetch(PDO::FETCH_ASSOC)) {
                unset($res['password']);
                $return[] = $res;
            }
        }
        return array_filter($return);
    }

    public function getParticipants(): array
    {
        $sql ="SELECT * FROM `user`;";
        $return = array();
        $sqlStudies ="SELECT * FROM `userstudy` WHERE `userid` = :userId";
        if ($query = $this->connection->query($sql)) {
            while ($res = $query->fetch(PDO::FETCH_ASSOC)) {
                unset($res['password']);
                $queryStudies = $this->connection->prepare($sqlStudies);
                if ($queryStudies->execute([':userId' => $res['userid']])) {
                    $studies = array();
                    while ($resStudies = $queryStudies->fetch(PDO::FETCH_ASSOC)) {
                        $studies[$resStudies['studyid']] = $resStudies;
                    }
                    $res['studies'] = $studies;
                }
                $return[] = $res;
            }
        }
        return array_filter($return);
    }

    public function saveStudy(
        mixed $studyid, 
        string $studyname, 
        ?int $owner, 
        string $running, 
        array $types, 
        string $eye_side
    ): bool {
        if (!in_array($eye_side, ['right', 'left']) ||
            !in_array($running, ['Y', 'N', '']) ||
            empty($studyname)
        ) {
            return false;
        }
        $dataSet = array(
            'coordinatorid' => $owner,
            'studyid'       => intval($studyid),
            'studyname'     => $studyname,
            'running'       => $running,
            'types'         => implode(',', $types),
            'eye_side'      => $eye_side
        );
        if ($dataSet['studyid'] > 0) {
            $sql = "UPDATE `study` SET ";
            $res = array();
            foreach ($dataSet as $fieldname => $value) {
                if ($fieldname != 'studyid') {
                    $res[] = "`{$fieldname}` = :{$fieldname}";
                }
            }
            $sql .= implode(',', $res) . " WHERE `studyid` = :studyid LIMIT 1;";
            $query = $this->connection->prepare($sql);
            return $query->execute($dataSet);
        }
        unset($dataSet['studyid']);
        $sql = "INSERT INTO `study` (`" . implode('`,`', array_keys($dataSet)) ."`) VALUES (:"
            . implode(', :', array_keys($dataSet)) . ');';
        $createLink = true;
        $query = $this->connection->prepare($sql);
        if ($query->execute($dataSet)) {
            $studyId = $this->connection->lastInsertId();
            if (!empty($studyId)) {
                $linksql = "INSERT INTO `userstudy` (`studyid`, `userid`,`invitetime`,`status`) VALUES " 
                    . "(:studyId, :owner, CURRENT_TIMESTAMP, 'Y');";
                $updatesql = "UPDATE `user` SET `running` = :studyId WHERE `userid` = :owner;";
                $linkQuery = $this->connection->prepare($linksql);
                $updateQuery = $this->connection->prepare($updatesql);
                $params = [':studyId' => $studyId, ':owner' => intval($owner)];
                return $linkQuery->execute($params) && $updateQuery->execute($params);
            }
        }

        return false;
    }

    public function indexStudy(mixed $studyid, array $datasets): bool
    {
        $sql = 'INSERT INTO `eyepool` (`studyid`, `filename`,`useeye`) VALUES ';
        $params = [':studyid' => intval($studyid)];

        foreach ($datasets as $i => $filename) {
            if (is_readable(dirname(SERVER_ROOT) . '/html/images/studies/structures/' . $studyid . '/' . $filename)) {
                $sql .= "(:studyid, :file_{$i},'Y'), ";
                $params[':file_' . $i] = $filename;
            }
        }
        if (count($datasets) > 0) {
            $query = $this->connection->prepare(rtrim($sql, ', ') . ';');
            return $query->execute($params);
        }
        return false;
    }
    
    public function getEnabledByFilename(mixed $studyid, array $filenames): array
    {
        if (empty($filenames)) {
            return [];
        }
        $sql = "SELECT `filename`, `useeye` from `eyepool` WHERE `studyid` = :studyid AND `filename` in (:filenames);";
        $query = $this->connection->prepare($sql);
        $query->execute([':studyid' => intval($studyid), ':filenames' => implode(', ', $filenames)]);
        return $query->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function updateFilenameByFilename(mixed $studyid, string $filename, string $value): bool
    {
        if (!empty($filename)) {
            return false;
        }
        $studyid = intval($studyid);
        switch (strtoupper($value)) {
            case 'N':
                $value = 'N';
                break;
            case 'Y':
            default:
                $value = 'Y';
        }
        $sql = "UPDATE `eyepool` SET `useeye` = :value WHERE `studyid` = :studyid AND `filename` = :filename;";
        $query = $this->connection->prepare($sql);
        return $query->execute([':studyid' => $studyid, ':value' => $value, ':filename' => $filename]);
    }

    public function saveData(answerKey $answerKey): bool
    {
        $baseData  = $answerKey->getAnswers();
        $nevi  = $answerKey->getFreckles();
        $neviData = [];
        $crypt = $answerKey->getCrypts();
        $cryptData = [];
        $baseInesert = "INSERT INTO `eyeplot` ";

        $sql['base'] = $baseInesert;
        if (count($baseData) > 0) {
            $sql['base'] .= "(`" . implode('`, `', array_keys($baseData)). "`) VALUES ";
            $sql['base'] .= "(:" . implode(', :', array_keys($baseData)). ")";
        }
        if (count($nevi) > 4) {
            $sql['nevi'] = "INSERT INTO `neviplot` 
                (`studyid`, `userid`, `eyepoolid`, `x`, `y`, `size`, `time`) VALUES ";
            $neviData = [
                ':studyid' => $nevi['studyid'],
                ':userid' => $nevi['userid'],
                ':eyepoolid' => $nevi['eyepoolid'],
            ];
            foreach ($nevi as $i => $neviInfo) {
                if (is_array($neviInfo)) {
                    $sql['nevi'] .= " (:studyid, :userid, :eyepoolid, :x_{$i}, :y_{$i}, :size_{$i}, :time), ";
                    $neviData[':x_'. $i] = $neviInfo['x'];
                    $neviData[':y_'. $i] = $neviInfo['y'];
                    $neviData[':size_'. $i] = $neviInfo['size'];
                }
                if (empty($neviData[':time']) && !empty($neviInfo['time'])) {
                    $neviData[':time'] = $neviInfo['time'];
                }
            }
            $sql['nevi'] = rtrim($sql['nevi'], ', ');
        }
        if (count($crypt) > 4) {
            $sql['crypt'] = "INSERT INTO `cryptplot` 
                (`studyid`, `userid`, `eyepoolid`, `x`, `y`, `size`, `time`) VALUES ";
            $cryptData = [
                ':studyid' => $crypt['studyid'],
                ':userid' => $crypt['userid'],
                ':eyepoolid' => $crypt['eyepoolid'],
            ];
            foreach ($crypt as $i => $cryptInfo) {
                if (is_array($cryptInfo)) {
                    $sql['crypt'] .= "(:studyid, :userid, :eyepoolid, :x_{$i}, :y_{$i}, :size_{$i}, :time), ";
                    $cryptData[':x_'. $i] = $cryptInfo['x'];
                    $cryptData[':y_'. $i] = $cryptInfo['y'];
                    $cryptData[':size_'. $i] = $cryptInfo['size'];
                }
                if (empty($cryptData[':time']) && !empty($cryptInfo['time'])) {
                    $cryptData[':time'] = $cryptInfo['time'];
                }
            }
            $sql['crypt'] = rtrim($sql['crypt'], ', ');
        }
        $return = true;
        foreach ($sql as $type => $statement) {
            $data = ${$type . 'Data'};
            if (!empty($data)) {
                $query = $this->connection->prepare($statement);
                $return = $return && $query->execute($data);
            }
        }
        return $return;
    }

    public function getColourData(mixed $studyId, ?int $userId, array $ancestories = [])
    {
        $studyId = intval($studyId);
        array_walk($ancestories, 'strtolower');
        $params = [':studyId' => $studyId];
        if (!empty($userId)) {
            $params[':userId'] = $userId;
        }

        switch ($this->getExtraDataForStudy($studyId, 'additional_data')) {
            case 'pigmentation_study':
                if (empty($ancestories)) {
                    $ancestories = $this->getExtraDataForStudy($studyId, 'types');
                }
                $params[':ancestories'] = implode(', ', $ancestories);
                $sql = "SELECT
                    pi.`participant`,
                    col.`total_l`,
                    col.`total_a`,
                    col.`total_b`,
                    pi.`ancestry`
                FROM
                    `colourplot` AS col
                        LEFT JOIN
                    `eyepool` AS ep ON col.`studyid` = ep.`studyid`
                        AND col.`eyepoolid` = ep.`eyepoolid`
                LEFT JOIN
                    `pigmentation_study` AS pi ON SUBSTR(ep.filename, 1, 4) = pi.`participant`
                        AND col.`studyid` = pi.`study_id`
                WHERE
                    LOWER(pi.`ancestry`) IN (:ancestories') AND
                    LOWER(ep.`useeye`) = 'y'
                    AND col.`studyid` = :studyId
                    AND ep.`filename` NOT LIKE '% IR %'";
                $sql .= (empty($userId))?';':" AND col.`userid` = :userId;";
                break;
            case 'cincinnatixx':
                if (empty($ancestories)) {
                    $ancestories = $this->getExtraDataForStudy($studyId, 'types');
                }
                $params[':ancestories'] = implode(', ', $ancestories);
                $sql = "SELECT
                    ep.`filename` as `participant`,
                    col.`total_l`,
                    col.`total_a`,
                    col.`total_b`,
                    pi.`ancestry`
                FROM
                    `colourplot` AS col
                LEFT JOIN
                    `eyepool` AS ep ON col.`studyid` = ep.`studyid`
                        AND col.`eyepoolid` = ep.`eyepoolid`
                LEFT JOIN
                    `cincinnati` AS pi ON ep.`filename` LIKE CONCAT(pi.`participant`, '%')
                        AND col.`studyid` = pi.`study_id`
                WHERE
                    LOWER(pi.`ancestry`) IN (:ancestories) AND
                    LOWER(ep.`useeye`) = 'y'
                    AND col.`studyid` = :studyId";
                $sql .= (empty($userId))?';':" AND col.`userid` = :userId;";
                break;

            case 'cincinnati': // Alone
                $sql = "SELECT
                    ep.`filename` as `participant`,
                    col.`total_l`,
                    col.`total_a`,
                    col.`total_b`,
                    pi.`ancestry`
                FROM
                    `colourplot` AS col
                LEFT JOIN
                    `eyepool` AS ep ON col.`studyid` = ep.`studyid`
                        AND col.`eyepoolid` = ep.`eyepoolid`
                LEFT JOIN
                    `cincinnati` AS pi ON ep.`filename` LIKE CONCAT(pi.`participant`, '%')
                        AND col.`studyid` = pi.`study_id`
                WHERE
                        col.`studyid` IN (1,2)
                ;";
                break;
            default:
                $sql = "SELECT
                    ep.`filename` as `participant`,
                    col.`total_l`,
                    col.`total_a`,
                    col.`total_b`,
                    'other' AS `ancestry`
                FROM
                    `colourplot` AS col
                        LEFT JOIN
                    `eyepool` AS ep ON col.`studyid` = ep.`studyid`
                        AND col.`eyepoolid` = ep.`eyepoolid`
                WHERE
                    LOWER(ep.`useeye`) = 'y'
                    AND col.`studyid` = :studyId";
                $sql .= (empty($userId))?';':" AND col.`userid` = :userId;";
        }
        $query = $this->connection->prepare($sql);
        if ($query->execute($params)) {
            return array_filter($query->fetchAll(PDO::FETCH_ASSOC));
        }
    }

    public function saveColourData($userId, $arr): bool
    {
        foreach ($arr as $key => $data) {
            if (in_array($key, array('Isample','Osample','studyid','eyepoolid','Tsample'))) {
                $arr[$key] = intval($data);
            } else {
                $arr[$key] = floatval($data);
            }
        }
        if (!empty($arr['i']) && intval($arr['i'])) {
            $userId = intval($arr['i']);
        }
        unset($arr['i']);
        $arr['userId'] = $userId;
        $arr['time'] = date("Y-m-d H:i:s");
        $sql = "INSERT INTO `colourplot` (
                    `studyid`,
                    `userid`,
                    `eyepoolid`,
                    `outer_r`,
                    `outer_g`,
                    `outer_bl`,
                    `outer_n`,
                    `inner_r`,
                    `inner_g`,
                    `inner_bl`,
                    `inner_n`,
                    `outer_l`,
                    `outer_a`,
                    `outer_b`,
                    `deltae`,
                    `inner_l`,
                    `inner_a`,
                    `inner_b`,
                    `total_r`,
                    `total_g`,
                    `total_bl`,
                    `total_n`,
                    `total_l`,
                    `total_a`,
                    `total_b`,
                    `time`
                ) VALUES (
                    :studyid,
                    :userId,
                    :eyepoolid,
                    :Or,
                    :Og,
                    :Obl,
                    :Osample,
                    :Ir,
                    :Ig,
                    :Ibl,
                    :Isample,
                    :Ol,
                    :Oa,
                    :Ob,
                    :deltae,
                    :Il,
                    :Ia,
                    :Ib,
                    :Tr,
                    :Tg,
                    :Tbl,
                    :Tsample,
                    :Tl,
                    :Ta,
                    :Tb,
                    :time
                );";
        unset($arr['access'], $arr['action']);
        $query = $this->connection->prepare($sql);
        return $query->execute($arr);
    }

    public function reportStudy($studyId): array
    {
        $sqlbase = "
            SELECT
                *
            FROM
                `eyeplot` AS plot
            LEFT JOIN
                `user` AS usr
            ON
                usr.`userid` = plot.`userid`
            LEFT JOIN
                `eyepool` AS pool
            ON
                plot.`eyepoolid` = pool.`eyepoolid`";
        switch ($this->getExtraDataForStudy($studyId, 'additional_data')) {
            case 'pigmentation_study':
                $sqlbase .= "
                    LEFT JOIN
                        `pigmentation_study` AS universe
                    ON
                        pool.`filename` LIKE CONCAT(universe.`participant`, '%')";
                break;
            case 'cincinnati':
                $sqlbase .= "
                    LEFT JOIN
                        `cincinnati` AS universe
                    ON
                        pool.`filename` LIKE CONCAT(universe.`participant`, '%')";
                break;
            default:
        }
            $sqlbase .= "
            WHERE
                plot.`studyid` = :studyId
            AND
                pool.`studyid` = :studyId
            ORDER BY pool.`filename` ASC, plot.`userid`;";
        $sql['rgb']   = "SELECT * FROM `colourplot` WHERE `studyid` = :studyId AND `userid` = ";
        $sql['nevi']  = "SELECT * FROM `neviplot` WHERE `studyid` = :studyId AND `userid` = ";
        $sql['crypt'] = "SELECT * FROM `cryptplot` WHERE `studyid` = :studyId AND `userid` = ";

        $return = array();
        $query = $this->connection->prepare($sqlbase);
        if ($query->execute([':studyId' => $studyId])) {
            while ($res = $query->fetch(PDO::FETCH_ASSOC)) {
                $idIr = null;
                unset($res['password']);

                $participantFile = pathinfo($res['filename'], PATHINFO_FILENAME);
                if (!empty($participantFile)) {
                    $params[':participantFile'] = $participantFile;
                    $concatIr = HOST === 'sqlite' ? ":participantFile || '%ir%'" : "CONCAT(:participantFile, '%ir%')";
                    $sqlIrBase = "SELECT `eyepoolid` FROM `eyepool` WHERE lower(`filename`) LIKE {$concatIr} AND `studyid` = :studyId;";
                    $queryIr = $this->connection->prepare($sqlIrBase);
                    if ($queryIr->execute([':studyId' => $studyId, ':participantFile' => $participantFile]) && 
                        $resIr = $queryIr->fetch(PDO::FETCH_ASSOC)
                    ) {
                        $idIr = $resIr['eyepoolid'];
                        $sql['neviIr']  = "SELECT * FROM `neviplot` WHERE `studyid` = :studyId AND `eyepoolid` = :idIr;";
                        $sql['cryptIr'] = "SELECT * FROM `cryptplot` WHERE `studyid` = :studyId AND `eyepoolid` = :idIr;";
                        $sql['otherIr'] = "SELECT * FROM `eyeplot` WHERE `studyid` = :studyId AND `eyepoolid` = :idIr;";
                    } else {
                        unset($sql['neviIr'],$sql['cryptIr'],$sql['otherIr']);
                    }
                }
                foreach ($sql as $plotTable => $sqlStr) {
                    $params = [
                        ':studyId' => $studyId,
                        ':userId' => $res['userid'],
                        ':eyepoolid' => $res['eyepoolid']
                    ];
                    $concat = ":userId AND `eyepoolid` = :eyepoolid;";
                    if (preg_match('/Ir$/', $plotTable) && !empty($idIr)) {
                        $concat = "";
                        unset($params[':userId']);
                        $params[':eyepoolid'] = $idIr;
                    }
                    $queryPlots = $this->connection->prepare($sqlStr . $concat);
                    if ($queryPlots->execute($params)) {
                        $data = $queryPlots->fetchAll(PDO::FETCH_ASSOC);
                        $resultSet = $this->categorize(
                            $plotTable,
                            $data,
                            floatval($res['iris_x']), 
                            floatval($res['iris_y'])
                        );
                        $res = array_merge($res, $resultSet);
                    }
                }
                $return[] = $res;
            }
        }
        return array_filter($return);
    }

    private function categorize(string $what, array $dataset, float $x, float $y): array
    {
        $list = array();
        $quadrants = array();
        foreach ($dataset as $datum) {
            switch ($what) {
                case 'nevi':
                case 'crypt':
                case 'neviIr':
                case 'cryptIr':
                    if (!empty($datum['size'])) {
                        if (empty($list[$what . 'size_'.strtolower($datum['size'])])) {
                            $list[$what . 'size_'.strtolower($datum['size'])] = 0;
                        }
                        $list[$what . 'size_'.strtolower($datum['size'])]++;
                    }
                    if (!empty($datum['x']) && !empty($datum['y'])) {
                        $quad = strtolower($this->findQuadrant($x, $y, floatval($datum['x']), floatval($datum['y'])));
                        if (empty($quadrants[$what . 'quadrant_'.$quad])) {
                            $quadrants[$what . 'quadrant_'.$quad] = array();
                        }
                        if (empty($quadrants[$what . 'quadrant_'.$quad]['size_'.strtolower($datum['size'])])) {
                            $quadrants[$what . 'quadrant_'.$quad]['size_'.strtolower($datum['size'])] = 0;
                        }
                        $quadrants[$what . 'quadrant_'.$quad]['size_'.strtolower($datum['size'])]++;
                    }
                    break;
                case 'rgb':
                    $quadrants[$what] = $datum;
                    break;
                case 'otherIr':
                    $quadrants[$what] = $datum;
                    break;
                default:
                    break;
            }
        }
        $category = 0;
        if (preg_match('/^nevi/i', $what)) {
            /*
            if (!empty($list[$what . 'size_s']) && $list[$what . 'size_s'] > 0 ) {
                $category = 1;
            }
            if (!empty($list[$what . 'size_l']) && $list[$what . 'size_l'] > 0 ) {
                $category = 2;
            }
            if (!empty($list[$what . 'size_l']) && $list[$what . 'size_l'] > 4 ) {
                $category = 3;
            }*/
            $nevi_count = @$list[$what . 'size_l'] + @$list[$what . 'size_s'];
            if ($nevi_count <= 0) {
                $category = 0;
            } elseif ($nevi_count < 3) {
                $category = 1;
            } else {
                $category = 2;
            }
        } elseif (preg_match('/^crypt/i', $what)) {
            $ql = 0;
            $qs = 0;
            foreach ($quadrants as $quad => $val) {
                if (!empty($val['size_l']) && $val['size_l'] > 0) {
                    $ql++;
                }
                if (!empty($val['size_s']) && $val['size_s'] > 0) {
                    $qs++;
                }
                if (!empty($val['size_f']) && $val['size_f'] > 0) {
                    $qs++;
                }
            }

            if ($ql == 0 && $qs > 2) { // 2 quadrants+
                $category = 1;
            } elseif ($ql > 0 && $ql < 3) {
                $category = 2;
            } elseif ($ql > 2) {
                $category = 3;
            }
        } elseif ($what == 'rgb') {
            $category = 'tba';
        }
        return array_merge($list, $quadrants, array($what . 'category' => $category));
    }

    public function findQuadrant(float $originX, float $originY, float $checkX, float $checkY): string
    {
        $quadrants = array(
            1 => array(0 => 'g', 1 => 'd'),
            0 => array(0 => 'a', 1 => 'b')
        );
        $onTop   = ($checkY <= $originY)? 1 : 0;
        $onRight = ($checkX >= $originX)? 1 : 0;
        return ($quadrants[$onRight][$onTop]);
    }

    private function initTables(): void
    {
        $primaryKeyDef = (HOST === 'sqlite') ? 
            'INTEGER PRIMARY KEY AUTOINCREMENT' :
            'INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        $tables = array(
            'user' => "
                CREATE TABLE IF NOT EXISTS `user` (
                    `userid`            {$primaryKeyDef},
                    `username`          VARCHAR(30),
                    `password`          VARCHAR(70),
                    `previous_password` VARCHAR(70),
                    `email`             VARCHAR(70),
                    `contactable`       CHAR(1) DEFAULT 'N',
                    `access`            TINYINT UNSIGNED DEFAULT 0,
                    `banned`            CHAR(1) DEFAULT 'N',
                    `running`           INTEGER UNSIGNED
                );",
            'study' => "
                CREATE TABLE IF NOT EXISTS `study` (
                    `studyid`         {$primaryKeyDef},
                    `coordinatorid`   INTEGER NOT NULL,
                    `studyname`       VARCHAR(30),
                    `running`         CHAR(1) DEFAULT NULL,
                    `additional_data` VARCHAR(50),
                    `types`           VARCHAR(255),
                    `eye_side`        VARCHAR(5) DEFAULT 'right'
                );",
            'eyepool' => "
                CREATE TABLE IF NOT EXISTS `eyepool` (
                    `eyepoolid`  {$primaryKeyDef},
                    `studyid`    INTEGER UNSIGNED,
                    `filename`   VARCHAR(125),
                    `useeye`     CHAR(1) DEFAULT 'Y'
                );",
            'eyeplot' => "
                CREATE TABLE IF NOT EXISTS `eyeplot` (
                    `eyeplotid`    {$primaryKeyDef},
                    `studyid`      INTEGER UNSIGNED,
                    `userid`       INTEGER UNSIGNED,
                    `eyepoolid`    INTEGER UNSIGNED,
                    `obstructed`   CHAR(1),
                    `iris_x`       DOUBLE,
                    `iris_y`       DOUBLE,
                    `iris_r`       DOUBLE UNSIGNED,
                    `pupil_x`      DOUBLE,
                    `pupil_y`      DOUBLE,
                    `pupil_r`      DOUBLE UNSIGNED,
                    `collarette_r` DOUBLE UNSIGNED,
                    `furrows_x`    DOUBLE,
                    `furrows_y`    DOUBLE,
                    `furrows_50`   CHAR(1),
                    `furrows_1`    CHAR(1),
                    `furrows_2`    CHAR(1),
                    `furrows_3`    CHAR(1),
                    `furrows_4`    CHAR(1),
                    `wolfflin_x`   DOUBLE,
                    `wolfflin_y`   DOUBLE,
                    `wolfflin_50`  CHAR(1),
                    `wolfflin_1`   CHAR(1),
                    `wolfflin_2`   CHAR(1),
                    `wolfflin_3`   CHAR(1),
                    `wolfflin_4`   CHAR(1),
                    `scleraRing`   CHAR(1),
                    `scleraSpots`  CHAR(1),
                    `time`         DATETIME
                );",
            'neviplot' => "
                CREATE TABLE IF NOT EXISTS `neviplot` (
                    `neviplotid`  {$primaryKeyDef},
                    `studyid`     INTEGER UNSIGNED,
                    `userid`      INTEGER UNSIGNED,
                    `eyepoolid`   INTEGER UNSIGNED,
                    `x`           DOUBLE,
                    `y`           DOUBLE,
                    `size`        VARCHAR(1),
                    `time`        DATETIME
                );",
            'cryptplot' => "
                CREATE TABLE IF NOT EXISTS `cryptplot` (
                    `cryptplotid` {$primaryKeyDef},
                    `studyid`     INTEGER UNSIGNED,
                    `userid`      INTEGER UNSIGNED,
                    `eyepoolid`   INTEGER UNSIGNED,
                    `x`           DOUBLE,
                    `y`           DOUBLE,
                    `size`        VARCHAR(1),
                    `time`        DATETIME
                );",
            'userstudy' => "
                CREATE TABLE IF NOT EXISTS `userstudy` (
                    `userstudyid` {$primaryKeyDef},
                    `studyid`     INTEGER UNSIGNED,
                    `userid`      INTEGER UNSIGNED,
                    `invitetime`  DATETIME,
                    `status`      VARCHAR(1)
                );",
            'colourplot' => "
                CREATE TABLE IF NOT EXISTS `colourplot` (
                    `colourplotid` {$primaryKeyDef},
                    `studyid`      INTEGER UNSIGNED,
                    `userid`       INTEGER UNSIGNED,
                    `eyepoolid`    INTEGER UNSIGNED,
                    `outer_r`      DOUBLE UNSIGNED,
                    `outer_g`      DOUBLE UNSIGNED,
                    `outer_bl`     DOUBLE UNSIGNED,
                    `outer_n`      INT UNSIGNED,
                    `inner_r`      DOUBLE UNSIGNED,
                    `inner_g`      DOUBLE UNSIGNED,
                    `inner_bl`     DOUBLE UNSIGNED,
                    `inner_n`      INT UNSIGNED,
                    `outer_l`      DOUBLE,
                    `outer_a`      DOUBLE,
                    `outer_b`      DOUBLE,
                    `deltae`       DOUBLE,
                    `inner_l`      DOUBLE,
                    `inner_a`      DOUBLE,
                    `inner_b`      DOUBLE,
                    `total_r`      DOUBLE UNSIGNED,
                    `total_g`      DOUBLE UNSIGNED,
                    `total_bl`     DOUBLE UNSIGNED,
                    `total_n`      INT UNSIGNED,
                    `total_l`      DOUBLE,
                    `total_a`      DOUBLE,
                    `total_b`      DOUBLE,
                    `time`         DATETIME
                );"

        );

        foreach ($tables as $key => $createStatement) {
            $results = null;
            $sql = HOST ==='sqlite' ? "PRAGMA table_info([{$key}]);" : "DESCRIBE `{$key}`;";
            $check = $this->connection->prepare($sql);
            try {
                $check->execute();
                $results = $check->fetchAll(PDO::FETCH_OBJ);
            } catch(Exception $e) {
                $results = null;
            }
            if (empty($results)) {
                $create = $this->connection->prepare($createStatement);
                if (!$create->execute()) {
                    $this->error = "Could not create {$key}";
                }
            } else {
                foreach ($results as $result) {
                    $this->dataSet[$key][$result->Field ?? $result->name] = $result->Type ?? $result->type;
                }
            }
        }
    }

    private function encryptMe(string $pass): string
    {
        $algorithm = 'sha256';
        return hash($algorithm, $this->inject($pass));
    }

    private function inject(string $string): string
    {
        $res = '';
        for ($i=0; $i < max(strlen($this->salt), strlen($string)); $i++) {
            if (!empty($this->salt[$i])) {
                $res .= $this->salt[$i];
            }
            if (!empty($string[$i])) {
                $res .= $string[$i];
            }
        }
        return $res;
    }
}
