<?php namespace LOE;

require_once __DIR__ . '/../loeBase.php';

class Anime extends LoeBase{

    const TABLE = 'anime';
    const DB = 'loe';
    const PRIMARYKEY = 'id';

    public $show_title;
    public $japanese_title;
    public $type;
    public $season;
    public $ep_number;
    public $ep_title;
    public $run_time;
    public $rating;
    public $genre;
    public $genre2;
    public $genre3;
    public $description;
    public $release_date;
    public $cover_path;
    public $file_path;

    public function __construct($UID = null){
        parent::__construct(self::DB,self::TABLE,self::PRIMARYKEY,$UID);
        $this->file_path = $this->_cleanFilePath($this->file_path);
        $this->cover_path = $this->_cleanFilePath($this->cover_path);
        $this->_cleanProperties();
    }
    public static function getAll(){
        $data = array();
        $ids = parent::getAll(self::DB,self::TABLE,self::PRIMARYKEY);
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
}
