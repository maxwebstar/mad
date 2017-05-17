<?php
class Application_Model_DbTable_Payment_Level extends Zend_Db_Table_Abstract
{
    public $_min = 0;
    public $_max = 1000000;
    
    protected $_name = 'payments_level';
    protected $_primary = 'id';
    
    public function getDataByValue($revenue)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('min <= ?', $revenue)
                    ->where('max >= ?', $revenue);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getDataByID($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('id = ?', $id);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getAllData()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('position ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
}
?>
