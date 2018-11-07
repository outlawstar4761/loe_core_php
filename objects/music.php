<?php namespace LOE;

require_once __DIR__ . '/../loeBase.php';
require_once __DIR__ . '/../libs/mp3Reader/mp3Reader.php';

class Song extends LoeBase{

    //todo move to such place as is accessible by all LOE objects
    const WEBROOT = '/var/www/html';

    const TABLE = 'music';
    const DB = 'loe';
    const PRIMARYKEY = 'UID';

    public $title;
    public $artist;
    public $album;
    public $year;
    public $track_number;
    public $genre;
    public $band;
    public $length;
    public $publisher;
    public $bpm;
    public $feat;
    public $cover_path;
    public $file_path;
    public $play_count;
    public $last_play;
    public $created_date;
    public $artist_country;

    public function __construct($UID = null){
        parent::__construct(self::DB,self::TABLE,self::PRIMARYKEY,$UID);
        $this->file_path = $this->_cleanFilePath($this->file_path);
        $this->cover_path = $this->_cleanFilePath($this->cover_path);
        $this->_cleanProperties();
    }
    public function getMp3Tags(){
        if(isset($this->created_date)){
            $path = self::WEBROOT . $this->file_path;
        }else{
            $path = $this->file_path;
        }
        $reader = new \Mp3Reader();
        $reader->fileName = $path;
        $reader->getTags();
        return $reader->tagData;
    }
    protected function _writeMp3Tags(){
        $path = self::WEBROOT . $this->file_path;
        $reader = new \Mp3Reader();
        $reader->fileName = $path;
        $reader->constructTags($this->title,$this->artist,$this->album,$this->year,$this->genre,"",$this->track_number);
        if(!$reader->writeTags()){
            return false;
        }
        return true;
    }
    public static function getAll(){
        $data = array();
        $ids = parent::getAll(self::DB,self::TABLE,self::PRIMARYKEY);
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
    public static function search($key,$value){
        $data = array();
        $ids = parent::search(self::DB,self::TABLE,self::PRIMARYKEY,$key,$value);
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
}
//class Artist{
//
//    const TABLE = 'music';
//
//    public function __construct(){}
//    public static function browse(){
//        return Record::browse(self::TABLE,'artist');
//    }
//}
//class Album{
//
//    const TABLE = 'music';
//
//    public function __construct(){}
//    public static function browse(){
//        return Record::browse(self::TABLE,'ablum');
//    }
//}
