<?php
class Application_Model_DbTable_Madads_Iframe extends Zend_Db_Table_Abstract
{
    protected $_name = 'madads_url_iframe';
    
    public function getData($SiteID)
    { 
        $sql = $this->_db->select()
                    ->from($this->_name, array('SUM(num) AS num', 'url', 'url_full'))
                    ->where('SiteID = ?', $SiteID)
                    ->group('url_full');
        
        return $this->_db->query($sql)->fetchAll();
    }   
}
?>
