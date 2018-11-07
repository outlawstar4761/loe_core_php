<?php namespace LOE;

require_once __DIR__ . '/../factory.php';

class MovieProcessor{

    const LOE = 'LOE';
    const MOVIES = 'movies';
    const ROOTDIR = "/var/www/html/LOE/Video/Movies/";
    const DBROOT = '/var/www/LOE/Video/Movies/';
    const SOURCE = '/var/www/html';
    const RECOVCODE = 'N/A';

    public $movie;
    private $targetDir;
    private $rmDir;

    public function __construct($movie){
        $movie->title = preg_replace("/\'/","",$movie->title);
        $movie->description = preg_replace("/\'/","",$movie->description);
        $this->movie = LoeFactory::create(self::MOVIES);
        $this->movie->setFields($movie);
        $this->targetDir = self::ROOTDIR . $this->_cleanDirPath($this->movie->title) . "/";
        $this->dbDir = self::DBROOT . $this->_cleanDirPath($this->movie->title) . "/";
        $this->_verifyDestination()
            ->_downloadCover()
            ->_transfer()
            ->_checkForSubtitles()
            ->_finalize()
            ->_cleanup($this->rmDir);
    }
    private function _transfer(){
        $sourceFile = self::SOURCE . $this->movie->file_path;
        $targetFile = $this->_buildTargetFile($sourceFile);
        if(!rename($sourceFile,$targetFile)){
            $error = error_get_last();
            throw new \Exception($error['message']);
        }
        return $this;
    }
    private function _buildTargetFile($sourceFile){
        $pathInfo = pathInfo($sourceFile);
        $this->rmDir = $pathInfo['dirname'];
        $extension = $pathInfo['extension'];
        $targetFile = $this->targetDir . 'movie.' . $extension;
        $this->movie->file_path = $this->dbDir . 'movie.' . $extension;
        //$dbFile = $this->dbDir . 'movie.' . $extension;
        return $targetFile;
    }
    private function _verifyDestination(){
        if(is_dir($this->targetDir)){
            if($this->_isRemake()){
                $this->targetDir = self::ROOTDIR . $this->_cleanDirPath($this->movie->title) . ' (' . $this->movie->relyear . ")/";
                $this->dbDir = self::DBROOT . $this->_cleanDirPath($this->movie->title) . ' (' . $this->movie->relyear . ")/";
            }else{
                $UID = $this->_isCrashRecovery();
                if(!$UID){
                    throw new \Exception('Target Dir Exists. Operation appears to not be recovery or remake');
                }else{
                    $this->movie = LoeFactory::create(self::MOVIES,$UID);
                    return $this;
                }
            }
        }
        if(!mkdir($this->targetDir)){
            $error = error_get_last();
            throw new \Exception($error['message']);
        }elseif(!chmod($this->targetDir,0777)){
            $error = error_get_last();
            throw new \Exception($error['message']);
        }
        return $this;
    }
    private function _isCrashRecovery(){
        $coverPath = $this->dbDir . "cover.jpg";
        $results = $GLOBALS['db']
            ->database(self::LOE)
            ->table(self::MOVIES)
            ->select("UID,file_path")
            ->where("cover_path","=","'" . $coverPath . "'")
            ->get();
        if(!mysqli_num_rows($results)){
            throw new \Exception('Existing Dir but unable to find record');
        }else{
            while($row = mysqli_fetch_assoc($results)){
                if($row['file_path'] == self::RECOVCODE){
                    return $row['UID'];
                }
            }
        }
        return false;
    }
    private function _isRemake(){
        $coverPath = $this->dbDir . "cover.jpg";
        $results = $GLOBALS['db']
            ->database(self::LOE)
            ->table(self::MOVIES)
            ->select("relyear")
            ->where("cover_path","=","'" . $coverPath . "'")
            ->get();
        if(!mysqli_num_rows($results)){
            throw new \Exception('Existing Dir but unable to find record');
        }else{
            while($row = mysqli_fetch_assoc($results)){
                if($row['relyear'] != $this->movie->relyear){
                    return true;
                }
            }
        }
        return false;
    }
    private function _cleanDirPath($dir){
        $dir = preg_replace('/:/','',$dir);
        $dir = preg_replace('/!/','',$dir);
        $dir = preg_replace('/\//','',$dir);
        //$dir = preg_replace('/\\/','',$dir);
        return $dir;
    }
    private function _downloadCover(){
        $file = file_get_contents($this->movie->cover_path);
        $targetFile = $this->targetDir . "cover.jpg";
        $dbFile = $this->dbDir . "cover.jpg";
        if(!file_put_contents($targetFile,$file)){
            $error = error_get_last();
            throw new \Exception($error['message']);
        }else{
            $this->movie->cover_path = $dbFile;
        }
        return $this;
    }
    private function _checkForSubtitles(){
        $results = scandir($this->rmDir);
        foreach($results as $result){
            if($result == "." || $result == ".."){
                continue;
            }else{
                $file = $this->rmDir . "/" . $result;
                $fileInfo = pathinfo($file);
                if($fileInfo["extension"] == "srt" || $fileInfo["extension"] == "sub"){
                    $destination = $this->targetDir . "movie." . $fileInfo["extension"];
                    if(!rename($file,$destination)){
                        $error = error_get_last();
                        throw new \Exception($error['message']);
                    }
                }
            }
        }
        return $this;
    }
    private function _finalize(){
        if(isset($this->movie->UID)){
            $this->movie->update();
        }else{
            $this->movie->create();
        }
        return $this;
    }
    private function _cleanup($dir){
        if(is_dir($dir)){
            $results = scandir($dir);
            foreach($results as $result){
                if($result != '.' && $result != '..'){
                    if(is_dir($dir . '/' . $result)){
                        $this->_cleanup($dir . '/' . $result);
                    }else{
                        unlink($dir . '/' . $result);
                    }
                }
            }
        }
        rmdir($dir);
        return $this;
    }
}
