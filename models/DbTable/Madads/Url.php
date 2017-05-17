<?php
class Application_Model_DbTable_Madads_Url extends Zend_Db_Table_Abstract
{
    protected $_name = 'madads_url';
    
    public function getData($SiteID)
    { 
        $sql = $this->_db->select()
                    ->from($this->_name, array('SUM(num) AS num', 'url'))
                    ->where('SiteID = ?', $SiteID)
                    ->group('url');
        
        return $this->_db->query($sql)->fetchAll();
    }   
}
?>
