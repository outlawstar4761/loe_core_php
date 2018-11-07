<?php namespace LOE;

require_once __DIR__ . '/../loeBase.php';

class Movie extends LoeBase{

    const DB = 'loe';
    const PRIMARYKEY = 'UID';
    const TABLE = 'movies';

    public $title;
    public $relyear;
    public $genre;
    public $genre2;
    public $genre3;
    public $director;
    public $description;
    public $run_time;
    public $file_path;
    public $cover_path;
    public $rating;

    public function __construct($UID = null){
        parent::__construct(self::DB,self::TABLE,self::PRIMARYKEY,$UID);
        $this->file_path = $this->_cleanFilePath($this->file_path);
        $this->cover_path = $this->_cleanFilePath($this->cover_path);
        $this->_cleanProperties();
    }
    public static function browseGenre($key,$value){
        $data = array();
        $results = $GLOBALS['db']
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("$key","=","'" . $value . "'")
            ->get();
        while($row = mysqli_fetch_assoc($results)){
            $data[] = new self($row[self::PRIMARYKEY]);
        }
        return $data;
    }
    public static function search($key,$value){
        $data = array();
        if(strtolower($key) == "genre"){
            $keys = array('genre','genre2','genre3');
            foreach($keys as $key){
                $data = array_merge($data,self::browseGenre($key,$value));
            }
            return $data;
        }
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