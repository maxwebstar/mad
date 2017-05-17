<?php
class Administrator_UrlController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->layout()->disableLayout(); 
        
        $siteID = (int)$this->_getParam('id');
        
        $tableSite = new Application_Model_DbTable_Sites();
        $dataSite = $tableSite->getSiteInfoByID($siteID);
        
        $this->view->siteID = $siteID; 
        $this->view->data = $dataSite;
        $this->view->date = array('start' => date('Y M, d', strtotime('-1 day')), 'end' => date('Y M, d'));
    }
    
        
    
    public function getAjaxAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
                
        $aColumns = array(  'IF(psa.iframe, psa.src, psa.url)', 'MAX(DATE_FORMAT(psa.query_date, "%Y %b, %d"))', 'SUM(psa.num)', 'IF(psa.iframe, psa.src, psa.url)');
        $Columns = array(  'IF(psa.iframe, psa.src, psa.url)', 'MAX(DATE_FORMAT(psa.query_date, "%Y %b, %d"))', 'SUM(psa.num)', 'IF(psa.iframe, psa.src, psa.url)');
        $likeColumns = array( 0 => 'IF(psa.iframe, psa.src, psa.url)', 1 => 'MAX(DATE_FORMAT(psa.query_date, "%Y %b, %d"))', 2 => 'SUM(psa.num)');  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "psa.SiteID";
	
	/* DB table to use */
	$sTable = "madads_psa AS psa";

        $sWhere = " WHERE psa.SiteID = '".$_GET['SiteID']."' ";
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
        
        if($sWhere == "") $sWhere = " WHERE psa.query_date >= '".$_GET['date_start']."' AND psa.query_date <= '".$_GET['date_end']."' ";  
        else              $sWhere .= " AND psa.query_date >= '".$_GET['date_start']."' AND psa.query_date <= '".$_GET['date_end']."' "; 

        /*$sWhere = "";*/
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere .= "AND (";
		foreach ( $likeColumns as $key => $field )
		{
			$sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
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
    
}
?>
