<?php namespace LOE;

require_once __DIR__ . '/../factory.php';

ini_set(max_execution_time, 300);

class MusicScanner{

    const ROOTDIR = "/var/www/html/LOE/holding_bay/music/";
    const ZIPDIR = "/var/www/html/LOE/holding_bay/music/zips/";

    public $songs = array();
    private $songCount;
    private $zips = array();
    private $covers = array();
    public $albums = array();
    public $artists = array();
    private $unknownAlbum = array();
    private $unknownArtist = array();

    private $results = array();

    public function __construct(){
        $this->songCount = 0;
        $this->_openPermissions()
            ->_scanForever(self::ROOTDIR)
            ->_getTags()
            ->_sortAlbums()
            ->_sortArtists();
    }
    private function _openPermissions(){
        $cmd = "sudo chmod 777 -R " . self::ROOTDIR;
        $results = shell_exec($cmd);
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
                if($fileInfo["extension"] == "mp3"){
                    $this->songs[$this->songCount] = new Song();
                    $this->songs[$this->songCount]->file_path = $tester;
                    $this->songCount++;
                }elseif($fileInfo["basename"] == "cover.jpg"){
                    $this->covers[] = $tester;
                }
            }elseif(is_dir($tester)){
                $this->_scanForever($tester);
            }else{
                continue;
            }
        }
        return $this;
    }
    private function _getTags(){
        $i = 0;
        foreach($this->songs as $song){
            $song->UID = $i++;
            $tags = $song->getMp3Tags();
            foreach($tags as $key=>$value){
                $song->$key = html_entity_decode($value);
            }
        }
        return $this;
    }
    private function _sortAlbums(){
        foreach($this->songs as $song){
            if(empty($song->album) || is_null($song->album)){
                $this->unknownAlbum[] = $song;
            }else{
                $this->albums[$song->album][] = $song;
            }
        }
        return $this;
    }
    private function _sortArtists(){
        foreach($this->albums as $album){
            if(empty($album[0]->artist) || is_null($album[0]->artist)){
                $this->unknownArtist[] = $album;
            }else{
                $this->artists[$album[0]->artist][$album[0]->album] = $album;
            }
        }
        return $this;
    }
}
