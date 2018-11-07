<?php namespace LOE;

require_once __DIR__ . '/../loeBase.php';

class Episode extends LoeBase{

    const DB = 'loe';
    const PRIMARYKEY = 'UID';
    const TABLE = 'tv';

    public $UID;
    public $show_title;
    public $genre;
    public $season_number;
    public $season_year;
    public $ep_number;
    public $runtime;
    public $cover_path;
    public $file_path;
    public $ep_title;

    public function __construct($UID = null){
        parent::__construct(self::DB,self::TABLE,self::PRIMARYKEY,$UID);
        $this->file_path = $this->_cleanFilePath($this->file_path);
        $this->cover_path = $this->_cleanFilePath($this->cover_path);
        $this->_cleanProperties();
    }
    public static function search($key,$value){
        $data = array();
        $ids = parent::search(self::DB,self::TABLE,self::PRIMARYKEY,$key,$value);
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
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
