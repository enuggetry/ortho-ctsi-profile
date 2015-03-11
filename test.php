<?php
header('Content-Type: text/html; charset=utf-8');

class ctsi_row {
    public $pub_id;
    public $pub_priority;
    function __construct($id=0,$pri=50){
        $this->pub_id = $id;
        $this->pub_priority = $pri;
    }
};

class ctsi_meta_stor {
    public $def_num_rows;
    public $def_priority;
    public $publist;    // array 
    function __construct($rows=10,$pri=50) {
        $this->def_num_rows = $rows;
        $this->def_priority = $pri;
        $this->publist = array();
    }
    public function addpub($id,$pri){
        array_push($this->publist,new ctsi_row($id,$pri));
    }
    public function delpub($pubid){
        
    }
    public function pub_isset($pubid){
        
    }
    public function getpri($pubid){
        
    }
};

$data = new ctsi_meta_stor();
//$data->$def_num_rows = 10;
//$data->$def_priority = 50;
$data->addpub("1138","20");
$data->addpub("222","30");

print_r($data);        
        
?>
