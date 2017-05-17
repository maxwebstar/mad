<?php

class Administrator_AlertsController extends Zend_Controller_Action
{
    protected $_layout;
    
    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }

    public function indexAction()
    {
        $this->_layout->nav = 'alerts';
        
        $tableUser = new Application_Model_DbTable_Users();
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $this->view->dataAuth = $dataAuth;
        $this->view->admins = $tableUser->fetchAll("role='admin' OR role='super'", "name ASC");
    }
    
    public function addAction()
    {
        $form = new Application_Form_Alerts();
        $usersModel = new Application_Model_DbTable_Users();
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $this->view->admins = $usersModel->fetchAll("role='admin' OR role='super'", "name ASC");
        $this->view->auth = $auth;
        
        if($this->getRequest()->isPost()){        	        	            
            if($form->isValid($this->getRequest()->getPost())){ 
                $alertsModel = new Application_Model_DbTable_Alerts();
                                
                foreach ($form->getValue('adminID') as $id){
                    $dataAlerts = $alertsModel->createRow();
                        $dataAlerts->setFromArray(array(
                            'date_alert' => $form->getValue('date_alert'),
                            'message' => $form->getValue('message'),
                            'created' => date("Y-m-d"),
                            'status' => 1,
                            'adminID'=>$id,
                            'add_by'=>$auth->admin_id
                        ));                

                    $dataAlerts->save();
                }
                    
                $this->_redirect('/administrator/alerts/');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();                 
            }
        }        
    }
    
    public function editAction()
    {
        $id = (int) $this->_getParam('id');
        
        $form = new Application_Form_Alerts();
        
        $alertsModel = new Application_Model_DbTable_Alerts();
        $usersModel = new Application_Model_DbTable_Users();
        
        $sql = $alertsModel->select()->where('id = ?', $id);
        
        $dataAlert = $alertsModel->fetchRow($sql);
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $this->view->admins = $usersModel->fetchAll("role='admin' OR role='super'", "name ASC");
        $this->view->auth = $auth;
        
        if($this->getRequest()->isPost()){        	        	            
            if($form->isValid($this->getRequest()->getPost())){             
                    
                $dataAlert->setFromArray(array(
                    'adminID'    => $form->getValue('adminID'),
                    'date_alert' => $form->getValue('date_alert'),
                    'message'    => $form->getValue('message'),
                    'updated'    => date('Y-m-d'),
                    'updated_by' => $auth->admin_id
                ));                

                $dataAlert->save();                
                    
                $this->_redirect('/administrator/alerts/');
                
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();                 
            }
        } else {        
            
            $startValue = array('adminID'    => $dataAlert->adminID,
                                'date_alert' => $dataAlert->date_alert,
                                'message'    => $dataAlert->message);
            
            $this->view->formValues = $startValue;
        }
    }
    
    public function deleteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $id = (int) $this->_getParam('id');
        
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $tableAlert = new Application_Model_DbTable_Alerts();
        
        $whereAlert = $dbAdapter->quoteInto('id = ?', $id);

        $tableAlert->delete($whereAlert);
        
        $this->_redirect('/administrator/alerts/');
    }
    
    public function getAjaxAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
                
        $aColumns = array( 'a.id', 'a.date_alert', 'a.message', 'IFNULL(cn.name, u.name)', 'a.id', 'a.id', 'a.id');
         $Columns = array( 'id', 'date_alert', 'message', 'IFNULL(cn.name, u.name)', 'id', 'id', 'id');
     $likeColumns = array( 0 => 'a.id', 1 => 'a.message');  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "a.id";
	
	/* DB table to use */
	$sTable = "alerts AS a";
        $sJoin = "LEFT JOIN users AS u ON u.id = a.add_by 
                  LEFT JOIN contact_notification AS cn ON cn.mail = u.email ";
	
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
	
        
        if($sWhere == ""){            
            switch($_GET['status']){
                case '1':       $sWhere = " WHERE a.status = 1 AND a.date_alert<='".date("Y-m-d")."' ";  break;
                case '2':       $sWhere = " WHERE a.status = 1 AND a.date_alert>'".date("Y-m-d")."' ";  break;
                case '3':       $sWhere = " WHERE a.status = 2 ";  break;
                default:break;
            }         
        } else {            
            switch($_GET['status']){
                case '1':       $sWhere .= " AND a.status = 1 AND a.date_alert<='".date("Y-m-d")."' ";  break;
                case '2':       $sWhere .= " AND a.status = 1 AND a.date_alert>'".date("Y-m-d")."' ";  break;
                case '3':       $sWhere .= " AND a.status = 2 ";  break;
                default:break;
            }
        }

        if($sWhere == ""){            
            $sWhere = " WHERE a.adminID='".$auth->admin_id."' ";
        } else {            
            $sWhere .= " AND a.adminID='".$auth->admin_id."' ";
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
    
    public function getAjaxAssignAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
                
        //                   0            1            2           3            4            5                  6                7       8       9           10            11
        $aColumns = array( 'a.id', 'a.date_alert', 'a.status', 'a.closed', 'a.message', 'u2.email', 'IFNULL(cn.name, u.name)', 'a.id', 'a.id', 'a.id', 'a.date_alert', 'a.status' );
         $Columns = array(  'id',   'date_alert',   'status',   'closed',   'message',    'email',  'IFNULL(cn.name, u.name)',  'id',   'id',   'id',   'date_alert',   'status' );
     $likeColumns = array( 0 => 'a.id', 1 => 'a.message');  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "a.id";
	
	/* DB table to use */
	$sTable = "alerts AS a";
        $sJoin = "LEFT JOIN users AS u ON u.id = a.add_by
                  LEFT JOIN users AS u2 ON u2.id = a.adminID
                  LEFT JOIN contact_notification AS cn ON cn.mail = u.email ";
	
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
	
        $account = (int) $_GET['accounts'];        
        if($account > 0){
            if($sWhere == "") $sWhere  = " WHERE u.id = '".$account."' ";
                         else $sWhere .= " AND u.id = '".$account."' ";
        }
        
           $filterCompleted = (int) $_GET['filterCompleted'];        
        if($filterCompleted > 0){
            if($sWhere == "") $sWhere  = " WHERE a.status != 2 ";
                         else $sWhere .= " AND a.status != 2 ";
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
    
    public function closeAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $id = (int) $this->_getParam('id');
                
        if($id){
            
            $dataAuth = Zend_Auth::getInstance()->getIdentity();
            
            $alertsModel = new Application_Model_DbTable_Alerts();
            $alertsModel->update(array('status'     => 2,
                                       'closed'     => date('Y-m-d'),
                                       'closed_by'  => $dataAuth->admin_id), "id = '".$id."'");
        }
        
        $this->_redirect('/administrator/alerts/');        
    }
}