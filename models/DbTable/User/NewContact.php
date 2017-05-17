<?php
class Application_Model_DbTable_User_NewContact extends Zend_Db_Table_Abstract
{
    protected $_name = 'user_new_contact';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_User_NewContact';
    
    public function getData($PubID){
                        
        $sql = $this->select()->where('PubID = ?', $PubID);
        
        $data = $this->fetchRow($sql);

        if($data) return $data;
        else      return $this->createRow();        
    }
    
    public function getNum()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('COUNT(id) AS count'));
        
        $data = $this->_db->query($sql)->fetchAll();
        
        return $data[0]['count'];
    }
    
    public function getApprovedContacts($params)
    {
        set_time_limit(0);
        //output
        $output = array(
                'sEcho' => intval($params['sEcho']),
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'aaData' => array()
        );
        //Base select
        $select = $this->_db->select()
        ->from(array('u' => 'users'),array('u.id','u.email','u.company','u.date_contact_approve'));
        $str = $select->__toString();

        //Sorting
        if(isset($params['iSortCol_0']) AND isset($params['sSortDir_0']))
        {
        	if(strtolower($params['sSortDir_0']) == 'asc' OR strtolower($params['sSortDir_0'] == 'desc'))
	        	switch(intval($params['iSortCol_0']))
	        	{
	        		case 0:
	        			$select->order('u.id ' . $params['sSortDir_0']);
	        			break;
	        		case 1:
	        			break;
	        		case 2:
	        			break;
	        		case 3:
	        			$select->order('u.email ' . $params['sSortDir_0']);
	        			break;
	        		case 4:
	        			$select->order('u.name ' . $params['sSortDir_0']);
	        			break;
	        		case 5:
	        			$select->order('u.date_contact_approve ' . $params['sSortDir_0']);
	        			break;
	        		case 6:
	        			break;
	        	}
        }
        //Number of viewing items
        if (isset($params['iDisplayStart']) && $params['iDisplayLength'] != '-1')
        {
        	$select = $select->limit(intval($params['iDisplayLength']));
        }
        //Search
        if(isset($params['sSearch']) AND strlen($params['sSearch']))
        {
        	$search_text = $this->_db->quote('%' . $params['sSearch'] . '%');
        	if($params['bSortable_0'] == true)        		
        		$select->orWhere('u.name LIKE ' . $search_text . ' AND date_contact_approve IS NOT NULL');
        	if($params['bSortable_3'] == true)
        		$select->orWhere('u.email LIKE ' . $search_text . ' AND date_contact_approve IS NOT NULL');
        	if($params['bSortable_4'] == true)
        		$select->orWhere('u.name LIKE ' . $search_text . ' AND date_contact_approve IS NOT NULL');
        }else
        //Where Approved
        $select->where('date_contact_approve IS NOT NULL');
        $str = $select->__toString();
        $data = $this->_db->query($select)->fetchAll();
        $output['iTotalRecords'] = count($data);
        $output['iTotalDisplayRecords'] = count($data);
        foreach($data as $contactInfo)
        {
        	$item = array(
        			$contactInfo['id'],
        			null,
        			null,
        			$contactInfo['email'],
        			$contactInfo['name'],
        			date('Y-m-d',strtotime($contactInfo['date_contact_approve'])),
        			$contactInfo['id'],
        			$contactInfo['id'],
        			$contactInfo['id'],
        	);
        	$output['aaData'][] = $item;
        }
    	return $output;
	}
    
    public function getPendingContacts($params)
    {
        set_time_limit(0);
        //output
        $output = array(
                'sEcho' => intval($params['sEcho']),
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'aaData' => array()
        );
        //Setting fetch mode
        $this->_db->setFetchMode(Zend_Db::FETCH_NUM);
        //Base select
        $select = $this->_db->select()
        ->from(array('unc' => 'user_new_contact'),
        		array('unc.PubID',  'unc.email', 'unc.name', 'unc.created'))
        		->joinLeft(array('u' => 'users'),'unc.PubID = u.id',array('u.email', 'u.name'));
        //Sorting
        if(isset($params['iSortCol_0']) AND isset($params['sSortDir_0']))
        {
        	if(strtolower($params['sSortDir_0']) == 'asc' OR strtolower($params['sSortDir_0'] == 'desc'))
	        	switch(intval($params['iSortCol_0']))
	        	{
	        		case 0:
	        			$select->order('unc.PubID ' . $params['sSortDir_0']);
	        			break;
	        		case 1:
	        			break;
	        		case 2:
	        			break;
	        		case 3:
	        			$select->order('unc.email ' . $params['sSortDir_0']);
	        			break;
	        		case 4:
	        			$select->order('unc.name ' . $params['sSortDir_0']);
	        			break;
	        		case 5:
	        			$select->order('unc.created ' . $params['sSortDir_0']);
	        			break;
	        		case 6:
	        			break;
	        	}
        }
        //Number of viewing items
        if (isset($params['iDisplayStart']) && $params['iDisplayLength'] != '-1')
        {
        	$select = $select->limit(intval($params['iDisplayLength']));
        }
        //Search
        if(isset($params['sSearch']) AND strlen($params['sSearch']))
        {
        	$search_text = $this->_db->quote('%' . $params['sSearch'] . '%');
        	if($params['bSortable_0'] == true)        		
        		$select->orWhere('unc.PubID LIKE ' . $search_text);
        	if($params['bSortable_1'] == true)
        		$select->orWhere('u.email LIKE ' . $search_text);
        	if($params['bSortable_2'] == true)
        		$select->orWhere('u.name LIKE ' . $search_text);
        	if($params['bSortable_3'] == true)
        		$select->orWhere('unc.email LIKE ' . $search_text);
        	if($params['bSortable_4'] == true)
        		$select->orWhere('unc.name LIKE ' . $search_text);
        	if($params['bSortable_0'] == true)
        		$select->orWhere('DATE_FORMAT(unc.created,\'%W %M %Y\') LIKE ' . $search_text);
        }
        $str = $select->__toString();
        $data = $this->_db->query($select)->fetchAll();
        $output['iTotalRecords'] = count($data);
        $output['iTotalDisplayRecords'] = count($data);
        foreach($data as $contactInfo)
        {
        	$item = array(
        			$contactInfo[0],
        			$contactInfo[4],
        			$contactInfo[5],
        			$contactInfo[1],
        			$contactInfo[2],
        			date('Y-m-d',strtotime($contactInfo[3])),
        			$contactInfo[0],
        			$contactInfo[0],
        			$contactInfo[0],
        	);
        	$output['aaData'][] = $item;
        }
    	return $output;
    }   
}
?>
