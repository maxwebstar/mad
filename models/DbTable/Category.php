<?php
class Application_Model_DbTable_Category extends Zend_Db_Table_Abstract 
{
    protected $_primary = 'id';
    protected $_name = 'category';
    
    public function getAllData()
    {
        $sql = $this->_db->select()
                   ->from($this->_name, array('*'))
                   ->order('name ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
}
?>
