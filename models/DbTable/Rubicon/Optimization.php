<?php
class Application_Model_DbTable_Rubicon_Optimization extends Zend_Db_Table_Abstract
{
    protected $_name = 'madads_rubicon_optimization';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_Rubicon_Optimization';
    
    public function insertDataZone($data)
    {
        $this->insert(array(
            'SiteID' => $data['SiteID'],
            'zone'   => $data['Zone'],
            'query_date' => $data['Date'],
            'impressions' => $data['Impressions'],
            'allocated_impressions' => $data['Paid_Impressions'],
            'rate'    => $data['Rate'],
            'revenue' => $data['Revenue'],
            'ecpm'    => $data['eCPM'],
        ));
    }
    
    public function insertDataKeyword($data)
    {
        $this->insert(array(
            'SiteID' => $data['SiteID'],
            'zone'   => $data['Zone'],
            'query_date' => $data['Date'],
            'impressions' => $data['Impressions'],
        ));
    }
    
    public function deleteData($SiteID, $date)
    {
        $this->delete(array("query_date = '$date'", "SiteID = '$SiteID'"));    
    }
}
?>
