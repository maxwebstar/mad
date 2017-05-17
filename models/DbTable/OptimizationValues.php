<?php
class Application_Model_DbTable_OptimizationValues extends Zend_Db_Table_Abstract
{
    protected $_name = 'optimization_values';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_OptimizationValues';
   
    public function getData($siteId)
    {
    	$select = $this->_db->select()
					    	->from($this->_name)
					    	->where('SiteID = ?',$siteId)
    						->limit(1);
    	$result = $this->_db->fetchAll($select);
		return $result;    		
    }
    
    public function updateData($siteId, $value, $notes)
    {
    	$select = $this->_db->select()
    						->from($this->_name)
    						->where('SiteID = ?',$siteId);
    	if(count($this->_db->fetchAll($select)))
    	{
    		$this->_db->update($this->_name,
    				array(
    						'Value' => $value,
    						'Notes' => $notes
    				),'SiteID = '.$siteId
    		);
    	}
    	else 
    	{
			$this->_db->insert($this->_name,
			    			 array(
			    			 'SiteID' => $siteId,
			    			 'Value' => $value,
			    			 'Notes' => $notes	
			    				  )
			    	);
    	}
    	return array('Value' => $value);
    }
    
}
?>
