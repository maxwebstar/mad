<?php
class Application_Model_DbTable_Madads_Psa extends Zend_Db_Table_Abstract
{
    protected $_primary = 'id';
    protected $_name = 'madads_psa';   
    
    public function deleteData($siteID, $url)
    {
        $sql = " DELETE FROM `madads_psa` WHERE `SiteID` = '".$siteID."' AND (`url` = '".$url."' OR `src` = '".$url."')";
        
        return $this->_db->query($sql);
    }
}
?>
