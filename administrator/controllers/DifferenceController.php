<?php
class Administrator_DifferenceController extends Zend_Controller_Action
{  

    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
                
        $this->view->headTitle('Difference');
    }
    
    public function indexAction(){  }
    
    public function impressionsAction()
    {
        
    }
    
    public function ajaxImpressionsAction()
    {
         set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array( 'd.PubID', 'd.SiteID', 's.SiteName', 'd.difference', 'd.impressions_estim', 'd.impressions_final', 'd.difference_type', 'd.query_date_estim', 'd.query_date_final', 'm.cpm', 'm.created', 'd.floor_pricing', 'd.type', 'd.PubID' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'difference', 'impressions_estim', 'impressions_final', 'difference_type', 'query_date_estim', 'query_date_final', 'cpm', 'created', 'floor_pricing', 'type', 'PubID' );
     $likeColumns = array( 0 => 'd.PubID', 1 => 'd.SiteID', 2 => 's.SiteName', 7 => 'd.query_date_estim', 8 => 'd.query_date_final' );  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "d.id";
	
	/* DB table to use */
	$sTable = "difference_impression AS d";
        $sJoin = " JOIN sites AS s ON s.SiteID = d.SiteID AND s.status = 3                   
              LEFT JOIN minimum_cpm AS m ON m.SiteID = d.SiteID AND m.status = 3 ";
                
        
        $sGroup = "GROUP BY d.id ";
	
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
            
            switch($_GET['filterMostValue']){
                case '0': $sWhere = ' WHERE d.difference_type = 0 '; break;
                case '1': $sWhere = ' WHERE d.difference_type = 1 '; break;
                default:break;
            }
          
        } else {
            
            switch($_GET['filterMostValue']){
                case '0': $sWhere .= ' AND d.difference_type = 0 '; break;
                case '1': $sWhere .= ' AND d.difference_type = 1 '; break;
                default:break;
            }
        }
        if($sWhere == ""){
            
            switch($_GET['filterFloorPricing']){
                case '0': $sWhere = ' WHERE d.floor_pricing IS NULL '; break;
                case '1': $sWhere = ' WHERE d.floor_pricing = 1 '; break;
                default:break;
            }
          
        } else {
            
            switch($_GET['filterFloorPricing']){
                case '0': $sWhere .= ' AND d.floor_pricing IS NULL '; break;
                case '1': $sWhere .= ' AND d.floor_pricing = 1 '; break;
                default:break;
            }
        }
        if($sWhere == ""){
            
            switch($_GET['filterTag']){
                case '4': $sWhere = ' WHERE d.type = 4 '; break;
                case '5': $sWhere = ' WHERE d.type = 5 '; break;
                case '6': $sWhere = ' WHERE d.type = 6 '; break;
                default:break;
            }
          
        } else {
            
            switch($_GET['filterTag']){
                case '4': $sWhere .= ' AND d.type = 4 '; break;
                case '5': $sWhere .= ' AND d.type = 5 '; break;
                case '6': $sWhere .= ' AND d.type = 6 '; break;
                default:break;
            }
        }

        if($sWhere == ""){ if($_GET['filterDate']) $sWhere = ' WHERE (d.query_date_estim = "'.$_GET['filterDate'].'" OR d.query_date_final = "'.$_GET['filterDate'].'") '; } 
                    else { if($_GET['filterDate']) $sWhere .= '  AND (d.query_date_estim = "'.$_GET['filterDate'].'" OR d.query_date_final = "'.$_GET['filterDate'].'") '; }
        
           $filterDifference = (int) $_GET['filterDifference'];
        if($filterDifference){
            
            if($sWhere == "") $sWhere = ' WHERE d.difference >= "'.$filterDifference.'" ';
                         else $sWhere .= ' AND d.difference >= "'.$filterDifference.'" ';       
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
        
        if($output['iTotalRecords'] == 0){
            
            $output['iTotalDisplayRecords'] = 0;
            $output['aaData'] = array();
            
        }
             
       
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    
    public function ecpmAction()
    {
        
    }
    
    public function ajaxEcpmAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array( 'd.PubID', 'd.SiteID', 's.SiteName', 'd.type', 'd.difference_type', 'd.difference', 'd.estim_ecpm', 'd.final_ecpm', 'd.estim_date', 'd.final_date', 'm.cpm', 'm.created', 'd.PubID' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'type', 'difference_type', 'difference', 'estim_ecpm', 'final_ecpm', 'estim_date', 'final_date', 'cpm', 'created', 'PubID' );
     $likeColumns = array( 0 => 'd.PubID', 1 => 'd.SiteID', 2 => 's.SiteName', 8 => 'd.estim_date', 9 => 'd.final_ecpm' );  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "d.id";
	
	/* DB table to use */
	$sTable = "difference_ecpm AS d";
        $sJoin = " JOIN sites AS s ON s.SiteID = d.SiteID AND s.status = 3
              LEFT JOIN minimum_cpm AS m ON m.SiteID = d.SiteID AND m.status = 3 ";
                
        
        $sGroup = "GROUP BY d.id ";
	
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
        
        if($sWhere == ""){ if($_GET['filterDate']) $sWhere = ' WHERE (d.estim_date = "'.$_GET['filterDate'].'" OR d.final_date = "'.$_GET['filterDate'].'") '; } 
                   else { if($_GET['filterDate']) $sWhere .= ' AND (d.estim_date = "'.$_GET['filterDate'].'" OR d.final_date = "'.$_GET['filterDate'].'") '; }
        
           $filterDifferenceType = (int) $_GET['filterDifferenceType'];
        if($filterDifferenceType){
            
            if($sWhere == "") $sWhere = ' WHERE d.difference_type = "'.$filterDifferenceType.'" ';
                         else $sWhere .= ' AND d.difference_type = "'.$filterDifferenceType.'" ';       
        } 
        
        if($sWhere == ""){
            
            switch($_GET['filterTag']){
                case '4': $sWhere = ' WHERE d.type = 4 '; break;
                case '5': $sWhere = ' WHERE d.type = 5 '; break;
                case '6': $sWhere = ' WHERE d.type = 6 '; break;
                default:break;
            }
          
        } else {
            
            switch($_GET['filterTag']){
                case '4': $sWhere .= ' AND d.type = 4 '; break;
                case '5': $sWhere .= ' AND d.type = 5 '; break;
                case '6': $sWhere .= ' AND d.type = 6 '; break;
                default:break;
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
        
        if($output['iTotalRecords'] == 0){
            
            $output['iTotalDisplayRecords'] = 0;
            $output['aaData'] = array();
            
        }
             
       
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    
}
?>
