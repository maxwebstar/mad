<?php
class Administrator_CpmController extends Zend_Controller_Action
{  
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
                
        $this->view->headTitle('Floor Tags');
    }
        
    public function indexAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'floor_tags';  
        
        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;        
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;                                
    }
    
    public function viewAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'floor_tags';  
        
        $cpmID = $this->_getParam('id');
        
        $tableFile = new Application_Model_DbTable_Cpm_File();
        $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
        $tableSite = new Application_Model_DbTable_Sites();

        $sql = $tableCpm->select()->where('id = ?', $cpmID);
        $dataCpm = $tableCpm->fetchRow($sql);
        
        $dataSite = $tableSite->getDataByID($dataCpm->SiteID);
        
        if($this->getRequest()->isPost()){ 
            
          switch($this->_getParam('choice')){
        
              case '3': 
                        if($dataSite['burst_tag']) $this->_redirect('/administrator/cpm/approve-burst/id/'.$dataCpm->id);  
                                              else $this->_redirect('/administrator/cpm/approve/id/'.$dataCpm->id);        
                        break;
              case '2':      
                        $dataCpm->status = 2;
                        $dataCpm->save();
                        break;
                    
              case 'delete': 
                        
                        $tableBurst = new Application_Model_DbTable_Cpm_Burst(); 
                        $tableMessage = new Application_Model_DbTable_Cpm_Message();
                  
                        $tableFile->delete('minimum_cpm_id = '. $dataCpm->id);
                        $tableBurst->delete('minimum_cpm_id = '. $dataCpm->id);
                        $tableMessage->delete('minimum_cpm_id = '. $dataCpm->id);
                        $dataCpm->delete(); 
                        break;
                    
              default:  break;
          }
          
          $this->_redirect('/administrator/cpm/index'); 
          
        }
        
        $this->view->data = $dataCpm;
        
    }
    
    public function approveAction()
    {            
            $this->_layout->setLayout('admin');

            //$classTag = new My_Tag('live');
            $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
            $tableSite = new Application_Model_DbTable_Sites();
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();

            $sql = $tableCpm->select()->where('id = ?', $this->_getParam('id'));
            $dataCpm = $tableCpm->fetchRow($sql);

            $dataSite = $tableSite->getSiteInfoByID($dataCpm->SiteID);

            if($dataCpm->cpm == 'Max Fill'){
                                                      
                if($dataCpm->checkPrevApprove()){

                    $sql = "DELETE FROM sites_floor_price WHERE SiteID = '".$dataSite['SiteID']."'";
                    $dbAdapter->query($sql);

                    $tableSite->update(array('floor_pricing' => NULL), 'SiteID = '.$dataSite['SiteID']);                    
                }   
                    $prevDynamic = $dataCpm->deleteData();
                    $dataCpm->status = 3;
                    $dataCpm->created = date('Y-m-d');
                    $dataCpm->save();

                    $siteTagsModel = new Application_Model_DbTable_SitesTags();
                    $siteTagsModel->changeAction($dataCpm->SiteID, 'gen', APPLICATION_ENV);

                    $logs_model = new Application_Model_DbTable_WebsiteLogs();
                    $params = array(
                    		'PubID' => $dataCpm->PubID,
                    		'SiteID' => $dataCpm->SiteID,
                    		'note_type' => 2,
                    		'note' => 'Floor tags was approved'
                    );
                    $logs_model->AddNote($params);
                    $this->_redirect('/administrator/cpm/index');                     
            }
            
            if($this->getRequest()->isPost()){ 
                if($dataCpm->status != 3){ 

                $tableSite->update(array('floor_pricing' => 1), 'SiteID ='.$dataCpm->SiteID);

                $sql = "INSERT INTO sites_floor_price (PubID, SiteID, date, price, percent) VALUES('".$dataCpm->PubID."', '".$dataCpm->SiteID."', '".$this->_getParam('floor_date')."', '".$this->_getParam('floor_price')."', '".$this->_getParam('floor_percent')."')";
                $dbAdapter->query($sql);

                $prevDynamic = $dataCpm->deleteData();
                $dataCpm->status = 3;
                $dataCpm->created = date('Y-m-d');
                $dataCpm->save();

                $siteTagsModel = new Application_Model_DbTable_SitesTags();
                $siteTagsModel->changeAction($dataCpm->SiteID, 'gen', APPLICATION_ENV);
    
                if($this->_getParam('notify_user') && $dataSite['notification_control_user'])
                    $this->sendEmail(array('email' => $dataSite['email'], 'subject' => $this->_getParam('subject'), 'message' => $this->_getParam('text')));

                }
                $logs_model = new Application_Model_DbTable_WebsiteLogs();
                $params = array(
                		'PubID' => $dataCpm->PubID,
                		'SiteID' => $dataCpm->SiteID,
                		'note_type' => 2,
                		'note' => 'Floor tags was approved'
                );
                $logs_model->AddNote($params);
                $this->_redirect('/administrator/cpm/index');                
             }

             $this->view->data = $dataCpm;
             $this->view->site = $dataSite;
  
    }
    
    public function approveBurstAction()
    {                
        $this->_layout->setLayout('admin');
        
        $cpmID = $this->_getParam('id');
        
        //$classTag = new My_Tag('live');
        $tableCpm = new Application_Model_DbTable_Cpm_Minimum();     
        $tableSite = new Application_Model_DbTable_Sites(); 
        $tableTag = new Application_Model_DbTable_Tags();
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();

        $sql = $tableCpm->select()->where('id = ?', $cpmID);
        $dataCpm = $tableCpm->fetchRow($sql);

        $dataSite = $tableSite->getSiteInfoByID($dataCpm->SiteID);  
        $dataTag = $tableTag->getTagBySiteID($dataCpm->SiteID);

        if($dataCpm->cpm == 'Max Fill'){          

             if($dataCpm->checkPrevApprove()){

                    $sql = "DELETE FROM sites_floor_price WHERE SiteID = '".$dataSite['SiteID']."'";
                    $dbAdapter->query($sql);

                    $tableSite->update(array('floor_pricing' => NULL), 'SiteID = '.$dataSite['SiteID']);                    
             }   
                    $prevDynamic = $dataCpm->deleteData();
                    $dataCpm->status = 3;
                    $dataCpm->created = date('Y-m-d');
                    $dataCpm->save();

                    $siteTagsModel = new Application_Model_DbTable_SitesTags();
                    $siteTagsModel->changeAction($dataSite['SiteID'], 'gen', APPLICATION_ENV);

                    if($dataTag['type']==4){
                        $tableCpmFile = new Application_Model_DbTable_Cpm_File();
                        $dataCpmFile = $tableCpmFile->fetchAll("minimum_cpm_id='".$dataCpm->id."'");
                        if($dataCpmFile){
                            $passbackArr = array();
                            foreach ($dataCpmFile as $item){
                                $passbackArr[$item->size]=$item->data;
                            }
                            /*
                            $tableSitesWantApi = new Application_Model_DbTable_Sites_WantApi();
                            $dataSitesWantApi = $tableSitesWantApi->createRow();
                            $dataSitesWantApi->SiteID = $dataCpm->SiteID;
                            $dataSitesWantApi->cpm = -1;
                            $dataSitesWantApi->created = date("Y-m-d H:i:s");
                            $dataSitesWantApi->creative_passback = serialize($passbackArr);
                            $dataSitesWantApi->save(); 
                            */                                           
                        }
                    }else{
	                    	/*
                            $tableSitesWantApi = new Application_Model_DbTable_Sites_WantApi();
                            $dataSitesWantApi = $tableSitesWantApi->createRow();
                            $dataSitesWantApi->SiteID = $dataCpm->SiteID;
                            $dataSitesWantApi->created = date("Y-m-d H:i:s");
                            $dataSitesWantApi->adExchange=1;
                            $dataSitesWantApi->passbacks=1;
                            $dataSitesWantApi->save(); 
                            */                                                                   
                    }
                    $logs_model = new Application_Model_DbTable_WebsiteLogs();
                    $params = array(
                    		'PubID' => $dataCpm->PubID,
                    		'SiteID' => $dataCpm->SiteID,
                    		'note_type' => 2,
                    		'note' => 'Floor tags was approved'
                    );
                    $logs_model->AddNote($params);

           $this->_redirect('/administrator/cpm/index');                     
        }

        if($this->getRequest()->isPost()){ 
            if($dataCpm->status != 3){                 

                $dataAuth = Zend_Auth::getInstance()->getIdentity();

                $dataCpm->status = 4;   
                $dataCpm->created = date('Y-m-d');
                $dataCpm->save();

                $tableBurst = new Application_Model_DbTable_Cpm_Burst();
                $tableBurst->insert(array('minimum_cpm_id' => $dataCpm->id,
                                          'admin_id' => $dataAuth->admin_id,
                                          'floor_price' => $this->_getParam('floor_price'),
                                          'floor_percent' => $this->_getParam('floor_percent'),
                                          'floor_date' => $this->_getParam('floor_date'),
                                          'status' => 4,
                                          'created' => date("Y-m-d H:i:s")));

                if($this->_getParam('notify_user') && $dataSite['notification_control_user']){ 

                    $tableMessage = new Application_Model_DbTable_Cpm_Message();
                    $tableMessage->insert(array('minimum_cpm_id' => $dataCpm->id,
                                                'subject' => $this->_getParam('subject'),
                                                'message' => $this->_getParam('text'),
                                                'status' => 4,
                                                'created' => date("Y-m-d H:i:s")));
                }             
            }

            $this->_redirect('/administrator/cpm/index');                
         }

         $this->view->data = $dataCpm;
         $this->view->site = $dataSite;

         $this->render('approve');
    }
    
    public function testAction()
    {
        $this->_layout->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $tableSite = new Application_Model_DbTable_Sites();
        $tableFile = new Application_Model_DbTable_Cpm_File();
        $tableMinimum = new Application_Model_DbTable_Cpm_Minimum();
        
        $sql = $tableFile->select()
                         ->where('file IS NOT NULL')
                         ->where('minimum_cpm_id = ?', $this->_getParam('id'));
        
        $dataFile = $tableFile->fetchAll($sql);
        $dataSite = $tableSite->getSiteInfoByID($dataFile[0]->SiteID);
  
        if($this->getRequest()->isPost()){
            
            $minimum_cpm = $tableMinimum->fetchRow("id='".$this->_getParam('id')."'");
            $dataPrev = $minimum_cpm;
            $minimum_cpm->_postDelete();
            $minimum_cpm->createDynamic();
            $minimum_cpm->created = date("Y-m-d");
            $minimum_cpm->checkPath();
            $minimum_cpm->checkPathIframe();
            $minimum_cpm->save();
                        
            foreach($dataFile as $iter){
                
                $iter->dynamic=$minimum_cpm->dynamic;
                $iter->checkPathIframe();
                
                /*Application_Plugin_PassbackTag::second($iter, $iter->getPath(), $this->_getParam('iframe_'.$iter->id), $dataSite['tag_type'], $dataSite['store_tag_url']);*/ 
                
                $iter->save();                
            }
            
            if($this->_getParam('approved')==1){
                $siteTagsModel = new Application_Model_DbTable_SitesTags();
                $siteTagsModel->changeAction($minimum_cpm->SiteID, 'gen', APPLICATION_ENV);
            }
            
            $this->view->message = 'Passback tags re-generated';            
        }
        $this->_redirect('http://scripts.madadsmedia.com/cpm-info.php?id='.$this->_getParam('id').'&approved='.$this->_getParam('approved').'&message='.$this->view->message);
        //$this->view->data = $dataFile;

    }
    
    public function rejectAction()
    {                                   
        $this->_layout->disableLayout();
        
        $cpmID = $this->_getParam('id');
        
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $tableSite = new Application_Model_DbTable_Sites();
        $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
        
        $sql = $tableCpm->select()->setIntegrityCheck(false)
                        ->from(array('mc' => 'minimum_cpm'),array('*'))
                        ->where('mc.id = ?', $cpmID)
                        ->join(array('s' => 'sites'),('s.SiteID = mc.SiteID'),
                               array('s.SiteName As SiteName',
                                     's.iframe_tags AS iframe_tags'))
                        ->join(array('u' => 'users'),('u.id = mc.PubID'),
                               array('u.email AS email',
                                     'u.name AS UserName'));
        
        $dataCpm = $tableCpm->fetchRow($sql);

            if($this->getRequest()->isPost()){
                if($this->_getParam('subject') && $this->_getParam('text')){

                    $prevDynamic = $dataCpm->deleteAllData();

                    $sql = "DELETE FROM sites_floor_price WHERE SiteID = '".$dataCpm['SiteID']."'";
                    $dbAdapter->query($sql);

                    $tableSite->update(array('floor_pricing' => NULL), 'SiteID = '.$dataCpm['SiteID']);

                    $siteTagsModel = new Application_Model_DbTable_SitesTags();
                    $siteTagsModel->changeAction($dataCpm['SiteID'], 'gen', APPLICATION_ENV);

                    $mail = new Zend_Mail();

                    $mail->setFrom(Application_Model_DbTable_Registry_Setting::getByName('admin_email'), Application_Model_DbTable_Registry_Setting::getByName('admin_name'));
                    $mail->addTo($dataCpm->email, $dataCpm->UserName);
                    $mail->setSubject($this->_getParam('subject'));
                    $mail->setBodyHtml($this->_getParam('text'));

                    $mail->send();

                    $this->view->message = 'Message has been sent!';
                }
                    $logs_model = new Application_Model_DbTable_WebsiteLogs();
                    $params = array(
                    		'PubID' => $dataCpm->PubID,
                    		'SiteID' => $dataCpm->SiteID,
                    		'note_type' => 2,
                    		'note' => 'Floor tags was rejected'
                    );
                    $logs_model->AddNote($params);
                }
                
         $this->view->data = $dataCpm;
         if($this->_getParam('new')==1)
             $this->render('reject-new');
         else
             $this->render ('reject');
    }
    
    public function sendEmail($data)
    {
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";    
        $headers .= "From: Publisher Support <adtags@madadsmedia.com>\r\n";                                    
        $to = $data['email'];
        $title = $data['subject'];
        $message = $data['message'];

        mail($to, $title, $message, $headers);   
    }
    
    public function ajaxGetAction()
    {       
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array( 's.SiteName', 'u.email', 'm.prev_cpm', 'm.cpm', 'GROUP_CONCAT( DISTINCT  cast(concat( f.id, ":", f.file ) AS char ) SEPARATOR "," )', 'DATE_FORMAT(m.created, "%Y-%m-%d")', 't.network_id', 'm.SiteID', 'm.PubID', 'm.id', 'm.PubID', 'm.id', 'm.status', 's.burst_tag', 't.network_id', 't.id as tag_id' );
         $Columns = array( 'SiteName', 'email', 'prev_cpm', 'cpm', 'GROUP_CONCAT( DISTINCT  cast(concat( f.id, ":", f.file ) AS char ) SEPARATOR "," )', 'DATE_FORMAT(m.created, "%Y-%m-%d")', 'network_id', 'SiteID', 'PubID', 'id', 'PubID', 'id', 'status', 'burst_tag', 'network_id', 'tag_id' );
     $likeColumns = array( 0 => 's.SiteName', 1 => 'u.email', 2 => 'm.prev_cpm', 3 => 'm.cpm', 5=>'DATE_FORMAT(m.created, "%Y-%m-%d")', 6 => 's.burst_tag' );  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "m.id";
	
	/* DB table to use */
	$sTable = "minimum_cpm AS m";
        $sJoin = " JOIN sites AS s ON s.SiteID = m.SiteID 
                   JOIN sites_tags AS t ON s.SiteID = t.site_id
                   JOIN users AS u ON u.id = m.PubID 
              LEFT JOIN minimum_cpm_file AS f ON f.minimum_cpm_id = m.id AND f.passback = 0 ";
                
        
        $sGroup = "GROUP BY m.id ";
	
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
            
            $sWhere = " WHERE s.status = 3 AND u.reject = 0 ";  
            
        } else {              
            
            $sWhere .= " AND s.status = 3 AND u.reject = 0 ";
            
        } 
        
        if($sWhere == ""){
            
            switch($_GET['filter']){

                case 'new':      $sWhere = ' WHERE m.status = 1 '; break;
                case 'reject':   $sWhere = ' WHERE m.status = 2 '; break;
                case 'approved': $sWhere = ' WHERE m.status = 3 '; break;
                case 'waitcron': $sWhere = ' WHERE m.status = 4 '; break;
                default:break;

            }
          
        } else {
            
            switch($_GET['filter']){

                case 'new':      $sWhere .= ' AND m.status = 1 '; break;
                case 'reject':   $sWhere .= ' AND m.status = 2 '; break;
                case 'approved': $sWhere .= ' AND m.status = 3 '; break;
                case 'waitcron': $sWhere .= ' AND m.status = 4 '; break;
                default:break;

            }
        }
        
        if($sWhere == ""){
            
            switch($_GET['filterType']){

                case '4':   $sWhere = ' WHERE t.network_id = 4 '; break;
                case '5':   $sWhere = ' WHERE t.network_id = 5 '; break;
                case '6':   $sWhere = ' WHERE t.network_id = 6 '; break;
                case '8':   $sWhere = ' WHERE t.network_id = 8 '; break;
                case '9':   $sWhere = ' WHERE t.network_id = 9 '; break;
                case '10':   $sWhere = ' WHERE t.network_id = 10 '; break;
                default:break;

            }
          
        } else {
            
            switch($_GET['filterType']){

                case '4':   $sWhere .= ' AND t.network_id = 4 '; break;
                case '5':   $sWhere .= ' AND t.network_id = 5 '; break;
                case '6':   $sWhere .= ' AND t.network_id = 6 '; break;
                case '8':   $sWhere .= ' AND t.network_id = 8 '; break;
                case '9':   $sWhere = ' WHERE t.network_id = 9 '; break;
                case '10':   $sWhere = ' WHERE t.network_id = 10 '; break;
                default:break;

            }
        }
        
        $account = (int)$_GET['accounts'];        
        if($account>0){
            if($sWhere == "")
                $sWhere = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere.= " AND u.account_manager_id='".$account."' ";
        }
        
           $onlyBurst = (int) $_GET['only_burst'];
        if($onlyBurst > 0){
            if($sWhere == "") $sWhere = " WHERE s.burst_tag = '".$onlyBurst."' ";
                         else $sWhere.= " AND s.burst_tag = '".$onlyBurst."' ";
        }

        if($sWhere == "") $sWhere = " WHERE t.primary = 1 ";
        else $sWhere.= " AND t.primary = 1 ";

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
        
        if($output['iTotalRecords'] == 0){
            
            $output['iTotalDisplayRecords'] = 0;
            $output['aaData'] = array();
            
        }
             
       
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    
    public function valueAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'floor_tags'; 
        
        $tableValue = new Application_Model_DbTable_Cpm_Value();
        
        $sql = $tableValue->select()->order('position ASC');
        
        $this->view->data = $tableValue->fetchAll($sql);
    }
    
    public function addValueAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'floor_tags'; 
        
        $tableValue = new Application_Model_DbTable_Cpm_Value();
        
        $dataValue = $tableValue->createRow();
        
        $form = new Application_Form_Cpm_Value($dataValue);
        
        if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->_getAllParams())) {
                    
                    $form->appendData();
                    
                    $dataValue->save();
                    
                    $this->_redirect('/administrator/cpm/value');
                    
                }
        }
        
        $this->view->form = $form;
    }
    
    public function editValueAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'floor_tags'; 
        
        $tableValue = new Application_Model_DbTable_Cpm_Value();
        
        $sql = $tableValue->select()->where('id = ?', $this->_getParam('id'));
        
        $dataValue = $tableValue->fetchRow($sql);
        
        $form = new Application_Form_Cpm_Value($dataValue);
        $form->showOldData();
        
        if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->_getAllParams())) {
                    
                    $form->appendData();
                    
                    $dataValue->save();
                    
                    $this->_redirect('/administrator/cpm/value');
                    
                }
        }
        
        $this->view->form = $form;
        
    }
    
    public function deleteValueAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'floor_tags'; 
        
        $tableValue = new Application_Model_DbTable_Cpm_Value();
        
        $sql = $tableValue->select()->where('id = ?', $this->_getParam('id'));
        
        $dataValue = $tableValue->fetchRow($sql);
        $dataValue->delete();
        
        $this->_redirect('/administrator/cpm/value');        
    }
    
}
?>
