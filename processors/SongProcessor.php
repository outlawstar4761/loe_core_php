<?php namespace LOE;

require_once __DIR__ . '/../factory.php';

class SongProcessor{

    const DESTDIR = '/var/www/html/LOE/Music/';

    public $song;
    private $albumDir;
    private $artistDir;
    private $sourceFile;
    private $coverPath;
    private $targetFile;

    public function __construct($song){
        $this->song = LoeFactory::create('music');
        $this->song->setFields($song);
        $this->artistDir = self::DESTDIR . $this->song->artist . "/";
        $this->albumDir = $this->artistDir . $this->song->album . " (" . $this->song->year . ")/";
        $this->sourceFile = $this->song->file_path;
        $this->coverPath = $this->albumDir . "cover.jpg";
        $this->targetFile = $this->albumDir . pathinfo($this->song->file_path,PATHINFO_BASENAME);
        $this->_verifyDestination()
            ->_transfer()
            ->_tryCover()
            ->_cleanUp();
    }
    private function _verifyDestination(){
        if(!is_dir($this->artistDir) && !mkdir($this->artistDir)){
            $error = error_get_last();
            $exceptionStr = 'Failed to create artist Dir: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }
        if(!is_dir($this->albumDir) && !mkdir($this->albumDir)){
            $error = error_get_last();
            $exceptionStr = 'Failed to create album Dir: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }
        return $this;
    }
    private function _transfer(){
        if(!rename($this->sourceFile,$this->targetFile)){
            throw new \Exception('Failed to Transfer');
        }else{
            $this->song->file_path = $this->targetFile;
            $this->song->cover_path = $this->coverPath;
            $this->song->create();
        }
        return $this;
    }
    private function _tryCover(){
        $sourceFile = dirname($this->sourceFile) . "/cover.jpg";
        if(is_file($sourceFile) && !rename($sourceFile,$this->coverPath)){
            $error = error_get_last();
            $exceptionStr = 'Failed moving cover: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }
        return $this;
    }
    private function _cleanUp(){
        $dir = dirname($this->sourceFile);
        if(count(scandir($dir)) == 2 && !rmdir($dir)){
            $error = error_get_last();
            $exceptionStr = "Failed To Remove Dir: " . $error['message'];
            throw new \Exception($exceptionStr);
        }
        return $this;
    }

}
