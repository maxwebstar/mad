<?php
class Application_Model_DbTable_Sub extends Zend_Db_Table_Abstract
{
    protected $_name = 'sub';
    protected $_primary = 'id';

    public function getAllData()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('name ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
   
}