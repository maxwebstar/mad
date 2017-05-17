<?php
class Application_Model_DbTable_Sites_NotifiMessage extends Zend_Db_Table_Abstract
{
    protected $_name = 'site_notifi_message';
    protected $_primary = 'id';
    
    ///////// page /////////
    // 1 - denied-url     //
    // 2 - checker        //
    ////////////////////////
    
    public function saveMessage($data)
    {
        $date = date('Y-m-d');
        
        $sql = " INSERT INTO `site_notifi_message` (PubID, SiteID, page, to_mail, to_name, subject, text, updated, created) 
                                            VALUES ('".$data['PubID']."', '".$data['SiteID']."', '".$data['page']."', '".$data['mail']."', '".$data['name']."', '".mysql_escape_string($data['subject'])."', '".mysql_escape_string($data['text'])."', '".$date."', '".$date."')    
                                            ON DUPLICATE KEY UPDATE page = '".$data['page']."', to_mail = '".$data['mail']."', to_name = '".$data['name']."', subject = '".mysql_escape_string($data['subject'])."', text = '".mysql_escape_string($data['text'])."', updated = '".$date."'";
        
        return $this->_db->query($sql);
    }
    
    public function getDataBySite($SiteID)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('SiteID = ?', $SiteID);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getNum()
    {
        $sql = $this->_db->select()
                    ->from(array('sm' => $this->_name), array('*'))
                    ->join(array('s' => 'sites'),('s.SiteID = sm.SiteID'),array(''))
                    ->where('s.status = 3')
                    ->where('sm.fixed = 0');
     
        $this->_db->query($sql);
        
        return $this->_db->fetchOne("select FOUND_ROWS()");
    }
    
    public function getSiteNeedNotifi()
    {
        $result = false;
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        if($auth){
            
            $tableManager = new Application_Model_DbTable_ContactNotification();
            $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");
        
            if($dataManager){
            
                $sql = $this->_db->select()
                            ->from(array('sm' => $this->_name), array('sm.id'))
                            ->join(array('s' => 'sites'),('s.SiteID = sm.SiteID'),array('s.SiteName'))
                            ->join(array('u' => 'users'),('u.id = s.PubID'),array(''))
                            ->where('u.account_manager_id = ?', $dataManager->id)
                            ->where('sm.created < ?', date('Y-m-d', strtotime('-2 day')))
                            ->where('sm.fixed = 0')
                            ->where('s.status = 3')
                            ->order('sm.updated ASC');

                $result = $this->_db->query($sql)->fetch();            
            }
        }
        
        return $result;
    }
   
}
?>
