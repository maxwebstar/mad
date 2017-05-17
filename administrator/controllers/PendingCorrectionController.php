<?php
class Administrator_PendingCorrectionController extends Zend_Controller_Action
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $this->_layout->nav = 'pending-correct';
        
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
        
        $aColumns = array('s.content_checker', 's.SiteID', 's.SiteName', 'sm.page', 'sm.created', 'sm.created', 'sm.created', 's.SiteID', 'sm.fixed', 's.status', 's.PubID', 's.SiteURL', 's.SiteURL', 'sm.id');
         $Columns = array('content_checker', 'SiteID', 'SiteName', 'page', 'created', 'created', 'created', 'SiteID', 'fixed', 'status', 'PubID', 'SiteURL', 'SiteURL', 'id');
     $likeColumns = array(1 => 's.SiteID', 2 => 's.SiteName');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";
	
	/* DB table to use */
	$sTable = "sites AS s";
        $sJoin = " JOIN site_notifi_message AS sm ON sm.SiteID = s.SiteID ";
       	$sGroup = "";
	
        if($_GET['accounts'])
            $sJoin.= " LEFT JOIN users AS u ON u.id = s.PubID ";        
        
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
            
            switch($_GET['filter']){

                case 'all'      :  $sWhere = ' WHERE s.status != 1 '; break;
                case 'pending'  :  $sWhere = ' WHERE s.status = 3 AND sm.fixed = 0 ' ; break;
                case 'disabled' :  $sWhere = ' WHERE s.status = 2 AND sm.fixed = 0 ' ; break;
                case 'fixed'    :  $sWhere = ' WHERE sm.fixed = 1 '; break;
                default : break;

            }
          
        } else {
            
            switch($_GET['filter']){

                case 'all'      :  $sWhere .= ' AND s.status != 1 '; break;
                case 'pending'  :  $sWhere .= ' AND s.status = 3 AND sm.fixed = 0 ' ; break;
                case 'disabled' :  $sWhere .= ' AND s.status = 2 AND sm.fixed = 0 ' ; break;
                case 'fixed'    :  $sWhere .= ' AND sm.fixed = 1 '; break;
                default : break;
            }
        }             
        
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
    
    public function sendAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $siteID = (int) $this->_getParam('id');
        
        $tableSites = new Application_Model_DbTable_Sites();
        $tableNotifi = new Application_Model_DbTable_Sites_NotifiMessage();
        
        $sqlSite = $tableSites->select()->setIntegrityCheck(false)
                           ->from(array('s' => 'sites'), 
                                  array('s.SiteName', 's.SiteID', 's.PubID'))
                           ->where('s.SiteID = ?', $siteID)
                           ->join(array('u' => 'users'),('s.PubID = u.id'),
                                  array('u.email', 'u.name'));

        $dataSite = $tableSites->fetchRow($sqlSite); 
        $dataNotifi = $tableNotifi->getDataBySite($siteID);
        
        if($this->getRequest()->isPost()){
            if($this->_getParam('subject') && $this->_getParam('text')){
                
                    $mail = new Zend_Mail();
                    /*$tableNotifi = new Application_Model_DbTable_Sites_NotifiMessage();*/

                    $text = $this->_getParam('text');
                    $subject = $this->_getParam('subject');
                    
                    $mail->setFrom(Application_Model_DbTable_Registry_Setting::getByName('admin_email'), Application_Model_DbTable_Registry_Setting::getByName('admin_name'));
                    $mail->addTo($dataSite->email, $dataSite->name);
                    $mail->setSubject($subject);
                    $mail->setBodyHtml($text);

                    $mail->send();           

                    $this->view->message = 'Message has been sent!';
                }
            }        
        
        $this->view->data = $dataSite;  
        $this->view->notifi = $dataNotifi;
        
    }  

}