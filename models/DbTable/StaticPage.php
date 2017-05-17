<?php
class Application_Model_DbTable_StaticPage extends Zend_Db_Table_Abstract 
{
    protected $_primary = 'id';
    protected $_name = 'static_page';
    
    public function getAllData()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('id ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getDataByID($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('id = ?', $id);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getDataByName($name)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('name = ?', $name);
        
        return $this->_db->query($sql)->fetch();
    }
    
}
?>
