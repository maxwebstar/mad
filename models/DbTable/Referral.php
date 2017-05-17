<?php
class Application_Model_DbTable_Referral extends Zend_Db_Table_Abstract
{
    protected $_name = 'referral';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_Referral';
  
    public function getReferalsByEmail($params)
    {
    	$result = array(
    		'sEcho' => 1,
    		'iTotalRecords' => 1,
    		'iTotalDisplayRecords' => 1,
    		'aaData' => array()
    	);
    	if(isset($params['id']) AND intval($params['id']))
    	{
    		$select_email = $this->_db->select()
                                          ->from('contact_notification',array('mail'))
                                          ->where('id = ?', $params['id'])
                                          ->limit(1);
    		$email = $this->_db->fetchRow($select_email);
    		if(count($email))
    		{
    			$select = $this->_db->select()
                                            ->from(array('r' => 'referral'),array('r.id', 'r.name', 'r.email', 'r.primary'))
                                            ->joinLeft( array('rs' => 'referral_stat'), 'r.id = rs.refID', array('SUM(rs.num_click)', 'SUM(rs.num_registration)', 'SUM(rs.impressions)', 'ROUND(SUM(rs.revenue),2)'))
                                            ->where('email = ?', $email['mail'])
                                            ->orWhere('email IS NULL')
                                            ->group('r.id');
    			$data_count = $this->_db->fetchAll($select);
    			$result['iTotalRecords'] = count($data_count);
    			$result['iTotalDisplayRecords'] = count($data_count);
    			switch($params['iSortCol_0'])
    			{
    				case "0":
    					$select->order('r.id '.$params['sSortDir_0']);
    					break;
    				case "1":
    					$select->order('r.name '.$params['sSortDir_0']);
    					break;
    				case "2":
    					$select->order('SUM(rs.num_click) '.$params['sSortDir_0']);
    					break;
    				case "3":
    					$select->order('SUM(rs.num_registration) '.$params['sSortDir_0']);
    					break;
    				case "4":
    					$select->order('SUM(rs.impressions) '.$params['sSortDir_0']);
    					break;
    				case "5":
    					$select->order('ROUND(SUM(rs.revenue),2) '.$params['sSortDir_0']);
    					break;
    				case "6":
    					$select->order('email '.$params['sSortDir_0']);
    					break;
    				default:
    					$select->order('id '.$params['sSortDir_0']);
    					break;;
    			}
    			if(strtolower($params['iDisplayLength']) != '-1')
    				$select->limit($params['iDisplayLength'],$params['iDisplayStart']);
    			$str = $select->__toString();
    			$data = $this->_db->fetchAll($select);
    			foreach($data as $row)
    			{
    				$item = array();
    				$item[] = $row['id'];
    				$item[] = $row['name'];
    				$item[] = $row['SUM(rs.num_click)'];
    				$item[] = $row['SUM(rs.num_registration)'];
    				$item[] = $row['SUM(rs.impressions)'];
    				$item[] = $row['ROUND(SUM(rs.revenue),2)'];
    				$item[] = $row['email'];
                                $item[] = $row['id'];
    				$item[] = $row['primary'];
    				$item[] = $row['email'];
    				$result['aaData'][] = $item;
    			}
    			$result['sEcho'] = $params['sEcho'];
    		}
    	}
    	return $result;
    }
    
    public function assignReferal($params)
    {
    	$result = array('status' => 0);
    	if(is_array($params))
    	{
    		if(intval($params['id']))
    		{
    			switch($params['action_type'])
    			{
    				case 'assign':
    					{
    						$contact_id = $params['contact_id'];
    						$select_email = $this->_db->select()
                                                                          ->from('contact_notification',array('mail'))
                                                                          ->where('id = ?', $contact_id)
                                                                          ->limit(1);
    						$email_data = $this->_db->fetchRow($select_email);
    						if(count($email_data) AND intval($params['id']))
    						{
    							$this->_db->update('referral',array('email' => $email_data['mail']),'id = '.$params['id']);
    						}
    						$result['status'] = 1;
    					}
    					break;
    				case 'cancel_assign':
    					{
    						if(intval($params['id']))
    						{
    							$this->_db->update('referral',array('email' => null, 'primary' => null),'id = '.$params['id']);
                                                        $this->_db->update('contact_notification',array('ref_id' => null),'ref_id = '.$params['id']);
    						}
    						$result['status'] = 1;	
    					}
    					break;
    			}	
    		}
    	}
    	return $result;    	
    }
    
    public function assignPrimaryReferal($params)
    {
    	$result = array('status' => 0);
    	if(is_array($params))
    	{
    		if(intval($params['id']))
    		{
    			$contact_id = $params['contact_id'];
    			$select_email = $this->_db->select()
    								 ->from('contact_notification',array('mail'))
    								 ->where('id = ?', $contact_id)
    			                     ->limit(1);
    			$email_data = $this->_db->fetchRow($select_email);
    			if(count($email_data) AND intval($params['id']))
    			{
    				$this->_db->update('referral',array('primary' => null),'email =  \''.$email_data['mail'].'\'');
    				$this->_db->update('referral',array('primary' => 1),'id = '.$params['id']);
    				$this->_db->update('contact_notification',array('ref_id' => $params['id']),'mail = \''.$email_data['mail'].'\'');
    			}
    			$result['status'] = 1;
    		}
    	}
    	return $result;
    }
    
    
    public function usersReferralProgramOn($params)
    {
    	if(is_array($params))
    	{
    		$data_to_db = array(
    			'name' => '#'.$params['id'].' - '.$params['name'].' ('.$params['company'].')',
    			'users_email' => $params['email'],
    			'num_click' => 0,
    			'num_registration' => 0,
    			'impressions' => 0,
    			'revenue' => 0
    		);
    		try
    		{
    			$this->_db->insert($this->_name, $data_to_db);
    		}
    		catch( Exception $e)
    		{
    			return false;	
    		}
    	}
    	return true;	
    }
    
    public function usersReferralProgramOff($params)
    {
    	
    	if(is_array($params))
    	{
    		try
    		{
    			$this->_db->delete($this->_name,'`users_email` = \''.$params['email'].'\'');
    		}
    		catch( Exception $e)
    		{
    			return false;	
    		}
    	}
    	
    	return true; 
    }
    
    public function getReferralsReport($user_email,$start_date = false, $end_date = false)
    {
    	$result = '';
    	if(strlen($user_email))
    	{
    		$select_ref_id = $this->_db->select()
							    	   ->from($this->_name,'id')
							    	   ->where('users_email = ?',$user_email)
    								   ->limit(1);
    		$result_ref_id = $this->_db->fetchRow($select_ref_id);
    		if(count($result_ref_id))
    		{
    			$ref_id = $result_ref_id['id'];
    			if(intval($ref_id))
    			{
    				$select_get_report = $this->_db->select()
    											   ->from('referral_stat')
    											   ->where('refID = ?',$ref_id)
    											   ->order('query_date DESC');
    				if($start_date AND $end_date)
    				{
    					$start_date = date('Y-m-d', strtotime($start_date));
    					$end_date = date('Y-m-d', strtotime($end_date));
    					$select_get_report->where('query_date <= \''.$end_date.'\'')
    									  ->where('query_date >= \''.$start_date.'\'');
    				}
    				$str = $select_get_report->__toString();
    				$result_get_report = $this->_db->fetchAll($select_get_report);
    				if(count($result_get_report))
    				{
    					$result = array();
    					foreach($result_get_report as $row)
    					{
    						$report_item = array(
    							'query_date' => $row['query_date'],
    							'paid_impressions' => 0,
                                                        'impressions_denied' => 0,
    							'impressions' => 0,
    							'estimated' => 0,
    							'revenue' => $row['revenue'],
    						);
    						$result[] = $report_item;
    					}	
    				}
    			}
    		}
    	}
    	return $result;
    }
    
    public function getDataByEmail($email)
    { 
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('email = ?', $email);
        
        return $this->_db->query($sql)->fetch();
    }

    public function getPrimaryDataByEmail($email)
    {
    	$sql = $this->_db->select()
    	->from($this->_name, array('*'))
    	->where('email = ?', $email)
    	->where('`primary` = ?', 1);
    
    	return $this->_db->query($sql)->fetch();
    }

    public function getArrOption($exclusionID = FALSE)
    {
        $sql = $this->_db->select()
            ->from('referral', array('id', 'name'))
            ->order('id ASC');

        if($exclusionID){ $sql->where('id != ?', $exclusionID); }

        $data = $this->_db->query($sql)->fetchAll();

        $result = array();
        $result[0] = 'Do Not Assign';

        foreach($data as $iter){ $result[$iter['id']] = $iter['name']; }

        return $result;
    }
}