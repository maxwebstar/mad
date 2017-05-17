<?php
class Administrator_OptimizationController extends Zend_Controller_Action
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }

    public function indexAction(){}

    public function pendingAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'optimization_pending';
    }

    public function doneAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'optimization_done';
    }

    public function allAction()
    {
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'optimization_all';

        $tableCase = new Application_Model_DbTable_Optimization_Case();

        $this->view->case = $tableCase->getAllData();

    }

    public function changeAction()
    {
        $tableSite = new Application_Model_DbTable_Sites();
        $tableSite->update(array('optimization' => 1, 'optimization_date' => date('Y-m-d')), 'SiteID = '. (int) $this->_getParam('SiteID'));

        $this->_redirect($_SERVER['HTTP_REFERER']);
    }

    public function ajaxDoneAction()
    {
        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );

        $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.alexaRank', 's.optimization_date', 's.SiteID', 's.PubID', 's.SiteID' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'alexaRank', 'optimization_date', 'SiteID', 'PubID', 'SiteID' );
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.alexaRank', 5 => 's.optimization_date' );

	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";

	/* DB table to use */
	$sTable = "sites AS s";
        $sJoin = " JOIN users AS u ON u.id = s.PubID
                   JOIN tags AS t ON t.site_id = s.SiteID
                   JOIN madads_rubicon_optimization AS mro ON mro.SiteID = s.SiteID AND mro.impressions > 100000 ";

        $sGroup = "GROUP BY s.SiteID ";

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

                case 'new':         $sWhere .= ' WHERE s.optimization IS NULL '; break;
                case 'optimized':   $sWhere .= ' WHERE s.optimization = 1 ';     break;
                default:break;

            }

        } else {

            switch($_GET['filter']){

                case 'new':         $sWhere .= ' AND s.optimization IS NULL '; break;
                case 'optimized':   $sWhere .= ' AND s.optimization = 1 ';     break;
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


        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }


    public function ajaxPendingAction()
    {
        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );

        $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 'mro.impressions', 's.alexaRank', 's.floor_pricing', 'mro.query_date', 's.SiteID', 's.PubID', 's.SiteID' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions', 'alexaRank', 'floor_pricing', 'query_date', 'SiteID', 'PubID', 'SiteID' );
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 'mro.impressions', 5 => 's.alexaRank', 6 => 's.floor_pricing', 7 => 'mro.query_date' );

	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";

	/* DB table to use */
	$sTable = "sites AS s";
        $sJoin = " JOIN users AS u ON u.id = s.PubID
                   JOIN tags AS t ON t.site_id = s.SiteID
                   JOIN (SELECT SiteID, impressions, query_date FROM madads_rubicon_optimization ORDER BY query_date ASC) AS mro ON mro.SiteID = s.SiteID AND mro.impressions > 100000 ";

        $sGroup = "GROUP BY s.SiteID ";

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

                case 'new':         $sWhere .= ' WHERE s.optimization IS NULL '; break;
                case 'optimized':   $sWhere .= ' WHERE s.optimization = 1 ';     break;
                default:break;

            }

        } else {

            switch($_GET['filter']){

                case 'new':         $sWhere .= ' AND s.optimization IS NULL '; break;
                case 'optimized':   $sWhere .= ' AND s.optimization = 1 ';     break;
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


        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }

    public function clearPendingAction()
    {
        set_time_limit(0);

        echo 'ok <br/>';

        $tableOptimization = new Application_Model_DbTable_Rubicon_Optimization();

        $sql = $tableOptimization->select()->setIntegrityCheck(false)
                                 ->from(array('mro' => 'madads_rubicon_optimization'), array('*'))
                                 ->join(array('s' => 'sites'),('s.SiteID = mro.SiteID'), array(''))
                                 ->where('s.optimization IS NULL')
                                 ->limit(100);

        $dataOptimization = $tableOptimization->fetchAll($sql);

        foreach($dataOptimization as $iter){ $iter->delete(); }

        if(count($dataOptimization) == 100) $this->_redirect('/administrator/optimization/clear-pending');

        exit();
    }


    public function ajaxAllAction()
    {
        $variables  = $_GET;
        set_time_limit(0);
        //error_reporting(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );

        $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 'IF(os.ValueClick IS NULL, 4, os.ValueClick)', 'IF(os.Advertising IS NULL, 4, os.Advertising)', 'IF(os.Media IS NULL, 4, os.Media)', 'IF(os.UnderDog IS NULL, 4, os.UnderDog)', 'IF(os.AudienceScience IS NULL, 4, os.AudienceScience)', 's.PubID', 's.optimization', 's.alexaRank', 's.impressions_1day_ago', 'IF(mro.impressions IS NULL, 0, mro.impressions)' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'IF(os.ValueClick IS NULL, 4, os.ValueClick)', 'IF(os.Advertising IS NULL, 4, os.Advertising)', 'IF(os.Media IS NULL, 4, os.Media)', 'IF(os.UnderDog IS NULL, 4, os.UnderDog)', 'IF(os.AudienceScience IS NULL, 4, os.AudienceScience)', 'PubID', 'optimization', 'SiteID', 'alexaRank', 'impressions_1day_ago', 'IF(mro.impressions IS NULL, 0, mro.impressions)' );
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 'os.ValueClick', 5 => 'os.Advertising', 6 => 'os.Media', 7 => 'os.UnderDog', 8 => 'os.AudienceScience', 10 => 's.optimization' );

	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";

	/* DB table to use */
	$sTable = "sites AS s";
        $sJoin = " JOIN users AS u ON u.id = s.PubID
                   JOIN tags AS t ON t.site_id = s.SiteID
                   LEFT JOIN (SELECT SiteID, impressions, query_date FROM madads_rubicon_optimization GROUP BY SiteID) AS mro ON mro.SiteID = s.SiteID
              LEFT JOIN optimization_site_case AS os ON os.SiteID = s.SiteID ";

        $sGroup = "GROUP BY s.SiteID";

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


        if($sWhere == "") $sWhere = "WHERE s.status = 3 AND (u.reject = 0 OR u.reject IS NULL)  ";
                     else $sWhere .= " AND s.status = 3 AND (u.reject = 0 OR u.reject IS NULL) ";

        if(!empty($_GET['filterMedia'])){

            if($_GET['filterMedia'] != 'all' && $_GET['filterMedia'] != 4) $sWhere .= " AND os.Media = '".$_GET['filterMedia']."' ";

        }
        if(!empty($_GET['filterUnderDog'])){

            if($_GET['filterUnderDog'] != 'all' && $_GET['filterUnderDog'] != 4) $sWhere .= " AND os.UnderDog = '".$_GET['filterUnderDog']."' ";

        }
        if(!empty($_GET['filterValueClick'])){

            if($_GET['filterValueClick'] != 'all' && $_GET['filterValueClick'] != 4) $sWhere .= " AND os.ValueClick = '".$_GET['filterValueClick']."' ";

        }
        if(!empty($_GET['filterAdvertising'])){

            if($_GET['filterAdvertising'] != 'all' && $_GET['filterAdvertising'] != 4) $sWhere .= " AND os.Advertising = '".$_GET['filterAdvertising']."' ";

        }
        if(!empty($_GET['filterAudienceScience'])){

            if($_GET['filterAudienceScience'] != 'all' && $_GET['filterAudienceScience'] != 4) $sWhere .= " AND os.AudienceScience = '".$_GET['filterAudienceScience']."' ";

        }

        if(isset($_GET['filter'])){

            switch($_GET['filter']){

                case 'pending': $sWhere .= ' AND s.optimization IS NULL '; break;
                case 'done'   : $sWhere .= ' AND s.optimization = 1 ';     break;
                default : break;
            }
        }

        if (intval($this->_getParam('showLive'))) {
            $sWhere .= ($sWhere == '' ? ' WHERE ' : ' AND ').'(s.live = 1 OR s.approved >= "'.date('Y-m_d 00:00:00', strtotime('-1 week')).'")';
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
}
?>
