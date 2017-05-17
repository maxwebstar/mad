<?php
class Administrator_CheckerController extends Zend_Controller_Action
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $this->_layout->nav = 'checker';
        
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
        
        $aColumns = array('s.content_checker', 's.SiteID', 's.SiteName', 's.SiteURL', 's.SiteID', 's.live', 's.SiteURL', 's.PubID', 's.SiteURL');
         $Columns = array( 'content_checker',   'SiteID',   'SiteName',   'SiteURL',   'SiteID',   'live',   'SiteURL',   'PubID',   'SiteURL');
     $likeColumns = array(0 => 'SiteID', 1 => 'SiteName', 2 => 'SiteURL');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";
	
	/* DB table to use */
	$sTable = "sites AS s";
        $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID ";
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
	
        if($sWhere == ""){

            switch($_GET['filter']){

                case 'hide'  : $sWhere = ' WHERE s.content_checker IS NULL AND notify_checker IS NULL '; break;
                case 'hidden': $sWhere = ' WHERE s.content_checker = 1 AND notify_checker IS NULL ';     break;
                case 'pending': $sWhere = ' WHERE s.notify_checker = 1 ';     break;
                default      : break;
            }

        } else {

            switch($_GET['filter']){

                case 'hide'  : $sWhere .= ' AND s.content_checker IS NULL AND notify_checker IS NULL '; break;
                case 'hidden': $sWhere .= ' AND s.content_checker = 1 AND notify_checker IS NULL ';     break;
                case 'pending': $sWhere .= ' AND s.notify_checker = 1 ';     break;
                default      : break;
            }
        }
        
        
        if($sWhere == ""){ 
            
            $sWhere = " WHERE s.status = 3";  
            
        } else {              
            
            $sWhere .= " AND s.status = 3";
            
        } 
        
        $account = (int)$_GET['accounts'];        
        if($account>0){
            if($sWhere == "")
                $sWhere = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere.= " AND u.account_manager_id='".$account."' ";
        }
        
        $filterLive = ( int ) $_GET ['filterLive'];
        if ($filterLive > 0) {
            if ($sWhere == "")
                $sWhere = " WHERE s.live = 1 ";
            else
                $sWhere .= " AND s.live = 1 ";
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
    
    public function updatecheckerAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering 
        
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		if($data['siteID']){
    			$siteModel = new Application_Model_DbTable_Sites();
    			
    			foreach ($data['siteID'] as $key=>$value){
    				if(isset($data['content_checker'][$key])){
    					//echo "Set 1 $key - $value <br>";
    					$siteModel->update(array('content_checker'=>1, 'date_checker'=>date("Y-m-d")), "SiteID=$key");
    				}else{
    					//echo "Set 0 $key - $value <br>";
                                        $siteModel->update(array('content_checker'=>NULL, 'date_checker'=>NULL), "SiteID=$key");
    				}
    			}
    		}
    	}
    	
    	$this->_redirect('/administrator/checker/');        
    }
    
    public function sendAction()
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
            if($this->_getParam('subject') && $this->_getParam('text')){
                    $dbAdapter = Zend_Db_Table::getDefaultAdapter(); 
                    $sql = 'UPDATE sites SET notify_checker = 1 WHERE SiteID='.$siteID;     
                    $dbAdapter->query($sql);
                
                    $mail = new Zend_Mail();
                    $tableNotifi = new Application_Model_DbTable_Sites_NotifiMessage();

                    $type = $this->_getParam('type');
                    $text = $this->_getParam('text');
                    $subject = $this->_getParam('subject');                    
                    
                    if($type==1 || $type==2){
                        $mail->setFrom(Application_Model_DbTable_Registry_Setting::getByName('admin_email'), Application_Model_DbTable_Registry_Setting::getByName('admin_name'));
                        $mail->addTo($dataSite->email, $dataSite->name);
                        $mail->setSubject($subject);
                        $mail->setBodyHtml($text);

                        $mail->send();            	                    
                    }

						$tableNotifi->saveMessage(array('PubID' => $dataSite['PubID'], 'SiteID' => $dataSite['SiteID'], 'page' => 2, 'mail' => $dataSite['email'], 'name' => $dataSite['name'], 'subject' => $subject, 'text' => $text));	
                    
                    if($type==2 || $type==3){
						$tableSites->update(array('status'=>2), 'SiteID='.$dataSite['SiteID']);                    
                    }

                    if($type==1){
						$tableSites->update(array('status'=>3), 'SiteID='.$dataSite['SiteID']);                    
                    }
                                                                            
                    $this->view->message = 'Done';
                    
                   
                    
                }
            }
        
        
        $this->view->data = $dataSite;        
        $this->render('send');
    }    
}