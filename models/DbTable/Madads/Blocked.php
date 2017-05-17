<?php
class Application_Model_DbTable_Madads_Blocked extends Zend_Db_Table_Abstract
{
    protected $_name = 'madads_blocked';
    
    public function getNum()
    { 
        $sql = $this->_db->select()
                    ->from($this->_name, array('SiteID'))
                    ->group('SiteID');
        
                 $this->_db->query($sql);
        $count = $this->_db->fetchOne('select FOUND_ROWS()');
       
        return $count;
    }   
}