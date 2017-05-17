<?php
/**
 * Description of SizesController
 *
 * @author nik
 */
class Administrator_DashboardController extends Zend_Controller_Action 
{

    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'dashboard';  
        
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $tableSites = new Application_Model_DbTable_Sites();
        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;        
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;
        
        /*
         * Yesterday's Impressions
         */
        $sql=$tableSites->select()->setIntegrityCheck(false)
                ->from('sites AS s', array(
                    'impress'=>'SUM(s.impressions_1day_ago)'
                ))
                ->joinLeft('users AS u', 'u.id=s.PubID')
                ->where("u.account_manager_id='".$dataManager['id']."'");
        $this->view->yesterday = $tableSites->fetchRow($sql);
        
        /*
         * 2 Days Ago Impressions
         */
        $sql=$tableSites->select()->setIntegrityCheck(false)
                ->from('sites AS s', array(
                    'impress'=>'SUM(s.impressions_2day_ago)'
                ))
                ->joinLeft('users AS u', 'u.id=s.PubID')
                ->where("u.account_manager_id='".$dataManager['id']."'");
        $this->view->daysTwoAgo = $tableSites->fetchRow($sql);
        
        /*
         * Yesterday's last week
         */
        $sql=$tableSites->select()->setIntegrityCheck(false)
                ->from('sites AS s', array(
                    'impress'=>'SUM(s.impressions_last_week)'
                ))
                ->joinLeft('users AS u', 'u.id=s.PubID')
                ->where("u.account_manager_id='".$dataManager['id']."'");
        $this->view->last_week = $tableSites->fetchRow($sql); 
        
        /*
         * Yesterday's last month
         */
        $sql=$tableSites->select()->setIntegrityCheck(false)
                ->from('sites AS s', array(
                    'impress'=>'SUM(s.impressions_last_month)'
                ))
                ->joinLeft('users AS u', 'u.id=s.PubID')
                ->where("u.account_manager_id='".$dataManager['id']."'");
        $this->view->last_month = $tableSites->fetchRow($sql);                        
    }    
    
    public function ajaxLiveAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
                
        $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.impressions_no_paid_1day_ago', 's.impressions_no_paid_2day_ago', 'IFNULL(ROUND((s.impressions_no_paid_1day_ago - s.impressions_no_paid_2day_ago)/s.impressions_no_paid_2day_ago * 100), 0)', 's.deni_impres_yesterday', 's.alexaRank', 's.alexaRankUS', 'sl.date', 's.live', 's.lived', 't.network_id', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 'COUNT(sl.SiteID) as counts' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions_no_paid_1day_ago', 'impressions_no_paid_2day_ago', 'IFNULL(ROUND((s.impressions_no_paid_1day_ago - s.impressions_no_paid_2day_ago)/s.impressions_no_paid_2day_ago * 100), 0)', 'deni_impres_yesterday', 'alexaRank', 'alexaRankUS', 'date', 'live', 'lived', 'network_id', 'privacy', 'live_name', 'id', 'tag_id', 'counts' );
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.impressions_no_paid_1day_ago' , 5 => 's.impressions_no_paid_2day_ago', 7 => 's.alexaRank', 10 => 't.network_id');
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "sl.SiteID";
	
	/* DB table to use */
	$sTable = "sites_live AS sl";
        $sJoin = " JOIN sites AS s ON s.SiteID = sl.SiteID
                   JOIN users AS u ON u.id = s.PubID
                   JOIN sites_tags AS t ON t.site_id = s.SiteID
                   LEFT JOIN sites_live AS sl2 ON sl2.SiteID = sl.SiteID AND sl2.date < '".date('Y-m-d', strtotime('-5 day'))."' ";

        $sGroup = " GROUP BY sl.SiteID ";
        $sHaving = "";
        //$sOrder = " ORDER BY s.live DESC ";
	
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
	//$sOrder = " ORDER BY sl.date DESC ";
        

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
	                                       
        if(isset($_GET['manager']) && $_GET['manager'] != -1){
            //$sLimit = " LIMIT 0,10 ";
           
            if($sWhere=="") $sWhere = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
                       else $sWhere .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";    
            
        }   
        
           $filterAdxStatus = (int) (isset($_GET['filterAdxStatus']) ? $_GET['filterAdxStatus'] : 0);
        if($filterAdxStatus > 0){
            
            if($sWhere == ""){ $sWhere = " WHERE u.inviteRequest = '".$filterAdxStatus."' "; }
                         else{ $sWhere.= " AND u.inviteRequest = '".$filterAdxStatus."' "; }             
        }
        
        if($sWhere=="") $sWhere = " WHERE s.live=1
                                    AND t.primary = 1
                                    AND sl.live = 1
                                    AND sl.date >= '".date('Y-m-d', strtotime('-4 day'))."' 
                                    AND sl2.live IS NULL ";
        
                  else $sWhere .= " AND s.live=1
                                    AND t.primary = 1
                                    AND sl.live = 1
                                    AND sl.date >= '".date('Y-m-d', strtotime('-4 day'))."' 
                                    AND sl2.live IS NULL ";  
        
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
                $sHaving
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
                $sHaving
        
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
    
    
    public function ajaxNeverLiveAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $str = preg_split('/[-]/', date("Y-m-d"));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, $str[1], $str[2] - 1 /* day */, $str[0]));
        $day2_ago = date('Y-m-d', strtotime('-2 day'));
        
        if(isset($_GET['filterSize']) && $_GET['filterSize'] != 'all'){
            
            $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 'sis.impressions_1day', 'sis.impressions_2day', 'IFNULL(ROUND((sis.impressions_1day - sis.impressions_2day)/sis.impressions_2day * 100), 0)', 'sis.impressions_1day_denied', 's.alexaRank', 's.alexaRankUS', 't.updated_at', 's.live', 's.lived', 't.network_id', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id' );
             $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions_1day', 'impressions_2day', 'IFNULL(ROUND((sis.impressions_1day - sis.impressions_2day)/sis.impressions_2day * 100), 0)', 'impressions_1day_denied', 'alexaRank', 'alexaRankUS', 'live', 'lived', 'network_id', 'updated_at', 'privacy', 'live_name', 'id', 'tag_id' );
         $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 'sis.impressions_1day' , 5 => 'sis.impressions_2day', 7 => 's.alexaRank', 10 => 't.network_id');

            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "s.SiteID";

            /* DB table to use */
            $sTable = "sites AS s";
            $sJoin = " JOIN users AS u ON u.id = s.PubID
                       LEFT JOIN sites_tags AS t ON t.site_id = s.SiteID
                            JOIN stats_impressions_size AS sis ON sis.SiteID = s.SiteID AND sis.AdSize = '".(int)$_GET['filterSize']."' ";

            $sGroup = " GROUP BY s.SiteID ";
            
        } else {
        
            $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.impressions_1day_ago', 's.impressions_2day_ago', 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 's.deni_impres_yesterday', 's.alexaRank', 's.alexaRankUS', 't.updated_at', 's.live', 's.lived', 't.network_id', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id' );
             $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions_1day_ago', 'impressions_2day_ago', 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 'deni_impres_yesterday', 'alexaRank', 'alexaRankUS', 'updated_at', 'live', 'lived', 'network_id', 'privacy', 'live_name', 'id', 'tag_id' );
         $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.impressions_1day_ago' , 5 => 's.impressions_2day_ago', 7 => 's.alexaRank', 10 => 't.network_id');

            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "s.SiteID";

            /* DB table to use */
            $sTable = "sites AS s";
            $sJoin = " JOIN users AS u ON u.id = s.PubID
                       LEFT JOIN sites_tags AS t ON t.site_id = s.SiteID ";

            $sGroup = " GROUP BY s.SiteID ";
            
        }
	
        if($_GET['accounts']=='my')
            $sJoin.=" LEFT JOIN contact_notification AS cn ON cn.id=u.account_manager_id ";
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
	               
        
        if(!empty($_GET['PubID'])){
        
            if($sWhere == "") $sWhere = " WHERE s.PubID = '".$_GET['PubID']."' ";  
            else              $sWhere .= " AND s.PubID = '".$_GET['PubID']."' "; 
        
        }
        
        if($sWhere == ""){
            
            switch($_GET['filter']){

                case 'live':       $sWhere = ' WHERE s.live = 1 ';                             break;
                case 'no_longer':  $sWhere = ' WHERE s.lived IS NOT NULL AND s.live IS NULL '; break;
                case 'never_live'   : $sWhere = ' WHERE s.lived IS NULL ';                     break;
                case 'store_tag_url': $sWhere = ' WHERE s.store_tag_url IS NOT NULL ';         break;
                default:break;

            }
          
        } else {
            
            switch($_GET['filter']){

                case 'live':       $sWhere .= ' AND s.live = 1 ';                             break;
                case 'no_longer':  $sWhere .= ' AND s.lived IS NOT NULL AND s.live IS NULL '; break;
                case 'never_live'   : $sWhere .= ' AND s.lived IS NULL ';                     break;
                case 'store_tag_url': $sWhere .= ' AND s.store_tag_url IS NOT NULL ';         break;
                default:break;

            }
        }

        if($sWhere == ""){            
            switch($_GET['accounts']){
                case 'my':       $sWhere = " WHERE cn.mail = '".$auth->email."' AND cn.status=1 ";                             break;
                default:break;
            }          
        } else {            
            switch($_GET['accounts']){
                case 'my':       $sWhere .= " AND cn.mail = '".$auth->email."' AND cn.status=1 ";                             break;
                default:break;
            }
        }

        if($sWhere == "") $sWhere = " WHERE t.primary = 1 ";
        else $sWhere.= " AND t.primary = 1 ";

        if((int)$_GET['filterType']){
            if($sWhere == "")
                $sWhere = ' WHERE t.network_id = '.(int)$_GET['filterType'].' ';
            else
                $sWhere .= ' AND t.network_id = '.(int)$_GET['filterType'].' ';
        }

        if($sWhere == ""){            
            if($_GET['filterCategory'] != 'all'){ $sWhere = " WHERE s.category = '".$_GET['filterCategory']."' "; }          
        } else {            
            if($_GET['filterCategory'] != 'all'){ $sWhere .= " AND s.category = '".$_GET['filterCategory']."' "; }
        }   

        if($sWhere == ""){ 
            
            $sWhere = " WHERE s.status = 3 AND u.reject = 0 AND t.updated_at < '".$day2_ago."' ";
            
        } else {              
            
            $sWhere .= " AND s.status = 3 AND u.reject = 0 AND t.updated_at < '".$day2_ago."' ";
        } 
        
        if(isset($_GET['manager'])){
            //$sLimit = " LIMIT 0,10 ";
                if($_GET['filter']=='live') $sOrder = " ORDER BY s.created DESC, s.last_live DESC";
            elseif($_GET['filter']=='never_live') $sOrder = " ORDER BY s.created DESC";
            
            if($_GET['manager'] != -1){
            
                if($sWhere=="") $sWhere = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
                           else $sWhere .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";         
                       
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
    
    public function getImpressStatAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        $data = array();
        
        $id = (int)$this->_getParam('id');
                
        if($id==-1){
            $where = "u.account_manager_id IS NOT NULL";
            $data['status'] = 'ok';
        }elseif($id>0){
            $where = "u.account_manager_id='$id'";
            $data['status'] = 'ok';            
        }else{
            $where = false;
        }
        
        if($where){
            $tableSites = new Application_Model_DbTable_Sites();
            /*
             * Yesterday's Impressions
             */
            $sql=$tableSites->select()->setIntegrityCheck(false)
                    ->from('sites AS s', array(
                        'impress'=>'SUM(s.impressions_1day_ago)'
                    ))
                    ->joinLeft('users AS u', 'u.id=s.PubID')
                    ->where($where);
            $dataImpress = $tableSites->fetchRow($sql);
            $data['yesterday'] = number_format($dataImpress->impress, 0, '.', ',');

            /*
             * 2 Days Ago Impressions
             */
            $sql=$tableSites->select()->setIntegrityCheck(false)
                    ->from('sites AS s', array(
                        'impress'=>'SUM(s.impressions_2day_ago)'
                    ))
                    ->joinLeft('users AS u', 'u.id=s.PubID')
                    ->where($where);
            $dataImpress = $tableSites->fetchRow($sql);
            $data['daysTwoAgo'] = number_format($dataImpress->impress, 0, '.', ',');

            /*
             * Yesterday's last week
             */
            $sql=$tableSites->select()->setIntegrityCheck(false)
                    ->from('sites AS s', array(
                        'impress'=>'SUM(s.impressions_last_week)'
                    ))
                    ->joinLeft('users AS u', 'u.id=s.PubID')
                    ->where($where);
            $dataImpress = $tableSites->fetchRow($sql);
            $data['last_week'] = number_format($dataImpress->impress, 0, '.', ',');

            /*
             * Yesterday's last month
             */
            $sql=$tableSites->select()->setIntegrityCheck(false)
                    ->from('sites AS s', array(
                        'impress'=>'SUM(s.impressions_last_month)'
                    ))
                    ->joinLeft('users AS u', 'u.id=s.PubID')
                    ->where($where);
            $dataImpress = $tableSites->fetchRow($sql);
            $data['last_month'] = number_format($dataImpress->impress, 0, '.', ',');               
        }
        
        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);
    }
    
    public function ajaxInactiveAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $str = preg_split('/[-]/', date("Y-m-d"));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, $str[1], $str[2] - 1 /* day */, $str[0]));
        
        $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.impressions_no_paid_1day_ago', 's.impressions_no_paid_2day_ago', 'IFNULL(ROUND((s.impressions_no_paid_1day_ago - s.impressions_no_paid_2day_ago)/s.impressions_no_paid_2day_ago * 100), 0)', 's.highest_impres', 's.alexaRank', 's.inactive_date', 's.email_notlive_3day', 's.manual_followup', 't.network_id', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 's.date_highest_impres' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions_no_paid_1day_ago', 'impressions_no_paid_2day_ago', 'IFNULL(ROUND((s.impressions_no_paid_1day_ago - s.impressions_no_paid_2day_ago)/s.impressions_no_paid_2day_ago * 100), 0)', 'highest_impres', 'alexaRank', 'inactive_date', 'email_notlive_3day', 'manual_followup', 'network_id', 'privacy', 'live_name', 'id', 'tag_id', 'date_highest_impres' );
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.impressions_no_paid_1day_ago' , 5 => 's.impressions_no_paid_2day_ago', 7 => 's.alexaRank', 10 => 't.network_id');
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";
	
	/* DB table to use */
	$sTable = "sites AS s";
        $sJoin = " JOIN users AS u ON u.id = s.PubID
                   JOIN sites_tags AS t ON t.site_id = s.SiteID ";
               
	$sOrder = " ORDER BY s.inactive_date DESC ";
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
            
            $sWhere = " WHERE s.status = 3
                        AND t.primary = 1
                        AND u.reject = 0  
                        AND s.lived IS NOT NULL 
                        AND s.live IS NULL 
                        AND s.period_more_100_impression = 0
                        AND s.once_more_1000_impression = 1
                        AND s.inactive_date >= '".date('Y-m-d', strtotime('-4 day'))."' 
                        AND s.impressions_no_paid_1day_ago>0 ";  
            
        } else {              
            
            $sWhere .= " AND s.status = 3
                         AND t.primary = 1
                         AND u.reject = 0 OR  
                         AND s.lived IS NOT NULL 
                         AND s.live IS NULL
                         AND s.period_more_100_impression = 0
                         AND s.once_more_1000_impression = 1
                         AND s.inactive_date >= '".date('Y-m-d', strtotime('-4 day'))."' 
                         AND s.impressions_no_paid_1day_ago>0 ";
            
        } 

        if((int)$_GET['filterType']){
            if($sWhere == "")
                $sWhere = ' WHERE t.network_id = '.(int)$_GET['filterType'].' ';
            else
                $sWhere .= ' AND t.network_id = '.(int)$_GET['filterType'].' ';
        }


        if(isset($_GET['manager']) && $_GET['manager'] != -1){
            //$sLimit = " LIMIT 0,10 ";
                        
            if($sWhere=="")
                $sWhere = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
            else
                $sWhere .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";            
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
    
    public function ajaxWeekStatisticAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array( 'f.query_date', 'SUM(f.impressions)', 'SUM(f.paid_impressions)','SUM(f.revenue)', 'f.query_date','(SUM(f.revenue)/SUM(f.impressions))*1000' );
         $Columns = array( 'query_date', 'SUM(f.impressions)','SUM(f.paid_impressions)', 'SUM(f.revenue)', 'query_date','(SUM(f.revenue)/SUM(f.impressions))*1000' );
     $likeColumns = array( );  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "f.PubID";
	
	/* DB table to use */
	$sTable = "users_reports_final AS f";
        $sJoin = " JOIN users AS u ON u.id = f.PubID ";
        $sGroup = " GROUP BY f.query_date ";       
	$sOrder = " ORDER BY f.query_date DESC ";
        
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
        
        if($sWhere == "") $sWhere = " WHERE f.query_date >= '".date('Y-m-d', strtotime('-6 day'))."' ";  
                     else $sWhere .= " AND f.query_date >= '".date('Y-m-d', strtotime('-6 day'))."' ";
                 

        if(isset($_GET['manager']) && $_GET['manager'] != -1){
                        
            if($sWhere == "") $sWhere = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
                         else $sWhere .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";            
        }        
        
           $filterAdxStatus = (int) (isset($_GET['filterAdxStatus']) ? $_GET['filterAdxStatus'] : 0);
        if($filterAdxStatus > 0){
            
            if($sWhere == ""){ $sWhere = " WHERE u.inviteRequest = '".$filterAdxStatus."' "; }
                         else{ $sWhere.= " AND u.inviteRequest = '".$filterAdxStatus."' "; }             
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
		"sEcho" => isset($_GET['sEcho']) ? intval($_GET['sEcho']) : 0,
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
    
    public function siteDayStatisticAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'dashboard';  
        
        $auth = Zend_Auth::getInstance()->getIdentity();        
        
        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;        
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;        
        $this->view->currentDate = $this->_getParam('date'); 
    }

    public function ajaxSiteDayStatisticAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array( 'f.PubID', 'f.SiteID', 's.SiteName', 'u.email', 'SUM(f.impressions)','SUM(f.paid_impressions)', 'SUM(f.revenue)', 's.alexaRank', 'f.query_date', '(SUM(f.revenue)/SUM(f.impressions))*1000' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'SUM(f.impressions)','SUM(f.paid_impressions)', 'SUM(f.revenue)', 'alexaRank', 'query_date','(SUM(f.revenue)/SUM(f.impressions))*1000' );
     $likeColumns = array( 2 => 's.SiteName', 3 => 'u.email', );  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "f.SiteID";
	
	/* DB table to use */
	$sTable = "users_reports_final AS f";
        $sJoin = " JOIN users AS u ON u.id = f.PubID 
                   JOIN sites AS s ON s.SiteID = f.SiteID ";
        $sGroup = " GROUP BY f.SiteID ";       
	        
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
        
        if($sWhere == "") $sWhere  = " WHERE f.query_date = '".$_GET['filterDate']."' ";  
                     else $sWhere .= " AND f.query_date = '".$_GET['filterDate']."' ";
                 

        if(isset($_GET['manager']) && $_GET['manager'] != -1){
                        
            if($sWhere == "") $sWhere = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
                         else $sWhere .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";            
        }  
        
           $filterAdxStatus = (int) (isset($_GET['filterAdxStatus']) ? $_GET['filterAdxStatus'] : 0);
        if($filterAdxStatus > 0){
            
            if($sWhere == ""){ $sWhere = " WHERE u.inviteRequest = '".$filterAdxStatus."' "; }
                         else{ $sWhere.= " AND u.inviteRequest = '".$filterAdxStatus."' "; }             
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
		"sEcho" => isset($_GET['sEcho']) ? intval($_GET['sEcho']) : 0,
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
    
    public function ajaxTopEarnersAction()
    {
    	set_time_limit(0);
    
    	$this->_helper->layout()->disableLayout(); // disable layout
    	$this->_helper->viewRenderer->setNoRender(); // disable view rendering
    	$auth = Zend_Auth::getInstance()->getIdentity();
    
    	/* MySQL connection */
    	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
    	 
    	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
    
    	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
    
    	$aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.impressions_no_paid_1day_ago', 's.impressions_no_paid_2day_ago', 'IFNULL(ROUND((s.impressions_no_paid_1day_ago - s.impressions_no_paid_2day_ago)/s.impressions_no_paid_2day_ago * 100), 0)', 's.deni_impres_yesterday', 's.alexaRank', 's.alexaRankUS', 's.revenue', 's.live', 's.lived', 't.network_id', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 'COUNT(sl.SiteID) as counts' );
    	$Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions_no_paid_1day_ago', 'impressions_no_paid_2day_ago', 'IFNULL(ROUND((s.impressions_no_paid_1day_ago - s.impressions_no_paid_2day_ago)/s.impressions_no_paid_2day_ago * 100), 0)', 'deni_impres_yesterday', 'alexaRank', 'alexaRankUS', 'revenue', 'live', 'lived', 'network_id', 'privacy', 'live_name', 'id', 'tag_id', 'counts' );
    	$likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.impressions_no_paid_1day_ago' , 5 => 's.impressions_no_paid_2day_ago', 7 => 's.alexaRank', 10 => 't.type');
    
    	/* Indexed column (used for fast and accurate table cardinality) */
    	$sIndexColumn = "sl.SiteID";
    
    	/* DB table to use */
    	$sTable = "sites_live AS sl";
    	$sJoin = " JOIN sites AS s ON s.SiteID = sl.SiteID
                   JOIN users AS u ON u.id = s.PubID
                   LEFT JOIN sites_tags AS t ON t.site_id = s.SiteID
                   LEFT JOIN sites_live AS sl2 ON sl2.SiteID = sl.SiteID AND sl2.date < '".date('Y-m-d', strtotime('-5 day'))."' ";
    
    	$sGroup = " GROUP BY sl.SiteID ";
    	$sHaving = "";
    	//$sOrder = " ORDER BY s.live DESC ";
    
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
    	//$sOrder = " ORDER BY sl.date DESC ";
    
    
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
    
    	if(isset($_GET['manager']) && $_GET['manager'] != -1){
    		//$sLimit = " LIMIT 0,10 ";
    		 
    		if($sWhere=="") $sWhere = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
    		else $sWhere .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";
    
    	}
    
    	$filterAdxStatus = (int) (isset($_GET['filterAdxStatus']) ? $_GET['filterAdxStatus'] : 0);
    	if($filterAdxStatus > 0){
    
    		if($sWhere == ""){ $sWhere = " WHERE u.inviteRequest = '".$filterAdxStatus."' "; }
    		else{ $sWhere.= " AND u.inviteRequest = '".$filterAdxStatus."' "; }
    	}
    
    	if($sWhere=="") $sWhere = " WHERE s.live=1
    	                            AND t.primary = 1
                                    AND sl.live = 1
                                    AND sl.date >= '".date('Y-m-d', strtotime('-4 day'))."'
                                    AND sl2.live IS NULL ";
    
    	else $sWhere .= " AND s.live=1
                                    AND sl.live = 1
                                    AND t.primary = 1
                                    AND sl.date >= '".date('Y-m-d', strtotime('-4 day'))."'
                                    AND sl2.live IS NULL ";
    
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
    		$sHaving
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
    		$sHaving
    
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
}