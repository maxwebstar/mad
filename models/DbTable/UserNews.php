<?php
/**
 * Description of Sizes
 *
 * @author nik
 */
class Application_Model_DbTable_UserNews extends Zend_Db_Table_Abstract 
{
    protected $_name = 'user_news';
    protected $_rowClass='Application_Model_DbTable_Row_UserNews';

    public function getNews($params)
    {
    	$result = array(
    		'sEcho' => $params['sEcho'],
    		'iTotalRecords' => 0,
    		'iTotalDisplayRecords' => 0,
    		'aaData' => array()
    	);
    	$select = $this->_db->select();
    	$select->from($this->_name);
    	switch($params['iSortCol_0'])
    	{
    		case "0":
    			$select->order('id '.$params['sSortDir_0']);
    			break;
    		case "1":
    			$select->order('title '.$params['sSortDir_0']);
    			break;
    		case "2":
    			$select->order('date '.$params['sSortDir_0']);
    			break;
    		case "3":
    			$select->order('published '.$params['sSortDir_0']);
    			break;
    		case "4":
    			$select->order('access '.$params['sSortDir_0']);
    			break;
    		default:
    			$select->order('id '.$params['sSortDir_0']);
    			break;;
    	}
    	if(strlen($params['sSearch']))
    		$select->where('title LIKE "%'.$params['sSearch'].'%"');
    	if(strtolower($params['iDisplayLength']) != '-1')
    		$select->limit($params['iDisplayLength']);
    	$data = $this->_db->fetchAll($select);
    	if($data)
    	{
    		$result['iTotalRecords'] = count($data);
    		$result['iTotalDisplayRecords'] = count($data);
    		foreach($data as $news_item)
    		{
    			$item = array();
    			$item[] = $news_item['id'];
    			$item[] = $news_item['title'];
    			$item[] = date('m/d/Y',strtotime($news_item['date']));
    			switch($news_item['published'])
    			{
    				case "1":
    					$item[] = 'Published';
    					break;
    				case "0":
    					$item[] = 'Not Published';
    					break;
    				default:
    					$item[]  = 'Unknown';
    					break;
    			}
    			switch($news_item['access'])
    			{
    				case "1":
    					$item[] = 'AdX Registered';
    					break;
    				case "2":
    					$item[] = 'Non AdX Registered';
    					break;
    				case "3":
    					$item[] = 'All Users';
    					break;
    				case "4":
    					$item[] = 'Only Burst';
    					break;
    				case "5":
    					$item[] = 'Rubicon publishers only';
    					break;
    				default:
    					$item[] = 'Unknown';
    					break;
    			}
    			$item[] = 0;
    			$item[] = 0;
    			$result['aaData'][] = $item;
    		}	
    	}
    	return $result;
    }
    
    public function addNews($data)
    {
    	if($this->_db->insert($this->_name, $data))
    		return 0;
    	else 
    		return 1;
    }
    
    
    public function saveNews($data,$newsId)
    {
    	$status = $this->update($data,'id = '.$newsId);
    	if($status)
    		return 0;
    	else
    		return 1;
    }
    
    
    public function deleteNews($id)
    {
    	$id = intval($id);
		if($this->_db->delete($this->_name,'id = '.$id))
			return true;
		else 
			return false;		
    }
    
    public function getCurrentNews($id)
    {
    	$id = intval($id);
    	$select = $this->_db->select()
    						->from($this->_name)
    						->where('id = ?',$id)
    						->limit(1);
    	$data = $this->_db->fetchRow($select);
    	return $data;
    }
    
    public function getPreparedNews($auth,$limit = null,$position=0)
    {
    	if($auth)
    	{
    		/*
    		$select_sites = $this->_db->select()
							    	  ->from('tags')
							    	  ->where("user_id = ?",$auth->id)
							    	  ->where("type = 4")
							    	  ->limit(1);
    		$str = $select_sites->__toString();
    		$is_adx_registered = $this->_db->fetchRow($select_sites);
    		*/
    		/*
    		$select_invite_adx = $this->_db->select()
    									   ->from('users',array('inviteRequest','active'))
    									   ->where('id = ?',$auth->id)
    									   ->limit(1);
    		$invite_adx_result = $this->_db->fetchRow($select_invite_adx);
    		*/
    		$select_sitesAdx = $this->_db->select()
							    	  ->from('tags')
							    	  ->where("user_id = ?",$auth->id)
							    	  ->where("type = 4")
							    	  ->limit(1);
    		$str = $select_sitesAdx->__toString();
    		$is_adx_registered = $this->_db->fetchRow($select_sitesAdx);

    		$select_sitesBurst = $this->_db->select()
							    	  ->from('tags')
							    	  ->where("user_id = ?",$auth->id)
							    	  ->where("type = 6")
							    	  ->limit(1);
    		$str = $select_sitesBurst->__toString();
    		$is_burst_registered = $this->_db->fetchRow($select_sitesBurst);

    		$select_sitesRubicon = $this->_db->select()
							    	  ->from('tags')
							    	  ->where("user_id = ?",$auth->id)
							    	  ->where("type = 5")
							    	  ->limit(1);
    		$str = $select_sitesRubicon->__toString();
    		$is_rubicon_registered = $this->_db->fetchRow($select_sitesRubicon);

    		$select = $this->_db->select()
					    		->from($this->_name)
					    		->where('published = 1')
					    		->order('date DESC');
    		if($limit)
    			$select->limit($limit);
    		/*
    		if($is_adx_registered['type'] == 4 && $is_burst_registered['type'] == 6 && $is_rubicon_registered['type'] == 5)	
    			$select->where('access = 1 OR access = 2 OR access = 4 OR access = 5 OR access = 3');
    		elseif($is_adx_registered['type'] == 4)
    			$select->where('access = 1 OR access = 3');
    		elseif($is_burst_registered['type'] == 6)
    			$select->where('access = 2 OR access = 4 OR access = 3');    			
    		elseif($is_rubicon_registered['type'] == 5)
    			$select->where('access = 2 OR access = 5 OR access = 3');    			
    		else
    			$select->where('access = 2 OR access = 3');*/

			$whereStr = " ";
			$setOR = null;
					
    		if($is_adx_registered['type'] == 4){
    			$setOR=1;
    			$whereStr.=" access = 1 OR access = 3 ";
    		}
    		
			if($is_burst_registered['type'] == 6){
				if($setOR==1)
    				$whereStr.=" OR access = 2 OR access = 4 OR access = 3 ";
    			else{
	    			$whereStr.=" access = 2 OR access = 4 OR access = 3 ";
	    			$setOR=1;
    			}
    		}
    		
			if($is_rubicon_registered['type'] == 5){			
				if($setOR==1)
    				$whereStr.=" OR access = 2 OR access = 5 OR access = 3 ";
    			else{
	    			$whereStr.=" access = 2 OR access = 5 OR access = 3 ";
	    			$setOR=1;
    			}
			}

			if($position==1)
				$select->where('position_left=1');
			elseif($position==2)
				$select->where('position_above=1');

			if($setOR)
				$select->where($whereStr);
			
    		$str = $select->__toString();
    		$data = $this->_db->fetchAll($select);
    		$result = array();
    		foreach($data as $news_item)
    		{
    			$item = array();
    			$item['title'] = $news_item['title'];
    			$item['text'] = $news_item['text'];
    			$item['date'] = $news_item['date'];
    			$result[] = $item;
    		}
    		return $result;
    	}
    }
}