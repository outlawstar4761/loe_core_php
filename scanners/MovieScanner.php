<?php namespace LOE;

require_once __DIR__ . '/../factory.php';

class MovieScanner{

    const ROOTDIR = "/var/www/html/LOE/holding_bay/movies";
    const WEBROOTPATTERN = "/\/var\/www\/html/";
    const MOVIES = 'movies';
    const YEARPATTERN1 = "/\(/";
    const YEARREPLACEMENT1 = "/\((.*)/";
    const YEARREPLACEMENT2 = "/[0-9]{4}(.*)/";
    const YEARPATTERN2 = "/[0-9]{4}/";

    public $movies = array();
    private $movieDirs = array();
    private $movieFiles = array();
    private $titles = array();
    private $xmlFiles = array();
    private $knownExtensions = array("mp4","MP4","avi","AVI","mkv","MKV");

    public function __construct(){
        $this->_scanForever(self::ROOTDIR);
    }
    private function _openPermissions(){
        $cmd = "sudo chmod 777 -R " . self::ROOTDIR;
        $results = shell_exec($cmd);
        if($results){
            throw new \Exception($results);
        }
        return $this;
    }
    protected function _scanForever($dir){
        $results = scandir($dir);
        foreach($results as $result){
            if($result == '.' || $result == '..'){
                continue;
            }
            $testPath = $dir . '/' . $result;
            if(is_file($testPath) && in_array(pathinfo($testPath)['extension'],$this->knownExtensions)){
                $this->_parseResult($testPath);
            }elseif(is_dir($testPath)){
                $this->_scanForever($testPath);
            }
        }
        return $this;
    }
    protected function _parseResult($result){
        $titleStr = pathinfo(pathinfo($result)['dirname'])['basename'];
        if(preg_match(self::YEARPATTERN1,$titleStr,$matches)){
            $titleStr = preg_replace(self::YEARREPLACEMENT1,'',$titleStr);
        }elseif(preg_match(self::YEARPATTERN2,$titleStr,$matches)){
            $titleStr = preg_replace(self::YEARREPLACEMENT2,'',$titleStr);
        }
        $searchResult = \Imdb::search($titleStr);
        if(!$searchResult){
            $this->exceptions[] = $titleStr;
        }else{
            $genres = explode(",",$searchResult->Genre);
            $movie = LoeFactory::create(self::MOVIES);
            $movie->title = $searchResult->Title;
            $movie->relyear = $searchResult->Year;
            $movie->rating = $searchResult->Rated;
            $movie->genre = $genres[0];
            $movie->genre2 = (isset($genres[1])) ? $genres[1] : null;
            $movie->genre3 = (isset($genres[2])) ? $genres[2] : null;
            $movie->file_path = preg_replace(self::WEBROOTPATTERN,'',$result);
            $movie->director = $searchResult->Director;
            $movie->description = $searchResult->Plot;
            $movie->run_time = $searchResult->Runtime;
            $movie->cover_path = $searchResult->Poster;
            $this->movies[] = $movie;
        }
        return $this;
    }
}
