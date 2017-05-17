<?php
class Application_Model_DbTable_CheckTask extends Zend_Db_Table_Abstract
{
    protected $_name = 'check_task';
    protected $_primary = 'id';
    
    public function getData()
    {
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        $email = isset($dataAuth->email) ? $dataAuth->email : 0;
        
        $sql = $this->_db->select()
                    ->from(array('t' => $this->_name), array('t.id', 't.name', 't.link'))
                    ->where('t.for IN ("all", "'.$email.'")')
                    ->joinLeft(array('tu' => 'check_task_user'),'tu.email = "'.$email.'" AND tu.task_id = t.id AND tu.date = "'.date('Y-m-d').'"',
                               array('IF(tu.id, 1, 0) AS status'))
                    ->order('t.position ASC');
        
        
    
        return $this->_db->query($sql)->fetchAll();     

    }
    
    public function getAllData(&$dataAuth)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('position ASC');        
                
        if($dataAuth->role != 'super'){ $sql->where('for = ?', $dataAuth->email);
                                        $sql->orWhere('for = ?', 'all'); }
                                        
        return $this->_db->query($sql)->fetchAll();                                
    } 
    
    
}