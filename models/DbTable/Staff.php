<?php
/**
 * Description of Alerts
 *
 * @author nik
 */
class Application_Model_DbTable_Staff extends Zend_Db_Table_Abstract 
{
    //////// status ///////
    // 1 - open (new)    //
    // 2 - closed        //
    ///////////////////////
    
    protected $_name = 'staff';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_Staff';    
    
    public function getAllData()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('name ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getDataByID($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('id = ?', $id);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getDataByEmail($email)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('email = ?', $email);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getDataForSelect()
    {
        $result[''] = 'Not selected';
        
        $data = $this->getAllData();
        
        foreach($data as $iter){ $result[$iter['id']] = $iter['name']; }
        
        return $result;
    }
}