<?php namespace LOE;

require_once __DIR__ . '/../factory.php';

class EpisodeProcessor{

    const DESTDIR = '/var/www/html/LOE/Video/Tv/';

    public $episode;
    private $showDir;
    private $seasonDir;
    private $sourceFile;
    private $targetFile;

    public function __construct($episode){
        $this->episode = LoeFactory::create('tv');
        $this->episode->setFields($episode);
        $this->genreDir = self::DESTDIR . $this->episode->genre . '/';
        $this->showDir = $this->genreDir . $this->episode->show_title . '/';
        $this->seasonDir = $this->showDir . "Season " . $this->episode->season_number . "/";
        $this->sourceFile = $this->episode->file_path;
        $this->_buildTargetFile()
            ->_verifyDestination()
            ->_transfer()
            ->_tryCover();
    }
    private function _buildTargetFile(){
        $pathInfo = pathinfo($this->sourceFile);
        $extension = $pathInfo['extension'];
        $this->targetFile = $this->seasonDir . $this->episode->ep_number . ' - ' . $this->episode->ep_title . '.' . $extension;
        return $this;
    }
    private function _verifyDestination(){
        if(!is_dir($this->genreDir) && !mkdir($this->genreDir)){
            $error = error_get_last();
            $exceptionStr = 'Failed to mkdir: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }
        if(!is_dir($this->showDir) && !mkdir($this->showDir)){
            $error = error_get_last();
            $exceptionStr = 'Failed to mkdir: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }
        if(!is_dir($this->seasonDir) && !mkdir($this->seasonDir)){
            $error = error_get_last();
            $exceptionStr = 'Failed to mkdir: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }
        return $this;
    }
    private function _transfer(){
        if(!copy($this->sourceFile,$this->targetFile)){
            $error = error_get_last();
            $exceptionStr = 'Failed to copy: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }else{
            $this->episode->file_path = $this->targetFile;
            if($this->episode->season_number <= 9){
                $this->episode->cover_path = $this->showDir . 'covers/S0' . $this->episode->season_number . 'cover.jpg';
            }else{
                $this->episode->cover_path = $this->showDir . 'covers/S' . $this->episode->season_number . 'cover.jpg';
            }
            $this->episode->create();
            if(!unlink($this->sourceFile)){
                $error = error_get_last();
                $exceptionStr = 'Failed to cleanup: ' . $error['message'];
            }
        }
        return $this;
    }
    private function _tryCover(){
        $showDir = dirname($this->episode->cover_path);
        $sourceFile = dirname($this->sourceFile) . "/cover.jpg";
        if(!is_dir($showDir) && !mkdir($showDir)){
            $error = error_get_last();
            $exceptionStr = 'Failed to mkdir: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }
        if(is_file($sourceFile) && !copy($sourceFile,$this->episode->cover_path)){
            $error = error_get_last();
            $exceptionStr = 'Failed copying cover: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }elseif(is_file($sourceFile) && !unlink($sourceFile)){
            $error = error_get_last();
            $exceptionStr = 'Failed to cleanup: ' . $error['message'];
            throw new \Exception($exceptionStr);
        }
        return $this;
    }
    private function trySubtitles(){
        return $this;
    }
    private function _cleanUp(){
        return $this;
    }
}
