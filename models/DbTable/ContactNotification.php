<?php
class Application_Model_DbTable_ContactNotification extends Zend_Db_Table_Abstract
{
    protected $_name = 'contact_notification';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_ContactNotification';
    
    public function getRandomID($where='1=1')
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('id'))
                    ->where('status = 1')
                    ->where($where)
                    ->order('RAND()');
        
        $data = $this->_db->query($sql)->fetch();
        
        return $data['id'];
    }
    
    public function getAllData()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('id ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getActiveContact()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('status = 1')
                    ->order('id ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getDataByStaffID($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('staff_id = ?', $id);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getPublisherContact($PubID)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->joinLeft('users', 'users.account_manager_id='.$this->_name.'.id', array())                    
                    ->where('users.id = '.$PubID);
                            
        return $this->getAdapter()->fetchRow($sql);	    
    }
    
    public function getDataByID($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('id = ?', $id);
        
        return $this->_db->query($sql)->fetch();
    }    
    
    public function getIdByMail($mail)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('id'))
                    ->where('mail = "'.$mail.'"');
        
        $dataMy = $this->_db->query($sql)->fetch();
        
        if($dataMy){
        
            return $dataMy['id'];
        
        }else{
        
            $sql = $this->_db->select()
                        ->from($this->_name, array('id'))
                        ->order('RAND()');

            $dataRa = $this->_db->query($sql)->fetch();
            
            return $dataRa['id'];        
        }
    }    
    
    public function getDataByEmail($email)
    {
        $sql = $this->_db->select()
        ->from($this->_name, array('*'))
        ->where('mail = ?', $email);
    
        return $this->_db->query($sql)->fetch();
    }
}