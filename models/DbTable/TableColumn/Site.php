<?php
class Application_Model_DbTable_TableColumn_Site extends Zend_Db_Table_Abstract
{
    protected $_name = 'table_site_column';
    protected $_primary = 'id';
    
  
    public function getDataByUser($email)
    {                
        $sql = $this->_db->select()
                    ->from(array('sc' => $this->_name),array('sc.name'))
                    ->joinLeft(array('su' => 'table_site_user'),'su.column_id = sc.id AND su.email = "'.$email.'"',
                               array('IF(su.hide, false, true) AS status'));               
        
        $data = $this->_db->query($sql)->fetchAll();
        $result = array();
        foreach($data AS $iter){ $result[$iter['name']] = $iter['status']; }

        return $result;       
                   
    } 
    
    public function getAllDataByUser($email)
    {
         $sql = $this->_db->select()
                    ->from(array('sc' => $this->_name),array('sc.id', 'sc.name', 'sc.label'))
                    ->joinLeft(array('su' => 'table_site_user'),'su.column_id = sc.id AND su.email = "'.$email.'"',
                               array('IF(su.hide, false, true) AS status'))
                    ->order('position ASC');               
        
         return $this->_db->query($sql)->fetchAll();
    }
    
    public function saveColumnStatus($columnID, $email, $hide)
    {     
        $sql = "INSERT INTO table_site_user (column_id, email, hide) VALUES ('".$columnID."', '".$email."', '".$hide."') ON DUPLICATE KEY UPDATE hide = '".$hide."'";
        
        $this->_db->query($sql);
    }
    
    
}
?>
