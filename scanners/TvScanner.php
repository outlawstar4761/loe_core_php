<?php namespace LOE;

require_once __DIR__ . '/../factory.php';

class TvScanner{

    const ROOTDIR = "/var/www/html/LOE/holding_bay/tv/";
    const LASTSLASHPAT = '/[^\/]+$/';
    const EPNUMPAT = '/S0[0-9]E[0-9]{2}/';
    const SEASONPAT = '/Season\s([0-9]{1,2})/';

    private $knownExtensions = array("mp4","MP4","avi","AVI","mkv","MKV");
    private $episodeCount;
    public $episodes = array();
    public $shows = array();

    public function __construct(){
        $this->episodeCount = 0;
        $this->_openPermissions()
            ->_scanForever(self::ROOTDIR)
            ->_sortShows();
    }
    private function _openPermissions(){
        $results = shell_exec('sudo chmod -R 777 /var/www/html/LOE/holding_bay/tv/');
        if($results){
            die($results);
        }
        return $this;
    }
    private function _scanForever($dir){
        $results = scandir($dir);
        for($i = 0; $i < count($results);$i++){
            if($dir != self::ROOTDIR){
                $tester = $dir . "/" . $results[$i];
            }else{
                $tester = $dir . $results[$i];
            }
            if($results[$i] == "." || $results[$i] == ".."){
                continue;
            }elseif(is_file($tester)){
                $fileInfo = pathinfo($tester);
                if(in_array($fileInfo["extension"],$this->knownExtensions)){
                    $this->_buildEpisodeData($tester);
                }
            }elseif(is_dir($tester)){
                $this->_scanForever($tester);
            }else{
                continue;
            }
        }
        return $this;
    }
    private function _buildEpisodeData($path){
        $e = LoeFactory::create('tv');
        $e->file_path= $path;
        $e->UID = $this->episodeCount++;
        if(preg_match(self::LASTSLASHPAT,dirname(dirname($path)),$matches)){
            $e->show_title = $matches[0];
        }
        if(preg_match(self::LASTSLASHPAT,$path,$matches)){
            $e->ep_title = $matches[0];
        }
        if(preg_match(self::LASTSLASHPAT,dirname(dirname(dirname($path))),$matches)){
            $e->genre = $matches[0];
        }
        if(preg_match(self::EPNUMPAT,$path,$matches)){
            $e->ep_number = $matches[0];
        }
        if(preg_match(self::SEASONPAT,$path,$matches)){
            $e->season_number = (int)$matches[1];
        }
        $this->episodes[] = $e;
        return $this;
    }
    private function _sortShows(){
        foreach($this->episodes as $episode){
            $seasonStr = 'Season ' . $episode->season_number;
            $this->shows[$episode->show_title][$seasonStr][] = $episode;
        }
        return $this;
    }
}
