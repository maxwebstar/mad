<?php
class Application_Model_DbTable_WebsiteLogs extends Zend_Db_Table_Abstract
{
    protected $_name = 'website_logs';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_WebsiteLogs';
    private $_existingData;
    private $_adminId;
    
    public function insert(array $data)
    {
    	$this->_db->insert($this->_name, $data);
    }
    
    public function getExistingData($siteId)
    {
    	if(!$this->_existingData)
    	{
    		$sitesModel = new Application_Model_DbTable_Sites();
    		$this->_existingData = $sitesModel->getSiteInfoByID($siteId);
    	}
    	if(!$this->_adminId)
    	{
    		$this->_adminId = Zend_Auth::getInstance()->getIdentity()->admin_id;    		
    	}    			
    }
	
    public function prepareArray($array)
    {
    	return array_filter(
    			$array,
    			create_function('$el', 'return !empty($el);')
    	);
    }
    
    public function logServingUrlChanges($pubId, $siteId, $formData)
    {
    	if(is_numeric(intval($siteId)))
    	{
    		$this->getExistingData($siteId);
    		if($this->_existingData)
    		{
    			if(isset($formData['ServingURL']))
    			{
					$status = $this->getUrlStatus('ServingURL', $formData);
    				if($status)
    				{
    					$data = array(
    						'PubID' => $pubId,
    						'SiteID' => $siteId,
    						'ServingURL' => $status,
    						'admin_id' => $this->_adminId,
    						'changed' => date("Y-m-d H:i:s")
    					);
    					$this->_db->insert($this->_name, $data);	
    				}
    			}
    		}
    	}	
    }
    
    public function logSiteUrlChanges($pubId, $siteId, $formData)
    {
        if(is_numeric(intval($siteId)))
    	{
    		$this->getExistingData($siteId);
    		if($this->_existingData)
    		{
    			if(isset($formData['SiteURL']))
    			{
					$status = $this->getUrlStatus('SiteURL', $formData);
    				if($status)
    				{
    					$data = array(
    						'PubID' => $pubId,
    						'SiteID' => $siteId,
    						'SiteURL' => $status,
    						'admin_id' => $this->_adminId,
    						'changed' => date("Y-m-d H:i:s")
    					);
    					$this->_db->insert($this->_name, $data);	
    				}
    			}
    		}
    	}
    }
    
    public function logIFramesChanges($pubId, $siteId, $formData)
    {
    	$this->getExistingData($siteId);
    	$status = null;
    	if($this->_existingData['iframe_tags'] != $formData['iframe_tags'])
    	{
    		if($formData['iframe_tags'] == 1)
    			$status = 1;//Enabled
    		elseif($formData['iframe_tags'] == null) 
    			$status = 2;//Disabled    		
    	}
    	if($status)
    	{
    		$data = array(
    				'PubID' => $pubId,
    				'SiteID' => $siteId,
    				'IFrame' => $status,
    				'admin_id' => $this->_adminId,
    				'changed' => date("Y-m-d H:i:s")
    		);
    		$this->_db->insert($this->_name, $data);
    	}
    }
    
    public function logEnabledDisabledChanges($pubId, $siteId, $enabled)
    {
    	$this->getExistingData($siteId);
		if($enabled == 1 OR $enabled == 2)
		{
			$data = array(
					'PubID' => $pubId,
					'SiteID' => $siteId,
					'Enabled' => $enabled,
					'admin_id' => $this->_adminId,
					'changed' => date("Y-m-d H:i:s")
			);
			$this->_db->insert($this->_name, $data);
		}
    }
    
    private function getUrlStatus($column, $formData)
    {
		if(isset($formData[$column]) AND isset($this->_existingData[$column]))
		{
			$status = null;
			$current = $this->prepareArray(preg_split('/[\n]/', $this->_existingData[$column]));
			$new = $this->prepareArray(preg_split('/[\n]/', $formData[$column]));
			if(count($current) > count($new))
			{
				$status = 1; //Deleted
			}
			elseif(count($current) < count($new))
			{
				$status = 2; //Added
			}
			else
			{
				if(count(array_diff($current, $new)) OR
				count(array_diff($new, $current)))
					$status = 3; //Changed
			}
			return $status;	
		}
    }
    
    public function GetLogs($siteId, $pubId = null)
    {
    	//Logs
    	$select_logs = $this->_db->select()
    						->from(array(
    								's' => 'sites'
    									),
    							     array('SiteName', 'created')
    						      )
    						      ->joinLeft(
    						      		array('l' => 'website_logs'),
    						      		'l.SiteID = s.SiteID',
    						      		array('l.id','l.ServingURL', 'l.SiteID' , 'l.PubID', 'l.SiteURL', 'l.IFrame', 'l.Enabled', 'l.AdminNote', 'l.changed', 'l.note_type')
    						      )
    						->joinLeft(
    							  array('u' => 'users'),
    							  'u.id = l.admin_id',
    							  array('name')	
    						)
    						->order(' COALESCE(l.changed, s.created) DESC');
    	if(isset($pubId))
    		$select_logs->where('s.PubID = ?', $pubId);
    	else 
    		$select_logs->where('s.SiteID = ?', $siteId);
    	
    	$logs['logs'] = $this->_db->query($select_logs)->fetchAll();
    	return $logs;
    }
    
    public function AddNote($params)
    {
    	$result = array('status' => 0, 'data' => null);
    	if(isset($params['note']) AND strlen(trim($params['note'])))
    	{
    		if(isset($params['SiteID']) AND isset($params['PubID']) AND isset($params['note_type']))
    		{
    			$pub_id = intval($params['PubID']);
    			$site_id = intval($params['SiteID']);
    			$note = trim($params['note']);
    			$auth = Zend_Auth::getInstance()->getIdentity();
    			$data_to_db = array(
    				'SiteID' => $site_id,
    				'PubID' => $pub_id,
    				'note_type' => $params['note_type'],
    				'AdminNote' => $note,
    				'admin_id' => $auth->admin_id,
    				'changed' => date('Y-m-d H:i:s', time())
    			);
    			if($params['note_type'] == 2 AND $auth->role == 'user')
    			{
    				$data_to_db['admin_id'] = $this->_adminId;
    			}
    			try 
    			{
    				$this->_db->insert($this->_name, $data_to_db);
    				$last_id = $this->_db->lastInsertId($this->_name,$this->_primary);
    				if($last_id)
    				{
    					$select_last = $this->_db->select()
				    						     ->from
				    						     (
				    				    		 		array('l' => 'website_logs'),
				    						     		array('l.id','l.AdminNote', 'l.changed','l.note_type')
				    						     )
				    						     ->joinLeft
				    						     (
				    						     		array('s' => 'sites'),
				    						     		's.SiteID = l.SiteID',
				    						     		array('SiteName', 'created')
				    						     )
				    							 ->joinLeft
				    							 (
						    							array('u' => 'users'),
						    							'u.id = l.admin_id',
						    							array('name')	
				    							 )
				    							->where('l.id = ?', $last_id)
    											->limit(1);
    					$last_note = $this->_db->fetchRow($select_last);
    					$last_note['changed'] = date('m/d/Y H:i',strtotime($last_note['changed']));
    					$result['data'] = $last_note;
    				}
    			}
    			catch (Exception $e)
    			{
    				$result['status'] = 3;	
    			}
    		}
    		else 
    			$result['status'] = 2;	
    	}
    	else
    		$result['status'] = 1;   	
    	return $result;
    }
    
    public function DeleteNote($params)
    {
    	$result = array('status' => 0, 'data' => null);
    	if(isset($params['note_id']) AND strlen(trim($params['note_id'])))
    	{
			$note_id = $params['note_id'];
			try 
			{
				$this->_db->delete($this->_name,'id = '.$note_id);
				$result['data']['note_id'] = $params['note_id'];
			}
			catch(Exception $e)
			{
				$result['status'] = 1;	
			}
    	}
    	else
    		$result['status'] = 1;
    	return $result;
    }
}
?>