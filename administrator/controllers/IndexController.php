<?php


class Administrator_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'new';  
        
        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;        
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;
        
        $tableReferral = new Application_Model_DbTable_Referral();
        $sql = $tableReferral->select()->order('id');
        $this->view->referral = $tableReferral->fetchAll($sql);
        
        //$users = new Application_Model_DbTable_Users();
        
        //$this->view->newUsers = $users->getNewUsersNoWaiting();
        
    }
    
    public function getAjaxNewAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('u.name', 'u.company', 'u.email', 'u.url', 'u.alexaRank', 'u.alexaRankUS', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'u.id', 'DATE_FORMAT(u.date_eligible, "%Y-%m-%d")', 'u.id', 'u.alexa_country');
         $Columns = array('name', 'company', 'email', 'url', 'alexaRank', 'alexaRankUS', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'id', 'DATE_FORMAT(u.date_eligible, "%Y-%m-%d")', 'id', 'alexa_country');
     $likeColumns = array(0 => 'name', 1 => 'company', 2 => 'u.email', 3 => 'url', 4 => 'alexa_country');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "u.id";
	
	/* DB table to use */
	$sTable = " users AS u ";
	        
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
	
        if($sWhere == ""){
            
            $sWhere = "WHERE (u.active = 0 AND u.reject = 0) AND (u.users_waiting=0 OR u.users_waiting IS NULL) ";
            
        } else {
            
            $sWhere .= " AND (u.active = 0 AND u.reject = 0) AND (u.users_waiting=0 OR u.users_waiting IS NULL) ";
        }

        $account = (int)$_GET['accounts'];        
        if($account>0){
            if($sWhere == "")
                $sWhere = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere.= " AND u.account_manager_id='".$account."' ";
        }
                
        $referral = (int)$_GET['referral'];
        
        if($referral){            
            $sWhere .= " AND u.referral_id='$referral' ";            
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
           
		$sWhere
         
		$sOrder
		$sLimit
		"; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
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
           
                $sWhere  
     
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
    
    public function newWaitingAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'newWaiting';  
        
        $tableReferral = new Application_Model_DbTable_Referral();
        $sql = $tableReferral->select()->order('id');
        $this->view->referral = $tableReferral->fetchAll($sql);
        //$users = new Application_Model_DbTable_Users();        
        //$this->view->newUsers = $users->getNewUsersWaiting();        
    }
    
    public function ajaxNewWaitingAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('u.name', 'u.company', 'u.email', 'u.url', 'u.alexaRank', 'u.alexaRankUS', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'u.id', 'u.id', 'u.id', 'u.alexa_country');
         $Columns = array('name', 'company', 'email', 'url', 'alexaRank', 'alexaRankUS', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'id', 'id', 'id', 'alexa_country');
     $likeColumns = array(0 => 'name', 1 => 'company', 2 => 'u.email', 3 => 'url', 4 => 'alexa_country');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "u.id";
	
	/* DB table to use */
	$sTable = " users AS u ";
	
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
	
        if($sWhere == ""){
            
            $sWhere = "WHERE (u.active = 0 AND u.reject = 0) AND u.users_waiting=1 ";
            
        } else {
            
            $sWhere .= " AND (u.active = 0 AND u.reject = 0) AND u.users_waiting=1 ";
        }

        $referral = (int)$_GET['referral'];
        
        if($referral){            
            $sWhere .= " AND u.referral_id='$referral' ";            
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
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		"; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
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
    
    public function urllookupAction(){
        if (!isset($_SESSION["search"])){
            $_SESSION["search"]="";
        }
        $search = $this->getRequest()->getParam("search");
        if ($search != ""){
            $_SESSION["search"]=$search;
        }
        if (!isset($_SESSION["fullsearch"])){
            $_SESSION["fullsearch"]="0";
        }
        $fullsearch = $this->getRequest()->getParam("fullsearch");
        if ($search != ""){
            $_SESSION["fullsearch"]=$fullsearch;
        }
        $this->view->search=$_SESSION["search"];
        $this->view->fullsearch=$_SESSION["fullsearch"];
    }
    
    public function new2Action()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'new-2';  
        
        $tableReferral = new Application_Model_DbTable_Referral();
        $sql = $tableReferral->select()->order('id');
        $this->view->referral = $tableReferral->fetchAll($sql);	    
    }
    
    public function ajaxNew2Action()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('u.name', 'u.company', 'u.email', 'u.url', 'u.alexaRank', 'u.alexaRankUS', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'u.id', 'u.alexa_country');
         $Columns = array('name', 'company', 'email', 'url', 'alexaRank', 'alexaRankUS', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'id', 'alexa_country');
     $likeColumns = array(0 => 'name', 1 => 'company', 2 => 'u.email', 3 => 'url', 4 => 'alexa_country');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "u.id";
	
	/* DB table to use */
	$sTable = " users AS u ";
	
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
	
        if($sWhere == ""){
            
            $sWhere = "WHERE (u.active = 0 AND u.reject = 0) AND u.users_waiting=2 ";
            
        } else {
            
            $sWhere .= " AND (u.active = 0 AND u.reject = 0) AND u.users_waiting=2 ";
        }

        $referral = (int)$_GET['referral'];
        
        if($referral){            
            $sWhere .= " AND u.referral_id='$referral' ";            
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
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		"; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
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
    
    public function viewAction()
    {
        if($this->_getParam('id')){
            $id = $this->_getParam('id');
            $co_approval_page = $this->_getParam('co_approval');
            
            $users = new Application_Model_DbTable_Users();
            $tableDue = new Application_Model_DbTable_PaymentDue();
            $tableReferral = new Application_Model_DbTable_Referral();
            $tableManagers = new Application_Model_DbTable_ContactNotification();
            $form = new Application_Form_ConfirmUser();
            $auth = Zend_Auth::getInstance()->getIdentity();
            
            $userData = $users->getUserAllInfoById($id);
            
            $this->view->userData = $userData;
            $this->view->revShare = $users->getUserShare($id);
            $this->view->referrals = $tableReferral->fetchAll();
            $this->view->managers = $tableManagers->fetchAll("status=1");
            $this->view->level = $tableDue->getLevelByUserID($id, 100);
            
            if($this->getRequest()->isPost()){ 
                $formData = $this->getRequest()->getPost();
                
                if($formData['action']==1 || $formData['action']==3){
                    $form->getElement('alexa')->clearValidators();
                    $form->getElement('nude')->clearValidators();
                    $form->getElement('porn')->clearValidators();
                    $form->getElement('sex')->clearValidators();
                    $form->getElement('nswf')->clearValidators();
                    $form->getElement('fuck')->clearValidators();
                    $form->getElement('represent_domain')->clearValidators();
                    $form->getElement('authorize_domain')->clearValidators();                    
                    $form->removeElement('revShare_date');
                    $form->removeElement('revShare_price');
                }
                
                if($formData['action'] == 2 AND !$co_approval_page)
                $form->removeElement('inviteURL');                
                
                if($form->isValid($formData) OR !$co_approval_page == true){ 

                	if($this->getRequest()->getPost('revShare_date') || $this->getRequest()->getPost('revShare_price')){ 
                		$shareDates = $this->getRequest()->getPost('revShare_date');
                		$sharePrice = $this->getRequest()->getPost('revShare_price');
                	
                		$users->deleteShare($id, $value);
                		
                		foreach ($shareDates as $key=>$value){
                			if(!empty($value) && !empty($sharePrice[$key])){
                				$users->saveShare($id, $value, str_replace(",", ".", $sharePrice[$key]));
                			}
                		}
                	}                	 

                    if($formData['action']==1){
                        //$users->deleteUser($id);
                        $users->rejectUser($id, $auth->email, $form->getValue('notes'));
                        if($co_approval_page == true)
                        {
                            $coApprovedModel = new Application_Model_DbTable_CoSiteApprovals();
                            $site_id = $this->_getParam('SiteID');
                            $coApprovedModel->delete("SiteID=".$site_id);
                        }                        
                        $this->_redirect('/administrator/');
                    }elseif($formData['action']==2){
                        
                         if($co_approval_page == true){
                            $title = $this->_getParam('title');
                            $message = $this->_getParam('message');
                            $to = $userData['email'];
                            $headers  = 'MIME-Version: 1.0' . "\r\n";
                            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                            $headers .= "From: Publisher Support <".Application_Model_DbTable_Registry_Setting::getByName('admin_email').">\r\n";
                            $message = stripslashes($message);
                            mail($to, $title, $message, $headers);
                        
                            $tableSites = new Application_Model_DbTable_Sites();
                            $site_id = $this->_getParam('SiteID');
                            $tableSites->update(array('co-approved'=>$auth->name, 'co_approved_by' => $auth->email, 'co_approved_date' => date("Y-m-d"), 'status' => 3), "SiteID=".$site_id);
                            $coApprovedModel = new Application_Model_DbTable_CoSiteApprovals();
                            $coApprovedModel->delete("SiteID=".$site_id);
                            $dataForm = array('enable_wire_transfer' => $form->getValue('enable_wire_transfer'),
                                    'notification_control_admin' => $form->getValue('notification_control_admin'),
                                    'inviteURL'  => $form->getValue('inviteURL') ? $form->getValue('inviteURL') : NULL,
                                    'auto_min_cpm' => $form->getValue('auto_min_cpm') == 1 ? NULL : 1,
                                    'approved_by' => $auth->email,
                                    'adx_name' => $form->getValue('adx_name'),
                                    'referral_id'=>$this->getRequest()->getPost('referralID'),
                                    'account_manager_id'=>$this->getRequest()->getPost('account_manager_id') ? $this->getRequest()->getPost('account_manager_id') : NULL,
									'desired_types'=>$form->getValue('desired_types'));
                             
                            if($form->getValue('inviteURL') && empty($userData['date_pending'])){ $dataForm['date_pending'] = date("Y-m-d"); }
                            if($form->getValue('inviteURL') && empty($userData['date_invited'])){ $dataForm['date_invited'] = date("Y-m-d"); }                            
                            $users->confirmUser($id, $dataForm, 1, 0);
                            $this->_redirect('/administrator/co-site-approvals/');
                        }else{
                                $form->removeElement('inviteURL');
                                $dataForm = array('enable_wire_transfer' => $form->getValue('enable_wire_transfer'),
                                        'notification_control_admin' => $form->getValue('notification_control_admin'),
                                        'inviteURL'  => $form->getValue('inviteURL') ? $form->getValue('inviteURL') : NULL,
                                        'auto_min_cpm' => $form->getValue('auto_min_cpm') == 1 ? NULL : 1,
                                        'approved_by' => $auth->email,
                                        'adx_name' => $form->getValue('adx_name'),
                                        'referral_id'=>$this->getRequest()->getPost('referralID'),
                                        'account_manager_id'=>$this->getRequest()->getPost('account_manager_id') ? $this->getRequest()->getPost('account_manager_id') : NULL,
										'desired_types'=>$form->getValue('desired_types'));
                                 
                                if($form->getValue('inviteURL') && empty($userData['date_pending'])){ $dataForm['date_pending'] = date("Y-m-d"); }
                                if($form->getValue('inviteURL') && empty($userData['date_invited'])){ $dataForm['date_invited'] = date("Y-m-d"); }
                                
                                $users->confirmUser($id, $dataForm, 6, 6);
                                //$users->insertPublisher($id, $userData['email']);
                                
		                        if(isset($userData['referral_id'])){ /* plus 1 regist for this referral ID */
		                            
		                            $sqlReferral = $tableReferral->select()->where('id = ?', $userData['referral_id']);
		                            $dataReferral = $tableReferral->fetchRow($sqlReferral);
		                            
		                            if($dataReferral){ 
		                                $tableReferralStat = new Application_Model_DbTable_ReferralStat();
		                                $sql = $tableReferralStat->select()->where('refID = ?', $userData['referral_id'])->where('query_date = ?', date("Y-m-d"));
		                                $dataReferralStat = $tableReferralStat->fetchRow($sql);                                           
		                                $dataReferralStat->num_registration += 1;
		                                //$dataReferral->save(); 
		                                
		                                $sql = "INSERT INTO referral_stat (refID, num_registration, query_date) VALUES ('".$userData['referral_id']."', '".$dataReferralStat->num_registration."', '".date("Y-m-d")."') ON DUPLICATE KEY UPDATE num_registration = '".$dataReferralStat->num_registration."'";
		                                $tableReferralStat->getDefaultAdapter()->query($sql);                                
		                                
		                                $tableReferralStatUsers = new Application_Model_DbTable_ReferralStatUsers();
		                                $sql = $tableReferralStatUsers->select()->where('refID = ?', $userData['referral_id'])->where('query_date = ?', date("Y-m-d"))->where('PubID = ?', $id);
		                                $dataReferralStatUsers = $tableReferralStatUsers->fetchRow($sql);                                           
		                                $dataReferralStatUsers->num_registration += 1;
		                                
		                                $sql = "INSERT INTO referral_stat_users (refID, num_registration, query_date, PubID) VALUES ('".$userData['referral_id']."', '".$dataReferralStatUsers->num_registration."', '".date("Y-m-d")."', '".$id."') ON DUPLICATE KEY UPDATE num_registration = '".$dataReferralStatUsers->num_registration."'";
		                                $tableReferralStatUsers->getDefaultAdapter()->query($sql);                                                                
		                            }
		                                     
		                        }                                                
                                
                                $this->_redirect('/administrator/sites/add-ajax/id/'.$id);	                        
                        }
                    }
                    $this->_redirect('/administrator/');
                }else{ 
                    $this->view->formErrors = $form->getMessages();
                    $this->view->formValues = $form->getValues();                                                        
                }
                
            }else{
                
                   $this->view->formValues = array('action'  => null,
                                                   'title'   => null,
                                                   'message' => null, 
                                                   'enable_wire_transfer' => $userData['enable_wire_transfer'],
                                                   'notification_control_admin' => $userData['notification_control_admin'],
                                                   'inviteURL' => $userData['inviteURL'],
                                                   'inviteAdx' => $userData['inviteAdx'],
                                                   'auto_min_cpm' => $userData['auto_min_cpm'],
                                                   'lock_am' => $userData['lock_am'],
                   								   'referral_system' => $userData['referral_system'],
					   								'desired_types'=>$userData['desired_types']);
            } 
            
        $this->view->co_approval_page = $co_approval_page;    
        }else{ $this->_redirect('/administrator/'); }

    }
    
    public function saveAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
            
    	$users = new Application_Model_DbTable_Users();
    	$pubID = $this->getRequest()->getPost('userID');
        $userData = $users->getUserAllInfoById($pubID);
        $referral_system = $this->getRequest()->getPost('referral_system');
        $referral_model = new Application_Model_DbTable_Referral();
        //If Referral Programm is ON and if was turned ON just now, then we create new Referral, else we delete tose referral
        if($referral_system AND !$userData['referral_system'])
			$referral_model->usersReferralProgramOn($userData);
        elseif(!$referral_system)
        	$referral_model->usersReferralProgramOff($userData);        
        $data_to_update = array(
        					 'notification_control_admin' => $this->getRequest()->getPost('notification_control_admin') == 1 ? NULL : 1,
                             'enable_wire_transfer' => $this->getRequest()->getPost('enable_wire_transfer') == 1 ? 1 : NULL,
        		     		 'reg_AdExchage' => $this->getRequest()->getPost('reg_AdExchage') == 1 ? 1 : NULL,
                             'inviteAdx' => $this->getRequest()->getPost('inviteAdx') == 1 ? NULL : 1,
                             'inviteRequest' => $this->getRequest()->getPost('inviteAdx') == 1 ? NULL : 4,
                             'inviteURL' => $this->getRequest()->getPost('inviteURL') ? $this->getRequest()->getPost('inviteURL') : NULL,
                             'date_invited'=> $this->getRequest('inviteURL') ? date("Y-m-d") : NULL,
                             'auto_min_cpm'=>  $this->getRequest()->getPost('auto_min_cpm')==1 ? 1 : NULL,
                             'referral_id'=>  $this->getRequest()->getPost('referralID')>0 ? $this->getRequest()->getPost('referralID') : NULL,
                             'account_manager_id'=>  $this->getRequest()->getPost('account_manager_id') ? $this->getRequest()->getPost('account_manager_id') : NULL,
                             'lock_am' => $this->getRequest()->getPost('lock_am') == 1 ? 1 : NULL,
        					 'referral_system' => $referral_system
        );
        
        $users->update($data_to_update, 'id = '.$pubID); 
           
      	if($this->getRequest()->getPost('revShare_date') || $this->getRequest()->getPost('revShare_price')){
    		$shareDates = $this->getRequest()->getPost('revShare_date');
    		$sharePrice = $this->getRequest()->getPost('revShare_price');

   			$users->deleteShare($pubID, $value);
   			
    		foreach ($shareDates as $key=>$value){
    			if(!empty($value) && !empty($sharePrice[$key])){
    				$users->saveShare($pubID, $value, str_replace(",", ".", $sharePrice[$key]));
    			}
    		}
    	}
        
        if($userData['account_manager_id']!=$this->getRequest()->getPost('account_manager_id') && $this->getRequest()->getPost('account_manager_notify')==1){
            $contactModel = new Application_Model_DbTable_ContactNotification();

            $sql = $contactModel->select()
                        ->from('contact_notification', array(
                            'name'=>'contact_notification.name',
                            'mail'=>'contact_notification.mail',
                            'new'=>'contact_notification.new'
                        ))
                        ->where("id='".$this->getRequest()->getPost('account_manager_id')."'");
            $dataManagerNew = $contactModel->fetchRow($sql);

            $sql = $contactModel->select()
                        ->from('contact_notification', array(
                            'name'=>'contact_notification.name',
                            'mail'=>'contact_notification.mail',
                            'new'=>'contact_notification.new'
                        ))
                        ->where("id='".$userData['account_manager_id']."'");
            $dataManagerOld = $contactModel->fetchRow($sql);
            
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";    
            $headers .= "From: ".$dataManagerNew->name." <".$dataManagerNew->mail.">\r\n";                                    
            $to = $userData['email'];
            $title = "New MadAds Media Account Manager (".$userData['company'].")";
            $message = "<p>Hello ".$userData['name'].",</p>";
            $message.="<p>I would like to introduce myself as your new account here at MadAds Media.  I will be taking over for ".$dataManagerOld->name.", going forward.</p>";
            $message.='<p>If at any point you have an issue with your account, or need a question answered, please don\'t hesitate to reach out!</p>';
            $message.='<p>I look forward to working with you.</p>';
            $message.='<p>Regards,<br />'.$dataManagerNew->name.'</p>';
            $message.= '<table width="450" cellspacing="3" cellpadding="0" border="0">
                          <tbody><tr>
                            <td width="225" valign="bottom"><font style="color:rgb(204, 0, 0); font-family:arial,helvetica,sans-serif; font-weight:bold;">'.$dataManagerNew->name.'</font></td>
                            <td align="right"><b><span style="font-size:8.0pt; font-family:Arial,sans-serif; color:#7f7f7f">tel: 856-874-8928</span></b></td>
                          </tr>
                          <tr>
                            <td valign="top"><font style="font-size:12px; font-family:arial,helvetica,sans-serif;">Publisher Relations Associate</font> </td>
                            <td align="right"><a style="color:rgb(204, 0, 0); font-size:11px; font-family:arial,helvetica,sans-serif;" target="_blank" href="mailto:'.$dataManagerNew->mail.'">'.$dataManagerNew->mail.'</a></td>
                          </tr>
                          <tr>
                            <td><img width="170" height="54" src="http://www.madadsmedia.com/images/signature/mam-signature.gif"></td>
                            <td valign="middle" align="right"><span style="font-size:8.0pt; font-family:arial,helvetica,sans-serif; color:#777777">717 Fellowship Rd. Suite D<br>
                                  <span style="font-size:8.0pt; font-family:arial,helvetica,sans-serif; color:#777777">Mount Laurel, NJ 08054</span> </span></td>
                          </tr>
                        </tbody></table>';                                
            if(APPLICATION_ENV!='development')
                mail($to, $title, $message, $headers);           
        }
        
    	 
    	$this->_redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function allAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'deny';  
    }
    
    public function ajaxDeniedAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('u.name', 'u.company', 'u.url', 'u.alexaRank', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'DATE_FORMAT(u.deny_date, "%Y-%m-%d")', 'u.denied_by', 'u.id', 'u.id');
         $Columns = array('name', 'company', 'url', 'alexaRank', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'DATE_FORMAT(u.deny_date, "%Y-%m-%d")', 'denied_by','id', 'id');
     $likeColumns = array(0 => 'u.name', 1 => 'u.company', 2 => 'u.email', 3 => 'DATE_FORMAT(u.created, "%Y-%m-%d")', 4 => 'DATE_FORMAT(u.deny_date, "%Y-%m-%d")');
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "u.id";
	
	/* DB table to use */
	$sTable = "users AS u";
	
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
	
        if($sWhere == ""){
            
            $sWhere = "WHERE u.reject = 1 ";
            
        } else {
            
            $sWhere .= " AND u.reject = 1 ";
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
		$sWhere
               	$sOrder
		$sLimit
		"; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	
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
                $sWhere 
                
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

    public function approvedAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'approve';   
        
        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;        
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;        
    }

    public function ajaxApprovedAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('u.reg_AdExchage', 'u.name', 'u.company', 'u.email', 'GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ":", s.SiteName ) AS char ) SEPARATOR "," )', 'u.revenue_today', 'u.revenue_1day_ago', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'u.enable_wire_transfer', 'u.reachout_mail', 'u.id', 'u.id');
         $Columns = array('reg_AdExchage', 'name', 'company', 'email', 'GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ":", s.SiteName ) AS char ) SEPARATOR "," )', 'revenue_today', 'revenue_1day_ago', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'enable_wire_transfer', 'reachout_mail', 'id', 'id');
     $likeColumns = array(0 => 'reg_AdExchage', 1 => 'name', 2 => 'u.company', 3 => 'u.email', 5 => 'DATE_FORMAT(u.created, "%Y-%m-%d")');  
       
        $sJoin = " LEFT JOIN sites AS s ON s.PubID = u.id ";    
     
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "u.id";
	
	/* DB table to use */
	$sTable = " users AS u ";
	                                                       
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
	
	$sGroup = "GROUP BY u.id ";
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
	
        if($sWhere == ""){ $sWhere = "WHERE u.active = 1 "; } 
                    else { $sWhere .= " AND u.active = 1 "; }
                    
        if(!empty($_GET['usersite'])){
            
            switch($_GET['usersite']){
                case 'have-site' :     $sWhere .= " AND s.SiteID IS NOT NULL "; break;
                case 'not-have-site' : $sWhere .= " AND s.SiteID IS NULL ";     break;
                default :              $sWhere .= " AND s.SiteID IS NOT NULL "; break;
            } 
            
        } else { $sWhere .= " AND s.SiteID IS NOT NULL "; }             
        
        $account = (int)$_GET['accounts'];        
        if($account>0){
            if($sWhere == "")
                $sWhere = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere.= " AND u.account_manager_id='".$account."' ";
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
    
    
    public function updateaprovdAction()
    {
    	$this->_helper->layout()->disableLayout(); // disable layout
    	$this->_helper->viewRenderer->setNoRender(); // disable view rendering
    	   
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		if($data['PubID']){
    			$userModel = new Application_Model_DbTable_Users();
    			
    			foreach ($data['PubID'] as $key=>$value){
    				if(isset($data['reg_AdExchage'][$key])){
    					//echo "Set 1 $key - $value <br>";
    					$userModel->checkAdx($key);
    				}else{
    					//echo "Set 0 $key - $value <br>";
    					$userModel->uncheckAdx($key);
    				}
    			}
    		}
    	}
    	if(empty($_POST['redirect'])){ $this->_redirect('/administrator/index/approved/'); }
                                 else{ $this->_redirect($this->_getParam('redirect')); }     	
    }
    
    public function authAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        
        if($this->_getParam('id')){
            $auth = Zend_Auth::getInstance()->getIdentity();
            
            $_SESSION['admin_id'] = $auth->id;
            $_SESSION['Zend_Auth']['storage']->id = $this->_getParam('id');
            
            $this->_redirect('/report');
        }else{
            $this->_redirect('/administrator/index/all/');
        }
    }
    
    public function backAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        
        if(isset($_SESSION['admin_id'])){
            $_SESSION['Zend_Auth']['storage']->id = $_SESSION['admin_id'];
            unset($_SESSION['admin_id']);
            $this->_redirect('/administrator/index/approved/');
        }else{
            $this->_redirect('/');
        }
    }    
    
    public function networkAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->nav = 'network';
    	       
    	$this->view->year = $this->_getParam('year');
    	$this->view->month = $this->_getParam('month');
        $this->view->rubicon_15 = $this->_getParam('rubicon_15');

    	$userModel = new Application_Model_DbTable_Users();

        $this->view->data = $userModel->getNetWorkStatNew($this->view->year, $this->view->month/*, $this->view->rubicon_15*/);

    }
    
    public function dailyAction()
    {
        $layout = Zend_Layout::getMvcInstance();
    	$layout->nav = 'daily';
        
        $year = $this->_getParam('year') ? $this->_getParam('year') : date('Y');
    	$month = $this->_getParam('month') ? $this->_getParam('month') : date('n');
        
        $tableSite = new Application_Model_DbTable_Sites();
        
        $this->view->data = $tableSite->getDailyStats($year, $month);        
        $this->view->live_site = $tableSite->getMaxSiteLive();
        $this->view->live_user = $tableSite->getMaxUserLive();
        
        $this->view->year = $year; 
        $this->view->month = $month;
    }
    
    public function generatenetworkAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	 
    	if($this->getRequest()->getPost('month') && $this->getRequest()->getPost('year')){
    		$userModel = new Application_Model_DbTable_Users();
    		$statsArray = $userModel->generateNetworkStats($this->getRequest()->getPost('year'), $this->getRequest()->getPost('month'));
    		$userModel->clearNetworkStats($this->getRequest()->getPost('year'), $this->getRequest()->getPost('month'));
    		if($statsArray){
    			foreach ($statsArray as $item){
    				$userModel->insertNetworkStats($item);		
    			}
    		}
    		$this->view->messages = 'COMPLETED!!!';
    	}
    	
    }
    public function blockedurlsAction()
    {
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $table = new Zend_Db_Table('madads_blocked');
        //$select = $table->select();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                    ->setIntegrityCheck(false);
        $select->joinLeft('sites', 'madads_blocked.SiteID=sites.SiteID', array(
            'sites.SiteName', 'madads_blocked.*'
        ));
//        $select->joinLeft('scrapee', 'category.scrapee_id=scrapee.id', array(
//            'category.*', 'parentName' => 'parent.name', 'parentName' => 'parent.name', 'parentID' => 'parent.id', 'scrapeeName' => 'scrapee.domain_name', 'scrapeeType' => 'scrapee.type'
//        ));
        if ($_SESSION["search"]!=""){
        
        $sSearch = $this->getRequest()->getParam("sSearch");
        $select->where("url LIKE '%".$_SESSION["search"]."%'");
        
        if (strlen($sSearch) >= 3):
            $select->where("sites.SiteName LIKE '%" . $sSearch . "%' OR url LIKE '%" . $sSearch . "%' OR url_full LIKE '%" . $sSearch . "%' OR src LIKE '%" . $sSearch . "%' OR num LIKE '%" . $sSearch . "%' OR updated LIKE '%" . $sSearch . "%' OR query_date LIKE '%" . $sSearch . "%'");
        endif;
        
        $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");

        $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");

        if (is_numeric($iSortCol_0)):
            switch ($iSortCol_0):
                case 0 :
                    $select->order("SiteName " . $sSortDir_0);
                    break;
                case 3 :
                    $select->order("url " . $sSortDir_0);
                    break;
                case 4 :
                    $select->order("url_full " . $sSortDir_0);
                    break;
                case 5 :
                    $select->order("src " . $sSortDir_0);
                    break;
                case 6 :
                    $select->order("num " . $sSortDir_0);
                    break;
                case 7 :
                    $select->order("updated " . $sSortDir_0);
                    break;
                case 8 :
                    $select->order("query_date " . $sSortDir_0);
                    break;
            endswitch;
        endif;
        
        $urls = $table->fetchAll($select);
        
        $sEcho = (int) $_GET['sEcho'];
        $output = array(
            "select" => $select->__toString(),
            "sEcho" => $sEcho++,
            "iTotalRecords" => count($urls),
            "iTotalDisplayRecords" => count($urls),
            "sColumns" => 2,
            "aaData" => array()
        );

        $aaData = array();
       
    
        if (count($urls) > 0):

            $select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
            $select->assemble();
            $urls = $table->fetchAll($select);

            foreach ($urls as $url):
                $urlData = array();
                $urlData[0] = $url->SiteName;
                $urlData["SiteName"] = $url->SiteName;
                $urlData[1] = $url->AdSize;
                $urlData["AdSize"] = $url->AdSize;
                $urlData[2] = $url->iframe;
                $urlData["iframe"] = $url->iframe;
                $urlData[3] = $url->url;
                $urlData["url"] = $url->url;
                $urlData[4] = $url->url_full;
                $urlData["url_full"] = $url->url_full;
                $urlData[5] = $url->src;
                $urlData["src"] = $url->src;
                $urlData[6] = $url->src_full;
                $urlData["src_full"] = $url->src_full;
                $urlData[7] = $url->num;
                $urlData["num"] = $url->num;
                $urlData[8] = $url->updated;
                $urlData["updated"] = $url->updated;
                $urlData[9] = $url->query_date;
                $urlData["query_date"] = $url->query_date;
                $urlData[10] = $url->SiteID;
                $urlData["SiteID"] = $url->SiteID;
                
                $aaData[] = $urlData;
            endforeach;

            $output['aaData'] = $aaData;

        endif;
        }
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    public function approvedservingurlsAction()
    {
        set_time_limit(0);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $table = new Zend_Db_Table('madads_url');
        //$select = $table->select();
        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                    ->setIntegrityCheck(false);
        $select->joinLeft('sites', 'madads_url.SiteID=sites.SiteID', array(
            'sites.SiteName', 'madads_url.*'
        ));
//        $select->joinLeft('scrapee', 'category.scrapee_id=scrapee.id', array(
//            'category.*', 'parentName' => 'parent.name', 'parentName' => 'parent.name', 'parentID' => 'parent.id', 'scrapeeName' => 'scrapee.domain_name', 'scrapeeType' => 'scrapee.type'
//        ));
        if ($_SESSION["search"]!=""){
        
        $sSearch = $this->getRequest()->getParam("sSearch");
        $select->where("url LIKE '%".$_SESSION["search"]."%'");
        
        if (strlen($sSearch) >= 3):
            $select->where("sites.SiteName LIKE '%" . $sSearch . "%' OR url LIKE '%" . $sSearch . "%' OR query_date LIKE '%" . $sSearch . "%' OR num LIKE '%" . $sSearch . "%'");
        endif;
        
        $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");

        $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");

        if (is_numeric($iSortCol_0)):
            switch ($iSortCol_0):
                case 0 :
                    $select->order("SiteName " . $sSortDir_0);
                    break;
                case 2 :
                    $select->order("query_date " . $sSortDir_0);
                    break;
                case 3 :
                    $select->order("url " . $sSortDir_0);
                    break;
                case 4 :
                    $select->order("num " . $sSortDir_0);
                    break;
            endswitch;
        endif;
        
        $urls = $table->fetchAll($select);
        
        $sEcho = (int) $_GET['sEcho'];
        $output = array(
            "select" => $select->__toString(),
            "sEcho" => $sEcho++,
            "iTotalRecords" => count($urls),
            "iTotalDisplayRecords" => count($urls),
            "sColumns" => 2,
            "aaData" => array()
        );

        $aaData = array();
       
    
        if (count($urls) > 0):

            $select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
            $select->assemble();
            $urls = $table->fetchAll($select);

            foreach ($urls as $url):
                $urlData = array();
                $urlData[0] = $url->SiteName;
                $urlData["SiteName"] = $url->SiteName;
                $urlData[1] = $url->AdSize;
                $urlData["AdSize"] = $url->AdSize;
                $urlData[2] = $url->query_date;
                $urlData["query_date"] = $url->query_date;
                $urlData[3] = $url->url;
                $urlData["url"] = $url->url;
                $urlData[4] = $url->num;
                $urlData["num"] = $url->num;
                $urlData[5] = $url->SiteID;
                $urlData["SiteID"] = $url->SiteID;
                
                $aaData[] = $urlData;
            endforeach;

            $output['aaData'] = $aaData;

        endif;
        }
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    public function approvediframeAction() {
        try{
        set_time_limit(0);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($_SESSION["fullsearch"] == "1") {
            $table = new Zend_Db_Table('madads_url_iframe');
            //$select = $table->select();
            $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                        ->setIntegrityCheck(false);
            $select->joinLeft('sites', 'madads_url_iframe.SiteID=sites.SiteID', array(
                'sites.SiteName', 'madads_url_iframe.*'
            ));
//        $select->joinLeft('scrapee', 'category.scrapee_id=scrapee.id', array(
//            'category.*', 'parentName' => 'parent.name', 'parentName' => 'parent.name', 'parentID' => 'parent.id', 'scrapeeName' => 'scrapee.domain_name', 'scrapeeType' => 'scrapee.type'
//        ));
            if ($_SESSION["search"] != "") {

                $sSearch = $this->getRequest()->getParam("sSearch");
                $select->where("url LIKE '%" . $_SESSION["search"] . "%'");

                if (strlen($sSearch) >= 3):
                    $select->where("sites.SiteName LIKE '%" . $sSearch . "%' OR url LIKE '%" . $sSearch . "%' OR num LIKE '%" . $sSearch . "%' OR src LIKE '%" . $sSearch . "%' OR url_full LIKE '%" . $sSearch . "%' OR query_date LIKE '%" . $sSearch . "%' OR num LIKE '%" . $sSearch . "%'");
                endif;

                $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");

                $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");

                if (is_numeric($iSortCol_0)):
                    switch ($iSortCol_0):
                        case 0 :
                            $select->order("sites.SiteName " . $sSortDir_0);
                            break;
                        case 2 :
                            $select->order("query_date " . $sSortDir_0);
                            break;
                        case 3 :
                            $select->order("url " . $sSortDir_0);
                            break;
                        case 4 :
                            $select->order("url_full " . $sSortDir_0);
                            break;
                        case 5 :
                            $select->order("src " . $sSortDir_0);
                            break;
                        case 6 :
                            $select->order("num " . $sSortDir_0);
                            break;
                    endswitch;
                endif;

                $urls = $table->fetchAll($select);
                //$urls=array();

                $sEcho = (int) $_GET['sEcho'];
                $output = array(
                    "select" => $select->__toString(),
                    "sEcho" => $sEcho++,
                    "iTotalRecords" => count($urls),
                    "iTotalDisplayRecords" => count($urls),
                    "sColumns" => 2,
                    "aaData" => array()
                );
                //$this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
                //return;
                $aaData = array();


                if (count($urls) > 0):

                    $select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
                    $select->assemble();
                    $urls = $table->fetchAll($select);
                    //$this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
                    //return;
                    foreach ($urls as $url):
                        $urlData = array();
                        $urlData[0] = $url->SiteName;
                        $urlData["SiteName"] = $url->SiteName;
                        $urlData[1] = $url->AdSize;
                        $urlData["AdSize"] = $url->AdSize;
                        $urlData[2] = $url->query_date;
                        $urlData["query_date"] = $url->query_date;
                        $urlData[3] = $url->url;
                        $urlData["url"] = $url->url;
                        $urlData[4] = $url->url_full;
                        $urlData["url_full"] = $url->url_full;
                        $urlData[5] = $url->src;
                        $urlData["src"] = $url->src;
                        $urlData[6] = $url->num;
                        $urlData["num"] = $url->num;
                        $urlData[7] = $url->SiteID;
                        $urlData["SiteID"] = $url->SiteID;

                        $aaData[] = $urlData;
                    endforeach;

                    $output['aaData'] = $aaData;

                endif;
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    } catch (Exception $e){
       $this->getResponse()->setBody(Zend_Json::encode($e->getMessage()))->setHeader('content-type', 'application/json', true);
    }
    }
    
    public function psaurlsAction()
    {
 
        $layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('admin');
        $layout->nav = 'denied'; 
        
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $sitesModel = new Application_Model_DbTable_Sites();
        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");
        
        if($auth->role!='admin' && $auth->role!='super') $layout->NoPsaUsers = true;       
        
        $this->view->dataAuth = $auth;
        $this->view->manager = $dataManager;
        $this->view->contactManager = $tableManager->getActiveContact();
      
    }
    
    public function ajaxStatusPsaurlAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $message = '';
        $existUrl = false;
        
        $siteID = $this->_getParam('SiteID');
        $status = $this->_getParam('status');        
        $regen = $this->_getParam('regen');
        $url = $this->_getParam('url');
        
        $clearUrl = strtolower($url);
        $clearUrl = str_replace(array("https://", "http://", "www."), "", $clearUrl);
        
        $tablePsaNum = new Application_Model_DbTable_Sites_PsaNum();
        $tableSite = new Application_Model_DbTable_Sites();        
        
        $result = $tablePsaNum->update(array('status' => $status), array('SiteID = ?' => $siteID, 'doname = ?' => $url));
        
        $dataSite = $tableSite->getDataByID($siteID);
        
        $arrURL = preg_split('/[\n]/', $dataSite['SiteURL']);

        foreach($arrURL as $iter){ if(strpos($iter, $clearUrl) !== false){ $existUrl = true; } }
        
        if($status == 1 && !$existUrl){ 
            
            $tableSite->update(array('SiteURL' => $dataSite['SiteURL']."\n"."http://".$clearUrl), 'SiteID = '.$siteID);
            
        } elseif($status != 1 && $existUrl) {            
                
            $dataSite['SiteURL'] = '';

            foreach($arrURL as $key => $iter){ 

                if(strpos($iter, $clearUrl) === false){ $dataSite['SiteURL'] .= (isset($arrURL[$key + 2]) || (isset($arrURL[$key + 1]) && strpos($arrURL[$key + 1], $clearUrl) === false)) ? $iter."\n" : $iter; }
                
            }   
            
            $tableSite->update(array('SiteURL' => $dataSite['SiteURL']), 'SiteID = '.$siteID);                                  
        }              
            
        if($regen == 1 && !$dataSite['lock_tags']){ 

            $siteTagsModel = new Application_Model_DbTable_SitesTags();
            $siteTagsModel->changeAction($siteID, 'gen', APPLICATION_ENV);
            
        } elseif($regen == 1 && $dataSite['lock_tags']) { $message = 'Tags for this site are locked'; }    
        
        $this->getResponse()->setBody(Zend_Json::encode(array('result' => $result, 'message' => $message)))->setHeader('content-type', 'application/json', true);
    }
    
    public function approvedwebsitesAction()
    {
        set_time_limit(0);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $table = new Zend_Db_Table('sites');
        $select = $table->select();
        //$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
        //            ->setIntegrityCheck(false);
        //$select->joinLeft('sites', 'madads_url_iframe.SiteID=sites.SiteID', array(
        //    'sites.SiteName', 'madads_url_iframe.*'
        //));
//        $select->joinLeft('scrapee', 'category.scrapee_id=scrapee.id', array(
//            'category.*', 'parentName' => 'parent.name', 'parentName' => 'parent.name', 'parentID' => 'parent.id', 'scrapeeName' => 'scrapee.domain_name', 'scrapeeType' => 'scrapee.type'
//        ));
        if ($_SESSION["search"]!=""){
        
        $sSearch = $this->getRequest()->getParam("sSearch");
        $select->where("SiteURL LIKE '%".$_SESSION["search"]."%'");
        
        if (strlen($sSearch) >= 3):
            $select->where("SiteID LIKE '%" . $sSearch . "%' OR SiteName LIKE '%" . $sSearch . "%' OR PubID LIKE '%" . $sSearch . "%'  OR tag_name LIKE '%" . $sSearch . "%' OR alexaRank LIKE '%" . $sSearch . "%'");
        endif;
        
        $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");

        $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");

        if (is_numeric($iSortCol_0)):
            switch ($iSortCol_0):
                case 0 :
                    $select->order("PubID " . $sSortDir_0);
                    break;
                case 1 :
                    $select->order("SiteID " . $sSortDir_0);
                    break;
                case 2 :
                    $select->order("SiteName " . $sSortDir_0);
                    break;
                case 3 :
                    $select->order("SiteURL " . $sSortDir_0);
                    break;
                case 4 :
                    $select->order("last_live " . $sSortDir_0);
                    break;
                    case 5 :
                        $select->order("tag_name " . $sSortDir_0);
                        break;
                    case 6 :
                        $select->order("alexaRank " . $sSortDir_0);
                        break;
                    case 7 :
                        $select->order("alexaRank_update " . $sSortDir_0);
                        break;
                    case 8 :
                        $select->order("impressions_1day_ago " . $sSortDir_0);
                        break;
                    case 9 :
                        $select->order("impressions_2day_ago " . $sSortDir_0);
                        break;
                endswitch;
            endif;

            $urls = $table->fetchAll($select);

            $sEcho = (int) $_GET['sEcho'];
            $output = array(
                "select" => $select->__toString(),
                "sEcho" => $sEcho++,
                "iTotalRecords" => count($urls),
                "iTotalDisplayRecords" => count($urls),
                "sColumns" => 2,
                "aaData" => array()
            );

            $aaData = array();


            if (count($urls) > 0):

                $select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
                $select->assemble();
                $urls = $table->fetchAll($select);

                foreach ($urls as $url):
                    $urlData = array();
                    $urlData[0] = $url->PubID;
                    $urlData["PubID"] = $url->PubID;
                    $urlData[1] = $url->SiteID;
                    $urlData["SiteID"] = $url->SiteID;
                    $urlData[2] = $url->SiteName;
                    $urlData["SiteName"] = $url->SiteName;
                    $urlData[3] = $url->SiteURL;
                    $urlData["SiteURL"] = $url->SiteURL;
                    $urlData[4] = $url->last_live;
                    $urlData["last_live"] = $url->last_live;
                    $urlData[5] = $url->tag_name;
                    $urlData["tag_name"] = $url->tag_name;
                    $urlData[6] = $url->alexaRank;
                    $urlData["alexaRank"] = $url->alexaRank;
                    $urlData[7] = $url->alexaRank_update;
                    $urlData["alexaRank_update"] = $url->alexaRank_update;
                    $urlData[8] = $url->impressions_1day_ago;
                    $urlData["impressions_1day_ago"] = $url->impressions_1day_ago;
                    $urlData[9] = $url->impressions_2day_ago;
                    $urlData["impressions_2day_ago"] = $url->impressions_2day_ago;

                    $aaData[] = $urlData;
                endforeach;

                $output['aaData'] = $aaData;

            endif;
        }
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }

    public function unnaprovedurlsAction() {
        set_time_limit(0);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($_SESSION["fullsearch"] == "1") {
            $table = new Zend_Db_Table('madads_psa');
            //$select = $table->select();
            $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                        ->setIntegrityCheck(false);
            $select->joinLeft('sites', 'madads_psa.SiteID=sites.SiteID', array(
                'sites.SiteName', 'madads_psa.*'
            ));
//        $select->joinLeft('scrapee', 'category.scrapee_id=scrapee.id', array(
//            'category.*', 'parentName' => 'parent.name', 'parentName' => 'parent.name', 'parentID' => 'parent.id', 'scrapeeName' => 'scrapee.domain_name', 'scrapeeType' => 'scrapee.type'
//        ));
            if ($_SESSION["search"] != "") {

                $sSearch = $this->getRequest()->getParam("sSearch");
                $select->where("url LIKE '%" . $_SESSION["search"] . "%'");

                if (strlen($sSearch) >= 3):
                    $select->where("sites.SiteName LIKE '%" . $sSearch . "%' OR query_date LIKE '%" . $sSearch . "%' OR url LIKE '%" . $sSearch . "%'  OR url_full LIKE '%" . $sSearch . "%' OR src LIKE '%" . $sSearch . "%' OR src_full LIKE '%" . $sSearch . "%' OR num LIKE '%" . $sSearch . "%'");
                endif;

                $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");

                $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");

                if (is_numeric($iSortCol_0)):
                    switch ($iSortCol_0):
                        case 0 :
                            $select->order("sites.SiteName " . $sSortDir_0);
                            break;
                        case 2 :
                            $select->order("query_date " . $sSortDir_0);
                            break;
                        case 4 :
                            $select->order("url " . $sSortDir_0);
                            break;
                        case 5 :
                            $select->order("url_full " . $sSortDir_0);
                            break;
                        case 6 :
                            $select->order("src " . $sSortDir_0);
                            break;
                        case 7 :
                            $select->order("src_full " . $sSortDir_0);
                            break;
                        case 8 :
                            $select->order("num " . $sSortDir_0);
                            break;
                    endswitch;
                endif;

                $urls = $table->fetchAll($select);

                $sEcho = (int) $_GET['sEcho'];
                $output = array(
                    "select" => $select->__toString(),
                    "sEcho" => $sEcho++,
                    "iTotalRecords" => count($urls),
                    "iTotalDisplayRecords" => count($urls),
                    "sColumns" => 2,
                    "aaData" => array()
                );

                $aaData = array();


                if (count($urls) > 0):

                    $select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
                    $select->assemble();
                    $urls = $table->fetchAll($select);

                    foreach ($urls as $url):
                        $urlData = array();
                        $urlData[0] = $url->SiteName;
                        $urlData["SiteName"] = $url->SiteName;
                        $urlData[1] = $url->AdSize;
                        $urlData["AdSize"] = $url->AdSize;
                        $urlData[2] = $url->query_date;
                        $urlData["query_date"] = $url->query_date;
                        $urlData[3] = $url->iframe;
                        $urlData["iframe"] = $url->iframe;
                        $urlData[4] = $url->url;
                        $urlData["url"] = $url->url;
                        $urlData[5] = $url->url_full;
                        $urlData["url_full"] = $url->url_full;
                        $urlData[6] = $url->src;
                        $urlData["src"] = $url->src;
                        $urlData[7] = $url->src_full;
                        $urlData["src_full"] = $url->src_full;
                        $urlData[8] = $url->num;
                        $urlData["num"] = $url->num;
                        $urlData[9] = $url->SiteID;
                        $urlData["SiteID"] = $url->SiteID;

                        $aaData[] = $urlData;
                    endforeach;

                    $output['aaData'] = $aaData;

                endif;
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    public function deniedAction() {
        set_time_limit(0);
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
//        $table = new Zend_Db_Table('madads_psa_report_daily');
//        $select = $table->select();
//        $select->order("impressions DESC");
//        $rows = $table->fetchAll($select);
//        $sEcho = (int) $_GET['sEcho'];
//        $output = array(
//            "select" => $select->__toString(),
//            "sEcho" => $sEcho++,
//            "iTotalRecords" => count($rows),
//            "iTotalDisplayRecords" => count($rows),
//            "sColumns" => 2,
//            "aaData" => array()
//        );
//
//        $aaData = array();
//        if (count($rows) > 0):
//            $select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
//            $select->assemble();
//            $rows = $table->fetchAll($select);
//            foreach ($rows as $row):
//                $rowData = array();
//                $rowData[0] = $row->site;
//                $rowData["site"] = $row->site;
//                $rowData[1] = $row->impressions;
//                $rowData["impressions"] = $row->impressions;
//                $rowData[2] = $row->ad_tag;
//                $rowData["ad_tag"] = $row->ad_tag;
//                $rowData[3] = $row->query_date;
//                $rowData["query_date"] = $row->query_date;
//                $aaData[] = $rowData;
//            endforeach;
//            $output['aaData'] = $aaData;
//        endif;
        
        
               /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
           
            $aColumns = array( 's.PubID', 'spn.SiteID', 'spn.doname', 'spn.num', 's.SiteName', 's.SiteName', 'spn.status', 'spn.status', 'spn.status', 'spn.updated', 'spn.SiteID', 's.SiteURL', 'spn.doname', 't.type' );
             $Columns = array( 'PubID', 'SiteID', 'doname', 'num', 'SiteName', 'SiteName', 'status', 'status', 'status', 'updated', 'SiteID', 'SiteURL', 'doname', 'type' );
         $likeColumns = array( 0 => 's.PubID', 1 => 'spn.SiteID', 2 => 'spn.doname', 3 => 'spn.num', 4 => 's.SiteName', 6 => 'spn.status', 9 => 'spn.updated' );  

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "spn.SiteID";

        /* DB table to use */
        $sTable = "sites_psa_num AS spn";
        $sJoin = " JOIN sites AS s ON s.SiteID = spn.SiteID
                   JOIN users AS u ON u.id = spn.SiteID
              LEFT JOIN tags AS t ON s.SiteID=t.site_id";
        $sGroup = " ";       
	
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
        
        if($sWhere == ""){ $sWhere = " WHERE s.status = 3 AND spn.doname != '' "; } 
                    else { $sWhere .= " AND s.status = 3 AND spn.doname != '' "; } 
                    
        if(!empty($_GET['PubID'])){ $sWhere .= " AND s.PubID = '".(int)$_GET['PubID']."' "; } 
        
        if(isset($_GET['filterStatus'])){
            
            switch($_GET['filterStatus']){
                
                case '0'   : $sWhere .= " AND spn.status = 0 "; break;
                case '1'   : $sWhere .= " AND spn.status = 1 "; break;
                case '2'   : $sWhere .= " AND spn.status = 2 "; break;
                default    : break;
            }
            
        }
        
           $account = (int)$_GET['filterAccounts'];
        if($account>0){
            
            if($sWhere == "") $sWhere = " WHERE u.account_manager_id = '".$account."' ";
                         else $sWhere.= " AND u.account_manager_id = '".$account."' ";
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
    
    public function psaurlsNewAction()
    {
        
    }
    
    public function getAjaxPsaurlsAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
                
        $aColumns = array(  'IF(psa.iframe, psa.src, psa.url)', 'SUM(psa.num)', 'psa.SiteID', 'MAX(psa.query_date)');
        $Columns = array(  'IF(psa.iframe, psa.src, psa.url)', 'SUM(psa.num)', 'SiteID', 'MAX(psa.query_date)');
        $likeColumns = array( 0 => 'IF(psa.iframe, psa.src, psa.url)', 1=>'psa.SiteID');  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "psa.SiteID";
	
	/* DB table to use */
	$sTable = "madads_psa AS psa";       
        //$sJoin = " JOIN sites AS s ON s.SiteID = psa.SiteID ";
        $sGroup = " GROUP BY IF(psa.iframe, psa.src, psa.url) ";
        
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
		$sWhere = "AND (";
		foreach ( $likeColumns as $key => $field )
		{
			$sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
        
	/* Individual column filtering */
	foreach( $likeColumns as $key => $field )
	{
		if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "AND ";
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
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		"; 
        //throw new Exception($sQuery);
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$tmp = $sQuery;
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
                $sWhere  
                $sGroup
		$sOrder
		$sLimit
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
    
    public function newAccountAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'new-account'; 
    }
           
    public function ajaxNewAccountUserAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('su.date', 'su.all', 'su.approve', 'su.percent', 'su.first_live', 'su.date');
         $Columns = array('date', 'all', 'approve', 'percent', 'first_live', 'date');
     $likeColumns = array(0 => 'su.date');         
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "su.id";
	
	/* DB table to use */
	$sTable = "stat_user AS su";
	$sGroup = " ";
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
	
        
        $currentDate = date('Y-m', strtotime($_GET['year'].'-'.$_GET['month'].'-01'));

        if($sWhere == ""){ $sWhere = "WHERE DATE_FORMAT(su.date, '%Y-%m') = '".$currentDate."' "; } 
                    else { $sWhere .= " AND DATE_FORMAT(su.date, '%Y-%m') = '".$currentDate."' "; }
        
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
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		"; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	
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
    
    public function ajaxNewAccountSiteAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('ss.date', 'ss.all', 'ss.approve', 'ss.percent', 'ss.lived', 'ss.date');
         $Columns = array('date', 'all', 'approve', 'percent', 'lived', 'date');
     $likeColumns = array(0 => 'ss.date');         
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "ss.id";
	
	/* DB table to use */
	$sTable = "stat_site AS ss";
	$sGroup = " ";
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
	
        
        $currentDate = date('Y-m', strtotime($_GET['year'].'-'.$_GET['month'].'-01'));
        
        if($sWhere == ""){ $sWhere = "WHERE DATE_FORMAT(ss.date, '%Y-%m') = '".$currentDate."' "; } 
                    else { $sWhere .= " AND DATE_FORMAT(ss.date, '%Y-%m') = '".$currentDate."' "; }
        
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
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		"; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	
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
    
    public function approvedDateUserAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'new-account'; 
        
        $this->view->cofirmDate = $this->_getParam('date-cofirm');
    }
    
    public function ajaxApprovedDateUserAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
//                                0               1             2           3                                                  4                                                         5                    6                           7                                8                      9            10      11           12
        $aColumns = array('u.reg_AdExchage', 'u.adx_name', 'u.company', 'u.email', 'GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ":", s.SiteName ) AS char ) SEPARATOR "," )', 'u.revenue_today', 'u.revenue_1day_ago', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'u.enable_wire_transfer', 'u.id', 'u.id', 'u.inviteRequest', 'u.url');
         $Columns = array('reg_AdExchage',    'adx_name',   'company',   'email',  'GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ":", s.SiteName ) AS char ) SEPARATOR "," )', 'revenue_today',    'revenue_1day_ago',  'DATE_FORMAT(u.created, "%Y-%m-%d")', 'enable_wire_transfer',    'id',   'id',  'inviteRequest', 'url');
     $likeColumns = array( 0 => 'u.id', 1 => 'u.adx_name', 2 => 'u.company', 3 => 'u.email', 5 => 'DATE_FORMAT(u.created, "%Y-%m-%d")');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "u.id";
	
	/* DB table to use */
	$sTable = " users AS u ";
        $sJoin = " LEFT JOIN sites AS s ON s.PubID = u.id ";
        $sGroup = " GROUP BY u.id "; 
	
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

        $confirmDate = $_GET['cofirmDate'];
          
        if(empty($_GET['filterDeniedUser'])){ $whereUser = " u.active = 1 AND DATE_FORMAT(u.date_confirm, '%Y-%m-%d') = '".$confirmDate."' "; }
                                        else{ $whereUser = " u.reject = 1 AND DATE_FORMAT(u.deny_date, '%Y-%m-%d') = '".$confirmDate."' "; }
        
        if($sWhere == ""){
            
            $sWhere = "WHERE ".$whereUser;
            
        } else {
            
            $sWhere .= " AND ".$whereUser;
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
    
    public function approveLiveAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'new-account'; 
        
        $this->view->dateLive = $this->_getParam('date-live');
    }
    
    public function ajaxApproveLiveAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
           
//                                 0           1            2           3                  4                        5                                                           6                                                                  7                          8                  9              10             11            12          13        14         15            16         17            18                 19                 20            21               
            $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.impressions_1day_ago', 's.impressions_2day_ago', 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 's.deni_impres_yesterday', 's.impressions_avg_7day', 's.revenue' , 's.alexaRank', 's.alexaRankUS', 's.live', 's.lived', 't.type', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 'u.inviteRequest', 's.PubID' );
             $Columns = array( 'PubID',    'SiteID',    'SiteName',  'email',   'impressions_1day_ago',   'impressions_2day_ago',   'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 'deni_impres_yesterday',   'impressions_avg_7day',   'revenue',    'alexaRank',    'alexaRankUS',  'live',    'lived',  'type',    'privacy',   'live_name',  'id',       'tag_id',   'inviteRequest',   'PubID' );
         $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.impressions_1day_ago' , 5 => 's.impressions_2day_ago', 8 => 'impressions_avg_7day', 9 => 's.revenue', 10 => 's.alexaRank', 14 => 't.type');

            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "s.SiteID";

            /* DB table to use */
            $sTable = "sites AS s";
            $sJoin = " JOIN users AS u ON u.id = s.PubID
            	       JOIN tags AS t ON t.site_id = s.SiteID";

            $sGroup = " ";

        
	
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


        $dataLive = $_GET['data-live'];
        $neverLive = isset($_GET['never-live']) ? $_GET['never-live'] : 0;           
        
        if($neverLive){ $whereLived = " "; /* show (sites live + sites never live) */ }
                  else{ $whereLived = " AND s.lived = 1 "; }
        
        if($sWhere == ""){  
            
            $sWhere = " WHERE s.status = 3 
                          AND u.active = 1
                          AND DATE_FORMAT(s.approved, '%Y-%m-%d') = '".$dataLive."'".$whereLived;  
            
        } else {              
            
            $sWhere .= " AND s.status = 3 
                         AND u.active = 1
                         AND s.lived = 1
                         AND DATE_FORMAT(s.approved, '%Y-%m-%d') = '".$dataLive."'".$whereLived;
            
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
            
		$sOrder
		$sLimit
		"; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	
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
    
    public function contactAction()
    {
        set_time_limit ( 0 );
        $this->_helper->layout ()->disableLayout ();
        $session = new Zend_Session_Namespace('Default');
        if($session->message)
        { 
            $this->view->message = $session->message;
            unset($session->message);
        }
        $PubID = ( int ) $this->_getParam ( 'PubID' );
        $tableUser = new Application_Model_DbTable_Users ();
        $tableStatic = new Application_Model_DbTable_StaticPage ();
        $tableMail = new Application_Model_DbTable_Mail ();
        $tableManager = new Application_Model_DbTable_ContactNotification ();
        $dataUser = $tableUser->getUserById ( $PubID );
        $tmpManager = $tableManager->getActiveContact ();
        $dataAuth = Zend_Auth::getInstance ()->getIdentity ();
        $dataManager = array ();
        foreach ( $tmpManager as $iter ) {
            $dataManager [$iter ['id']] = $iter;
        }
        if ($this->getRequest ()->isPost ()) {
            $menegerID = ( int ) $this->_getParam ( 'accountManager' );
            
            $status = (int) $this->_getParam('status');
            $staff_id = (int) $this->_getParam('staff');
            $client = (int) $this->_getParam('client');
            $bcc = (int) $this->_getParam('bcc');
            
            $to = trim(strip_tags($this->_getParam('to')));
            $subject = $this->_getParam ( 'subject' );
            $text = $this->_getParam ( 'text' );
            $dbAdapter = Zend_Db_Table::getDefaultAdapter ();
            $dataSignature = $tableStatic->getDataByName ( 'signature' );
            $dataSignature ['content'] = str_replace ( '{ADMIN_NAME_HERE}', $dataManager [$menegerID] ['name'], $dataSignature ['content'] );
            $dataSignature ['content'] = str_replace ( '{ADMIN_EMAIL_HERE}', $dataManager [$menegerID] ['mail'], $dataSignature ['content'] );
            
            
            $arrEmailvalid = array();
            $arrEmail = preg_split("[,]", $to);
            foreach($arrEmail as $iterEmail){
            
                $iterEmail = trim($iterEmail);
                if(filter_var($iterEmail, FILTER_VALIDATE_EMAIL)){ $arrEmailvalid[] = $iterEmail; }
            
            } $to = count($arrEmailvalid) ? implode(",", $arrEmailvalid) : "";
            
            $text = $text . $dataSignature ['content'];
            switch($status){
                case 1 :            
                    foreach($arrEmailvalid as $iterEmail){                                     
                        $classMail = new Zend_Mail ();
                        $classMail->setFrom ( $dataManager [$menegerID] ['mail'], $dataManager [$menegerID] ['name'] );
                        $classMail->addTo ( $iterEmail, $dataUser->name );
                        $classMail->setSubject ( $subject );
                        $classMail->setBodyHtml ( $text );
                        if($bcc) $classMail->addBcc($dataManager [$menegerID] ['mail']);                        
                        $classMail->send ();
                    }            
                    $session->message = 'Data has been save, and Email sent !';
                    break;
                case 2 :
                    $session->message = 'Data has been save, without Email !';
                    $subject =  $client ? 'Note (Client\'s Response)' : 'Note';
                    $text = $text ? $text : '';
                    break;
                default :
                    $session->message = 'Request return error, Please try again !';
                    $this->_redirect('/administrator/index/contact/PubID/'.$PubID);
                    break;
            }      
            
            $dataInsert = array (
                    'PubID' => $PubID,
                    'subject' => $subject,
                    'text' => $text,
                    'author' => $dataAuth->email,
                    'account_manager' => $dataManager [$menegerID] ['id'],
                    'created' => date ( 'Y-m-d H:i:s' )
            );
            $tableMail->insert ( $dataInsert );
            $this->_redirect ( '/administrator/index/contact/PubID/' . $PubID );
        }
        $this->view->dataUser = $dataUser;
        $this->view->contactManager = $dataManager;
        $this->view->data = $tableMail->getDataByUserWithManager ( $PubID );
    } 
    
    public function reachoutAction()
    {
        set_time_limit(0);        
        $this->_helper->layout()->disableLayout(); 
                
        $PubID = (int) $this->_getParam('PubID');
        
        $tableUser = new Application_Model_DbTable_Users();
        $tableStatic = new Application_Model_DbTable_StaticPage();
        $tableMail = new Application_Model_DbTable_Mail();
        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataUser = $tableUser->getUserById($PubID);
        $tmpManager = $tableManager->getActiveContact();
        $dataAuth = Zend_Auth::getInstance()->getIdentity();        
                
                $dataManager = array();
        foreach($tmpManager as $iter){ $dataManager[$iter['id']] = $iter; }

        if($this->getRequest()->isPost()){
        	
        	$mark_reach_out = (int) $this->_getParam('mark_reach_out');
            $menegerID = (int) $this->_getParam('accountManager');
            $subject = $this->_getParam('subject');
            $text = $this->_getParam('text');   
           
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $dataSignature = $tableStatic->getDataByName('signature'); 
            
            $dataSignature['content'] = str_replace('{ADMIN_NAME_HERE}', $dataManager[$menegerID]['name'], $dataSignature['content']);
    		$dataSignature['content'] = str_replace('{ADMIN_EMAIL_HERE}', $dataManager[$menegerID]['mail'], $dataSignature['content']);

            if($mark_reach_out == 0)
            {
            	$classMail = new Zend_Mail();
            	$classMail->setFrom($dataManager[$menegerID]['mail'], $dataManager[$menegerID]['name']);
            	$classMail->addTo($dataUser->email, $dataUser->name);
            	$classMail->setSubject($subject);
            	$classMail->setBodyHtml($text.$dataSignature['content']);
            	$classMail->send();
            	
            	$dataInsert = array('PubID'   => $PubID,
            			'subject' => $subject,
            			'text'    => $text.$dataSignature['content'],
            			'author'  => $dataAuth->email,
            			'account_manager' => $dataManager[$menegerID]['id'],
                                'type' => 1,
            			'created' => date('Y-m-d H:i:s'));
            }
            else 
            {
            	$dataInsert = array('PubID'   => $PubID,
            			'subject' => '',
            			'text'    => 'Marked as "reached out", no email sent.',
            			'author'  => $dataAuth->email,
            			'account_manager' => $dataManager[$menegerID]['id'],
                                'type' => 1,
            			'created' => date('Y-m-d H:i:s'));
            }
            
            $tableMail->insert($dataInsert);
            
            $whereUser = $dbAdapter->quoteInto('id = ?', $PubID);
            $tableUser->setTable();
            $tableUser->update(array('reachout_mail' => 1), $whereUser);
            
            $this->_redirect('/administrator/index/reachout/PubID/'.$PubID);
        }
       
        $this->view->dataUser = $dataUser;
        $this->view->contactManager = $dataManager;
        $this->view->data = $tableMail->getDataByUser($PubID);
    }           
    
    public function viewTagsCreateAction()
    {
        $date = date('Y-m-d', strtotime($this->_getParam('date')));
        
        $tableTag = new Application_Model_DbTable_Tags();
        
        $dataTag = $tableTag->getAllByCreated($date);
        
        $this->view->data = $dataTag;
    }
    
    public function viewPrevLiveSiteAction()
    {
        $date = date('Y-m-d', strtotime($this->_getParam('date')));
        
        $tableSite = new Application_Model_DbTable_Sites();
        
        $dataSite = $tableSite->getPrevLiveByData($date);
        
        $this->view->data = $dataSite;
    } 
    
    public function viewFirstLiveAction()
    {
        $date = date('Y-m-d', strtotime($this->_getParam('date')));
        
        $tableSite = new Application_Model_DbTable_Sites();
        
        $dataSite = $tableSite->getAllByFirstLive($date);
        
        $this->view->data = $dataSite;
    }    
    
    public function viewTotalLiveAction()
    {
        $date = date('Y-m-d', strtotime($this->_getParam('date')));
        
        $tableSite = new Application_Model_DbTable_Sites();
        
        $dataFirst = $tableSite->getAllByFirstLive($date);
        $dataPrev = $tableSite->getPrevLiveByData($date);
        
        $dataSite = array_merge($dataFirst, $dataPrev);
        
        $this->view->data = $dataSite;
    }    
    
    public function viewInactiveAction()
    {
        $date = date('Y-m-d', strtotime($this->_getParam('date')));
        
        $tableSite = new Application_Model_DbTable_Sites();
        
        $dataSite = $tableSite->getAllByInactive($date);
        
        $this->view->data = $dataSite;
    }

    public function ajaxRejectAction()
    {
        set_time_limit(0);
        $id = (int) $this->_getParam('id');

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $users = new Application_Model_DbTable_Users();
        $auth = Zend_Auth::getInstance()->getIdentity();

        $users->rejectUser($id, $auth->email);

        $data['status'] = 'ok';

        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);
    }

    public function disableAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();

        $PubID = $this->_getParam('PubID');

        $tableUser = new Application_Model_DbTable_Users();
        $tableMail = new Application_Model_DbTable_Mail();

        $sql = $tableUser->select()->setIntegrityCheck(false)
            ->from(array('u' => 'users'), array('*'))
            ->where('u.id = ?', $PubID);

        $dataUser = $tableUser->fetchRow($sql);
        $dataAuth = Zend_Auth::getInstance()->getIdentity();

        if($this->getRequest()->isPost()){

            $resultMail = false;

            if($this->_getParam('mail-status')){

                $mail = new Zend_Mail();

                $mail->setFrom('support@madadsmedia.com', 'MadAdsMedia Support');
                $mail->addTo($dataUser->email, $dataUser->name);
                $mail->setSubject($this->_getParam('subject'));
                $mail->setBodyHtml($this->_getParam('text'));

                $dataInsert = array('PubID'   => $PubID,
                    'subject' => $this->_getParam('subject'),
                    'text'    => $this->_getParam('text'),
                    'author'  => $dataAuth->email,
                    'account_manager' => NULL,
                    'type'    => 2,
                    'created' => date('Y-m-d H:i:s'));
                $tableMail->insert($dataInsert);


                $resultMail = $mail->send();
            }

            $tableUser->rejectUser($PubID, $dataAuth->email);

            if($resultMail) $this->view->message = 'User '.$dataUser->name.' is disabled, email send.';
            else $this->view->message = 'User '.$dataUser->name.' is disabled, email not send.';

        }
        $this->view->mail_data = $tableMail->getDataByUser($PubID);
        $this->view->data = $dataUser;
    }

    public function unDisableAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();

        $PubID = (int) $this->_getParam('PubID');

        $dataAuth = Zend_Auth::getInstance()->getIdentity();

        $tableUser = new Application_Model_DbTable_Users();
        $tableUser->update(array('reject' => 0,
            'active' => 1,
            'approved_by' => $dataAuth->email,
            'date_confirm' => date('Y-m-d H:i:s')), 'id = '.$PubID);

        $this->_redirect('/administrator/index/view/id/'.$PubID);

    }
}