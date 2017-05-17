<?php
class Application_Model_DbTable_RecruitingTemplate extends Zend_Db_Table_Abstract
{
    protected $_name = 'recruiting_email_templates';
    protected $_primary = 'id';
    
    public function getDataByID($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('id = ?', $id);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getDataByOrder($orderID)
    {        
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('`order` = ?', $orderID)
                    ->order('date_created ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
}