<?php
class Administrator_CoSiteApprovalsController extends Zend_Controller_Action
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $this->_layout->nav = 'co-site';
        
        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;        
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;                                        
    }    
    
    public function ajaxGetAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('s.SiteName', 'u.email', 's.alexaRank', 's.alexaRankUS', 'cn.name', 's.SiteID', 's.SiteID', 's.SiteID', 's.SiteURL', 'u.id', 's.SiteName');
         $Columns = array('SiteName', 'email', 'alexaRank', 'alexaRankUS', 'name', 'SiteID', 'SiteID', 'SiteID', 'SiteURL', 'id', 'SiteName');
     $likeColumns = array(0 => 'email', 1 => 'SiteName', 2 => 's.SiteID');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "cos.SiteID";
	
	/* DB table to use */
	$sTable = "co_site_approvals AS cos";
        $sJoin = " LEFT JOIN sites AS s ON s.SiteID = cos.SiteID LEFT JOIN users AS u ON u.id = s.PubID LEFT JOIN contact_notification AS cn ON cn.mail=s.approved_by ";
	$sGroup = "";
                
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
				 	mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		foreach ( $likeColumns as $key => $field )
		{
			$sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}	                
        
        $account = (int)$_GET['accounts'];        
        if($account>0){
            if($sWhere == "")
                $sWhere = " WHERE cos.manager='".$account."' ";
            else
                $sWhere.= " AND cos.manager='".$account."' ";
        }
        
        $type= $_GET['type'];
        if(strlen($type)){
            if($sWhere == "")
                $sWhere = " WHERE cos.type='".$type."' ";
            else
                    $sWhere.= " AND cos.type='".$type."' ";
        }
        
	/* Individual column filtering */
	foreach( $likeColumns as $key => $field )
	{
		if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
		}
	}
	
        
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
                $sJoin
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		"; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$sQueryLog = $sQuery;
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	"; 
	$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error()); 
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal); 
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
                $sJoin
                $sWhere  
                $sGroup
	"; 
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
     
	
	$output['sql'] = $sQueryLog;
        
	while ( $aRow = mysql_fetch_array( $rResult ) )
	{
		$row = array();
		for ( $i=0 ; $i<count($Columns) ; $i++ )
		{
			if ( $Columns[$i] == "version" )
			{
				/* Special output formatting for 'version' column */
				$row[] = ($aRow[ $Columns[$i] ]=="0") ? '-' : $aRow[ $Columns[$i] ];
			}
			else if ( $Columns[$i] != ' ' )
			{
				/* General output */
				$row[] = $aRow[ $Columns[$i] ];
			}
		}
		$output['aaData'][] = $row;
	}
             
       
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
	      
    }  
    
    public function approveUserAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering        
        $id = (int)$this->_getParam('id');
        $tableSites = new Application_Model_DbTable_Sites();
        $info  = $tableSites->getSiteByID($id);
        $this->_redirect('/administrator/index/view/id/'.$info['PubID'].'/SiteID/'.$id.'/co_approval/true'); 
    }    
    
    public function approveSiteAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering        
        $id = (int)$this->_getParam('id');
        $this->_redirect('/administrator/sites/viewnew-co/id/'.$id.'/co_approval/true');                
    } 
    
    public function sendAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $siteID = (int) $this->_getParam('id');
        $user = (int) $this->_getParam('user');
        
        $tableSites = new Application_Model_DbTable_Sites();
        
        $sqlSite = $tableSites->select()->setIntegrityCheck(false)
                           ->from(array('s' => 'sites'), 
                                  array('s.SiteName', 's.SiteID', 's.PubID'))
                           ->where('s.SiteID = ?', $siteID)
                           ->join(array('u' => 'users'),('s.PubID = u.id'),
                                  array('u.email', 'u.name'));

        $dataSite = $tableSites->fetchRow($sqlSite);            
        
        if($this->getRequest()->isPost()){
            if($this->_getParam('subject') && $this->_getParam('text')){
                                
                $type = $this->_getParam('type');
                $text = $this->_getParam('text');
                $subject = $this->_getParam('subject');                    
                
                $tableSites->rejectSite($siteID);
                
                if($user==1){
	                $auth = Zend_Auth::getInstance()->getIdentity();
	                $tableUsers = new Application_Model_DbTable_Users();
	                $tableUsers->rejectUser($dataSite->PubID, $auth->email);
                }
                                
                $coApprovedModel = new Application_Model_DbTable_CoSiteApprovals();
                $coApprovedModel->delete("SiteID='$siteID'");
                
                if($type==1){
                    $mail = new Zend_Mail();
                    $mail->setFrom(Application_Model_DbTable_Registry_Setting::getByName('admin_email'), Application_Model_DbTable_Registry_Setting::getByName('admin_name'));
                    $mail->addTo($dataSite->email, $dataSite->name);
                    $mail->setSubject($subject);
                    $mail->setBodyHtml($text);
                    $mail->send();   
                    $this->view->message = 'Message has been sent!';
                }else{
                    $this->view->message = 'Done!';
                }                
            }
        }
        $this->view->data = $dataSite;        
        $this->render('send');
    }        
}