<?php
namespace EdwardsEyes\inc;

class answerKey
{

    protected $data;
    protected $freckles;
    protected $crypts;
    protected $userid;
    protected $studyid;
    protected $eyepoolid;
    protected $time;

    public function __construct($userId, $studyId, $eyepoolid)
    {
        $this->data = array();
        $this->freckles = array();
        $this->crypts = array();
        $this->userid = intval($userId);
        $this->studyid = intval($studyId);
        $this->eyepoolid = intval($eyepoolid);
        $this->time = date("Y-m-d H:i:s");
    }

    public function setAnswers($obj)
    {
        $translate = array('no' => 'N', 'yes' => 'Y');
        foreach ($obj as $key => $result) {
            switch (strtolower($key)) {
                case 'access':
                case 'action':
                    break;
                case 'obstructed':
                case 'scleraring':
                case 'scleraspots':
                    $this->data[self::translate($key)] = self::translate($result[0]['response'], $translate);
                    break;
                case 'nevi':
                    self::setFreckles($result);
                    break;
                case 'crypts':
                    self::setCrypts($result);
                    break;
                default:
                    foreach ((array)$result as $idx => $val) {
                        $idxOffset = strtolower($idx);
                        $newKey = implode('_', array(self::translate($key), self::translate($idxOffset)));

                        if (is_array($val) && isset($val['response'])) {
                            $this->data[$newKey] = self::translate($val['response'], $translate);
                        } else {
                            $this->data[$newKey] = $val;
                        }
                    }
            }
        }

    }

    public function setFreckles($arr)
    {
        $translate = array('left' => 'S', 'right' => 'L');
        foreach ((array)$arr as $data) {
            $this->freckles[] = array(
                'x' => $data['x'],
                'y' => $data['y'],
                'size' => self::translate($data['size'], $translate)
            );
        }
    }
    public function setCrypts($arr)
    {
        $translate = array('right' => 'L', 'left' => 'S', 'fsmall' => 'F');
        foreach ((array)$arr as $data) {
            $this->crypts[] = array(
                'x' => $data['x'],
                'y' => $data['y'],
                'size' => self::translate($data['size'], $translate)
            );
        }
    }

    private function translate($str, $arr = null)
    {
        if (empty($arr)) {
            $arr = array(
                'obstructed' => 'obstructed',
                'scaleraRing' => 'scleraRing',
                'scaleraSpots' => 'scleraSpots',
                'contractionFurrows' => 'furrows',
                'wolfflinNodules' => 'wolfflin',
                '0' => 50
            );
        }
        if (!empty($arr[$str])) {
            return $arr[$str];
        } else {
            return $str;
        }
    }
    private function getIds()
    {
        return array(
            'userid' => $this->userid,
            'studyid'=> $this->studyid,
            'time' => $this->time, 
            'eyepoolid' => $this->eyepoolid
        );
    }
    public function getAnswers()
    {
        return array_merge(self::getIds(), $this->data);
    }
    public function getFreckles()
    {
        return array_merge(self::getIds(), $this->freckles);
    }
    public function getCrypts()
    {
        return array_merge(self::getIds(), $this->crypts);
    }
}
