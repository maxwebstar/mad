<?php
class Administrator_BurstController extends Zend_Controller_Action {
		
	public function init() {
		
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('admin');
		$this->view->headTitle('Burst CPM & Revenue');
	}

	public function indexAction() 
	{
			
	}	
	
	
	public function getAjaxAction()
	{
		set_time_limit(0);
		$this->_helper->layout()->disableLayout(); // disable layout
		$this->_helper->viewRenderer->setNoRender(); // disable view rendering
		$burst_model = new Application_Model_DbTable_BurstMediaTags();
		$params = $this->_getAllParams();
    	$result = $burst_model->getBurst($params);
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true); 	     	
	}
	
	public function updateAjaxAction()
	{
		$this->_helper->layout()->disableLayout(); // disable layout
		$this->_helper->viewRenderer->setNoRender(); // disable view rendering
		$result = array('status' => 0, 'data' => null);
		$burst_model = new Application_Model_DbTable_BurstMediaTags();
		$params = $this->_getAllParams();
		$data = $burst_model->updateData($params);
		if($data)
		{
			$result['status'] = 1;
			$result['data'] = $params['value'];
		}
		$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
	}
	
	public function updateAjaxAllAction()
	{
		$this->_helper->layout()->disableLayout(); // disable layout
		$this->_helper->viewRenderer->setNoRender(); // disable view rendering
		$result = array('status' => 0);
		$burst_model = new Application_Model_DbTable_BurstMediaTags();
		$params = $this->_getAllParams();
		$burst_model->updateAllData($params);
		$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);		
	}
	
	
	
	public function getAjaxAction2()
	{
        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        /* MySQL connection */
		$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

		$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
		mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );

        $aColumns = array( 's.SiteName', 'ds.name', 'SUM(br.impressions)', 'br.cpm', 'ROUND(SUM(br.revenue),2)', 'br.SiteID');
         $Columns = array( 'SiteName', 'name', 'SUM(br.impressions)', 'cpm', 'ROUND(SUM(br.revenue),2)', 'SiteID');
     $likeColumns = array( 0 => 's.SiteName');


        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "br.SiteID";

        /* DB table to use */
        $sTable = "burst_media_tags AS br ";
        $sJoin = " LEFT JOIN sites AS s ON s.SiteID = br.SiteID 
        			LEFT JOIN display_size as ds ON ds.id=br.AdSize ";
		
		if($_GET['group']=='group')			
	        $sGroup = " GROUP BY br.SiteID ";
		else
			$sGroup = " GROUP BY br.SiteID, br.AdSize, br.query_date ";
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
		if($_GET['iSortCol_0'] == 13)
		{
			$sOrder = "ORDER BY o.Value ".$_GET['sSortDir_0']." ";
		}
		else
		{
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


        if(!empty($_GET['date'])){
            if($sWhere == "") $sWhere = " WHERE br.query_date = '".$_GET['date']."' ";
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
	
	public function setCpmAction()
	{
        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		if($data['siteID'] && $data['date'] && $data['cpm']){    			
    			$burstModel = new Application_Model_DbTable_Burst();  
    			$cpm = str_replace(",", ".", $data['cpm']); 
    			$date = $data['date']; 			
    			foreach ($data['siteID'] as $key=>$value){
    				$burstModel->update(array('cpm'=>$cpm, 'revenue'=>new Zend_Db_Expr('ROUND((`impressions`/1000)*'.$cpm.',2)')), "SiteID=$key AND query_date='$date'");
    			}
    		}
    	}
    	
    	$this->_redirect('/administrator/burst/');        
        		
	}
	
	public function setAction()
	{
        $this->_helper->layout()->disableLayout(); // disable layout

		$date = $this->getRequest()->getParam('date');
		
    	if (!empty($date) && strtotime($date)) {
    		$form = new Application_Form_SetBurst();
			$this->view->form = $form;
			if($this->getRequest()->isPost()){
				if($form->isValid($this->getRequest()->getPost())){
					echo $form->getValue('cpm');
					echo '<br>'.$form->getValue('paid');
				}
			}
    	}
				
	}
	
	public function burstRequestsAction()
	{
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'burts-requests';  
        
        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;        
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;                        		
	}
	
    public function ajaxBurstRequestsAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array( 's.PubID', 's.SiteName', 's.SiteID', 's.SiteID', 'sb.status', 'sb.created', 'sb.tags_live', 'sb.impressions', 's.alexaRank', 's.alexaRankUS', 's.alexa_country', 's.SiteID', 'pt.adTagId', 's.PubID' );
         $Columns = array( 'PubID', 'SiteName', 'SiteID', 'SiteID', 'status', 'created', 'tags_live', 'impressions', 'alexaRank', 'alexaRankUS', 'alexa_country', 'SiteID', 'adTagId', 'PubID');
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteName' );  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";
	
	/* DB table to use */
	$sTable = "sites_want_burst AS sb";
        $sJoin = " LEFT JOIN sites AS s ON s.SiteID = sb.SiteID
        			LEFT JOIN users AS u ON u.id = s.PubID
        			LEFT JOIN pubmatic_tags AS pt ON pt.SiteID = s.SiteID ";
       
    $sOrder = " ORDER BY sb.created DESC";
    $sGroup = " GROUP BY s.SiteID ";
  	
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
        
    $account = (int)$_GET['accounts'];        
    if($account>0){
        if($sWhere == "")
            $sWhere = " WHERE u.account_manager_id='".$account."' ";
        else
            $sWhere.= " AND u.account_manager_id='".$account."' ";
    }

    if($sWhere == ""){
        switch($_GET['live']){
            case 'live':       $sWhere = ' WHERE sb.tags_live = 1 ';                             break;
            case 'no_longer':  $sWhere = ' WHERE sb.tags_live IS NULL '; break;
            default:break;
        }
    }else{
        switch($_GET['live']){
            case 'live':       $sWhere .= ' AND sb.tags_live = 1 ';                             break;
            case 'no_longer':  $sWhere .= ' AND sb.tags_live IS NULL '; break;
            default:break;
        }
    }
        
    if($sWhere == "") $sWhere = " WHERE sb.status != 3 ";  
    else              $sWhere .= " AND sb.status != 3 "; 
      
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
    
    public function denyAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $siteID = (int) $this->_getParam('id');
        
        $tableSites = new Application_Model_DbTable_Sites();
        
        $sqlSite = $tableSites->select()->setIntegrityCheck(false)
                           ->from(array('s' => 'sites'), 
                                  array('s.SiteName', 's.SiteID', 's.PubID'))
                           ->where('s.SiteID = ?', $siteID)
                           ->join(array('u' => 'users'),('s.PubID = u.id'),
                                  array('u.email', 'u.name'));

        $dataSite = $tableSites->fetchRow($sqlSite);            
        
        if($this->getRequest()->isPost()){
            if($this->_getParam('subject') && $this->_getParam('reason')){
                                
                $text = $this->_getParam('reason');
                $subject = $this->_getParam('subject');                    
                                                
				$burst_model = new Application_Model_DbTable_WantBurst();
	            $burst_model->update(array('status' => 2), "SiteID=".$siteID);
	            	            
                $tableSites->update(array('status' => 2, 
                						'status_approved' => 1,
                						'reject_date' => date("Y-m-d H:i:s")),'SiteID = '.$siteID);
                $websiteLogsModel = new Application_Model_DbTable_WebsiteLogs();
                $websiteLogsModel->logEnabledDisabledChanges($dataSite->PubID,$siteID, 2);//Log that site is blocked

                $siteTagsModel = new Application_Model_DbTable_SitesTags();
                $siteTagsModel->changeAction($dataSite->SiteID, 'del', APPLICATION_ENV);

                $mail = new Zend_Mail();
                $mail->setFrom('support@madadsmedia.com', 'MadAdsMedia Support');
                $mail->addTo($dataSite->email, $dataSite->name);
                $mail->setSubject($subject);
                $mail->setBodyHtml($text);
                $mail->send();   
                $this->view->message = 'Message has been sent!';
            }
        }else{
	        $this->view->subject = 'Your Upgrade Status for '.$dataSite['SiteName'];
	        $this->view->reason = "<p>Hello,</p><p>After a re-review of your website, our approval team have deemed ".$dataSite['SiteName']." ineligible for displaying MadAdsMedia's ad tags.</p><p>As our approval team is always re-reviewing websites, we will reach out in the future if ".$dataSite['SiteName']." should be approved.</p><p>Regards,<br>MadAdsMedia Publisher Support Team</p>";
        }
        $this->view->data = $dataSite;        
    }
    
    public function updateStatusAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
	    
		$params = $this->_getAllParams();
		$id = (int)$params['id'];
		$burst_model = new Application_Model_DbTable_WantBurst();
		$burst_model->update(array('status' => 1), "SiteID=".$id);
    }
}
?>
