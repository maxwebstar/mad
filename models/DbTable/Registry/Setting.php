<?php
class Application_Model_DbTable_Registry_Setting {
    
	static protected $_data;
        
        static function get(){
            
		if(self::$_data === null){
                    
		$table = new Application_Model_DbTable_Setting();
                       
                       self::$_data = $table->fetchAll(); }
               	return self::$_data;
	}
       
	static function getByName($name){
           	foreach(self::get() AS $row){
			if($row->name == $name){
				return $row->value;
			}
		}
	}
        
        static function getById($id){
		foreach(self::get() AS $row){
			if($row->id == $id){
				return $row->value;
			}
		}
	}
        
}
?>
