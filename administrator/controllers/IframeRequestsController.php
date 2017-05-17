<?php
/**
 * Description of SizesRequestsController
 *
 * @author nik
 */
class Administrator_IframeRequestsController extends Zend_Controller_Action 
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }

    public function indexAction()
    {
        $this->_layout->nav = 'iframe-requests';
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
                
        $aColumns = array( 's.SiteID', 's.SiteName', 'u.email', 's.alexaRank', 'ri.created', 's.SiteID');
         $Columns = array( 'SiteID', 'SiteName', 'email', 'alexaRank', 'created', 'SiteID');
     $likeColumns = array( 0 => 's.SiteName', 1 => 'u.email', 2 => 's.alexaRank', 3 => 'ri.created', 4 => 's.SiteID');  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "ri.SiteID";
	
	/* DB table to use */
	$sTable = "request_iframe AS ri";
        $sJoin = " LEFT JOIN sites AS s ON ri.SiteID = s.SiteID
                   LEFT JOIN users AS u ON u.id = s.PubID ";
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
    
    public function enableAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $id = (int)$this->_getParam('id');
        
        if($id){
            $tableSite = new Application_Model_DbTable_Sites();
            $tableWantBaner = new Application_Model_DbTable_Sites_WantIframe();

            $tableSite->update(array('iframe_tags'=>1), "SiteID='".$id."'");
            $tableWantBaner->delete("SiteID='".$id."'");

            $siteTagsModel = new Application_Model_DbTable_SitesTags();
            $siteTagsModel->changeAction($id, 'gen', APPLICATION_ENV);
        }
        $this->_redirect('/administrator/iframe-requests');        
    }
    
    public function disableAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $id = (int)$this->_getParam('id');
        
        if($id){
            $tableSite = new Application_Model_DbTable_Sites();
            $tableWantBaner = new Application_Model_DbTable_Sites_WantIframe();

            $tableSite->update(array('iframe_tags'=>NULL), "SiteID='".$id."'");
            $tableWantBaner->delete("SiteID='".$id."'");

            $siteTagsModel = new Application_Model_DbTable_SitesTags();
            $siteTagsModel->changeAction($id, 'gen', APPLICATION_ENV);
        }
        
        $this->_redirect('/administrator/iframe-requests');
    }    
}