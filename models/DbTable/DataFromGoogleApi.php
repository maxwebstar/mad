<?php
class Application_Model_DbTable_DataFromGoogleApi extends Zend_Db_Table_Abstract
{
    protected $_name = 'data_from_google_api';
    protected $_primary = 'id';
    
    public function inserData($data)
    {
            $sql = " INSERT INTO `data_from_google_api` (`size`, `SizeID`, `PubID`, `SiteID`, `doname`, `date`, `date_start`, `date_end`, `impressions`, `revenue`)
                          VALUES ('".$data['SizeAd']."', '".$data['SizeID']."', '".$data['PubID']."', '".$data['SiteID']."', '".$data['SiteName']."', '".$data['date']."', '".$data['date_start']."', '".$data['date_end']."', '".$data['impressions']."', '".$data['revenue']."') 
         ON DUPLICATE KEY UPDATE `impressions` = VALUES(`impressions`), `revenue` = VALUES(`revenue`) ";
            
            return $this->_db->query($sql);
    }
    
}
?>
