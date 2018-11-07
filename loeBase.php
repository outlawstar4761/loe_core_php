<?php namespace LOE;

require_once __DIR__ . '/libs/record/record.php';


class LoeBase extends \Record{

    const FILEPATT = '/^.*(?=(\/LOE))/';
    const WEBROOT = '/var/www/html';
    const FILEUNSETERR = 'File path must be set.';

    public function __construct($driver, $database, $table, $id)
    {
        parent::__construct($driver, $database, $table, $id);
    }
    protected function _cleanFilePath($path){
        return html_entity_decode(preg_replace(self::FILEPATT,"",$path));
    }
    protected function _cleanProperties(){
        $reflection = new \ReflectionObject($this);
        $data = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach($data as $obj){
            $key = $obj->name;
            $this->$key = html_entity_decode($this->$key);
        }
        return $this;
    }
    public function verifyLocation(){
        if(!isset($this->file_path)){
            throw new \Exception(self::FILEUNSETERR);
        }
        $path = self::WEBROOT . $this->file_path;
        if(!is_file($path)){
            return false;
        }
        return true;
    }
    public function calculateSize(){
        if(!isset($this->file_path)){
            throw new \Exception(self::FILEUNSETERR);
        }
        if($this->verifyLocation()){
            $path = self::WEBROOT . $this->file_path;
            $fileSize = filesize($path);
            return $fileSize;
        }
        return false;
    }
    public function backup(){
        //todo implement backup solution
    }
}