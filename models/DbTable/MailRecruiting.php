<?php
class Application_Model_DbTable_MailRecruiting extends Zend_Db_Table_Abstract
{
    protected $_name = 'mail_recruiting';
    protected $_primary = 'id';
    
    ///////// type ////////
    // 1 - contacted     //
    ///////////////////////
    
    public function getDataByRecruiting($id)
    {
        $id = (int) $id;
        
        $sql = "SELECT m.id, 
                       m.mail, 
                       m.subject, 
                       m.text, 
                       m.author,
                       m.recruiting_id,
                       m.staff_id,
                       m.template_id,
                       m.order_id,
                       m.created,
                       IFNULL(s.email, m.author) AS staff_email,
                       IFNULL(s.name, m.author) AS staff_name
                FROM mail_recruiting AS m      
                LEFT JOIN staff AS s ON s.id = m.staff_id
                WHERE m.recruiting_id = '".$id."'
                ORDER BY m.created DESC";
                                                
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getDataByRecruitingType($id, $type)
    {
        $id = (int) $id;
        
        $sql = "SELECT m.id, 
                       m.mail, 
                       m.subject, 
                       m.text, 
                       m.author,
                       m.recruiting_id,
                       m.staff_id,
                       m.template_id,
                       m.order_id,
                       m.created,
                       IFNULL(s.email, m.author) AS staff_email,
                       IFNULL(s.name, m.author) AS staff_name
                FROM mail_recruiting AS m      
                LEFT JOIN staff AS s ON s.id = m.staff_id
                WHERE m.recruiting_id = '".$id."'
                  AND m.type = '".$type."'
                ORDER BY m.created DESC";
                                                
        return $this->_db->query($sql)->fetchAll();
    }    
}