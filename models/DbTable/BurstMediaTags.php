<?php
class Application_Model_DbTable_BurstMediaTags extends Zend_Db_Table_Abstract
{
    protected $_name = 'burst_media_tags';
    protected $_primary = 'id';
    
	public function getBurst($params = false)
	{
		$result =  array(
    		'sEcho' => $params['sEcho'],
    		'iTotalRecords' => 0,
    		'iTotalDisplayRecords' => 0,
    		'aaData' => array(),
			'params' => $params
    	);
		if(is_array($params))
		{
			$select = $this->_db->select()
								->from(array('b' => $this->_name),array('b.cpm', 'b.SiteID', 'b.AdSize','SUM(b.impressions)','SUM(b.paid_impressions)','ROUND(SUM(b.revenue),2)'))
								->joinLeft(array('s' => 'sites'), 'b.SiteID = s.SiteID', array('SiteName'))
								->joinLeft(array('ds' => 'display_size'), 'b.AdSize = ds.id', 'name')
								->group('b.SiteID');
			if($params['group'] == 'size')
				$select->group(array('b.AdSize', 'b.query_date'));
			switch($params['iSortCol_0'])
	    	{
	    		case "0":
	    			$select->order('s.SiteName '.$params['sSortDir_0']);
	    			break;
	    		case "1":
	    			$select->order('b.AdSize '.$params['sSortDir_0']);
	    			break;
	    		case "2":
	    			$select->order('SUM(b.impressions) '.$params['sSortDir_0']);
	    			break;
	    		case "3":
	    			$select->order('SUM(b.paid_impressions) '.$params['sSortDir_0']);
	    			break;
	    		case "4":
	    			$select->order('b.cpm '.$params['sSortDir_0']);
	    			break;
	    		case "5":
	    			$select->order('SUM(b.revenue) '.$params['sSortDir_0']);
	    			break;
	    		default:
	    			break;
	    	}
	    	if(strlen($params['date']))
	    		$select->where('b.query_date = ?', $params['date']);
	    	if(strlen($params['sSearch']))
	    		$select->where('s.SiteName LIKE "%'.$params['sSearch'].'%"');
	    	if($params['cpm_filter'] != '0')
	    	{
	    		$select->joinLeft(array('c' => 'minimum_cpm'), 'b.SiteID = c.SiteID',array('status'));
	    		$select->where('c.created <= \''.$params['date'].'\'');
	    		$select->where('c.status = 3');
	    		$select->where('c.cpm = ?', $params['cpm_filter']);
	    		$select->order('c.created DESC');
	    	}
	    	$str = $select->__toString();
	    	$total_count = count($this->_db->fetchAll($select));
	    	if(strtolower($params['iDisplayLength']) != '-1')
	    		$select->limit($params['iDisplayLength'],$params['iDisplayStart']);
			$select_result = $this->_db->fetchAll($select);
			$count = count($select_result);
			if($count)
			{
				foreach($select_result as $row)
				{
					$item = array();
					$item[] = $row['SiteName'];
					$item[] = $row['name'];
					$item[] = $row['SUM(b.impressions)'];
					$item[] = $row['SUM(b.paid_impressions)'];
					$item[] = $row['cpm'];
					$item[] = $row['ROUND(SUM(b.revenue),2)'];
					$item[] = $row['SiteID'];
					$item[] = $row['AdSize'];
					$item[] = $row['SiteID'];
					$item[] = $row['AdSize'];
					$result['aaData'][] = $item;
				}
				$result['iTotalDisplayRecords'] = $total_count;
				$result['iTotalRecords'] = $total_count;
			}
				
		}
		return $result;		
	}
	
	public function updateData($params)
	{
		if(is_array($params))
		{
			if(isset($params['data_type']) AND strlen($params['data_type'])
			   AND isset($params['site_id']) AND strlen($params['site_id'])
			   AND isset($params['adsize']) AND strlen($params['adsize'])
			   AND isset($params['query_date']) AND strlen($params['query_date']))
			{
				$result = false;
				$select = $this->_db->select()
									->from($this->_name)
									->where('SiteID = ?', $params['site_id'])
									->where('query_date = ?', $params['query_date'])
									->where('adsize = ?', $params['adsize'])
									->limit(1);
				$select_result = $this->_db->fetchRow($select);
				if(is_array($select_result))
				{
					switch($params['data_type'])
					{
						case "total_impression_data":
							{
								$data_to_db = array(
										'impressions' => $params['value'],
										'query_date' => $params['query_date'],
										'edit' => 1
								);
								try
								{
									$where = '`SiteID` = '.$params['site_id'].' AND `adsize` = '.$params['adsize'].' AND `query_date` = \''.$params['query_date'].'\'';
									$this->_db->update($this->_name, $data_to_db,$where);
									return true;
								}
								catch(Exception $e)
								{
									return false;
								}
							}
							break;
						case "impression_data":
							{
								$data_to_db = array(
										'paid_impressions' => $params['value'],
										'revenue' => ($params['value'] * $select_result['cpm']) / 1000,
										'query_date' => $params['query_date'],
										'edit' => 1
								);
								try
								{
									$where = '`SiteID` = '.$params['site_id'].' AND `adsize` = '.$params['adsize'].' AND `query_date` = \''.$params['query_date'].'\'';
									$this->_db->update($this->_name, $data_to_db,$where);
									return true;
								}
								catch(Exception $e)
								{
									return false;
								}
							}
							break;
						case "cpm_data":
							{
								$data_to_db = array
								(
										'cpm' => $params['value'],
										'revenue' => ($params['value'] * $select_result['paid_impressions']) / 1000,
										'query_date' => $params['query_date'],
										'edit' => 1
								);
								try
								{
									$where = '`SiteID` = '.$params['site_id'].' AND `adsize` = '.$params['adsize'].' AND `query_date` = \''.$params['query_date'].'\'';
									$this->_db->update($this->_name, $data_to_db,$where);
									return true;
								}
								catch(Exception $e)
								{
									return false;
								}		
							}
							break;
						case "revenue_data":
							{
								$data_to_db = array
								(
										'revenue' => $params['value'],
										'query_date' => $params['query_date'],
										'edit' => 1
								);
								try
								{
									$where = '`SiteID` = '.$params['site_id'].' AND `adsize` = '.$params['adsize'].' AND `query_date` = \''.$params['query_date'].'\'';
									$this->_db->update($this->_name, $data_to_db,$where);
									return true;
								}
								catch(Exception $e)
								{
									return false;
								}
							}
							break;
						default:
							return false;
					}
				}
			}
		}
		return false;
	}
	
	public function UpdateAllData($all_params = false)
	{
		$params = array();
		$params['value'] = $all_params['value'];		
		if(is_array($all_params['params']))
		{
			foreach($all_params['params'] as $param_item)
			{
				switch($param_item['name'])
				{
					case "group":
						$params['group'] = $param_item['value'];
						break;
					case "date":
						$params['query_date'] = $param_item['value'];
						break;
					case "sSearch":
						$params['sSearch'] = $param_item['value'];
						break;
					case "cpm_filter":
						$params['cpm_filter'] = $param_item['value'];
						break;
					case "group":
						$params['group'] = $param_item['group'];
						break;
					default: break;
				}
			}
		}		
	
		if(is_array($params))
		{			
			if(isset($params['value']) AND isset($params['query_date']) AND strlen($params['query_date']))
			{
				$select = $this->_db->select()
				->from(array('b' => $this->_name),array('b.cpm', 'b.SiteID', 'b.AdSize','b.paid_impressions','b.revenue'))
				->joinLeft(array('s' => 'sites'), 'b.SiteID = s.SiteID', array('SiteName'))
				->joinLeft(array('ds' => 'display_size'), 'b.AdSize = ds.id', 'name')
				->where('edit IS NULL');
				if(strlen($params['query_date']))
					$select->where('b.query_date = ?', $params['query_date']);
				if(strlen($params['sSearch']))
					$select->where('s.SiteName LIKE "%'.$params['sSearch'].'%"');
				if($params['cpm_filter'] != '0')
		    	{
		    		$select->joinLeft(array('c' => 'minimum_cpm'), 'b.SiteID = c.SiteID',array('status'));
		    		$select->where('c.created <= \''.$params['query_date'].'\'');
		    		$select->where('c.status = 3');
		    		$select->where('c.cpm = ?', $params['cpm_filter']);
		    		$select->order('c.created ASC');
		    	}
				$str = $select->__toString();
				$select_result = $this->_db->fetchAll($select);	
				if(is_array($select_result))
				{
					if($params['group'] == 'group')
					{
						foreach($select_result as $row)
						{
							$select_site = $this->_db->select()	
													 ->from($this->_name)
													 ->where('SiteID = ?', $row['SiteID']);
							$result_sites = $this->_db->fetchAll($select_site);
							if($result_sites)
							{
								foreach($result_sites as $site)
								{
									$data_to_db = array(
											'cpm' => $params['value'],
											'revenue' => ($params['value'] * $site['paid_impressions']) / 1000,
											'query_date' => $params['query_date']
									);
									$where = '`SiteID` = '.$site['SiteID'].' AND `adsize` = '.$site['AdSize'].' AND `query_date` = \''.$params['query_date'].'\'';
									$this->_db->update($this->_name, $data_to_db,$where);
								}
							}
						}	
					}
					else
					{
						foreach($select_result as $row)
						{
							$data_to_db = array
							(
									'cpm' => $params['value'],
									'revenue' => ($params['value'] * $row['paid_impressions']) / 1000,
									'query_date' => $params['query_date']
							);
							try
							{
								$where = '`SiteID` = '.$row['SiteID'].' AND `adsize` = '.$row['AdSize'].' AND `query_date` = \''.$params['query_date'].'\'';
								$this->_db->update($this->_name, $data_to_db,$where);
								continue;
							}
							catch(Exception $e)
							{
								continue;
							}
						}	
					}
					return true;
				}
			}
		}
		return false;
	}
}
?>