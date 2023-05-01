<?php
namespace EdwardsEyes;

class isItAnImage
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function test($file)
    {
        $filename = $this->path .'/' . $file;
        $size = false;
        if (!preg_match('/^\.{1,2}$/', $file) && file_exists($filename)) {
            $gs = getimagesize($filename);
            $size = ($gs[0]>0)?true:false;
        }
        return $size;
    }

    public function setPath($path) 
    {
        $this->path = $path;
    }
}