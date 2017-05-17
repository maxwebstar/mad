<?php
class Administrator_ReferralController extends Zend_Controller_Action
{
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
        
        $this->view->headTitle('Referral');
    }
    
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'ref_system';

        $tableStat = new Application_Model_DbTable_Sites_BaseStatistic();
        $firstDate = $tableStat->getFirstDate();
        $firstDate = ($firstDate ? $firstDate : date('Y-m-d'));

        $this->view->firstReportDate = $firstDate;
        $this->view->startDate = $firstDate;
        $this->view->endDate = date('Y-m-d');
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

        if(!empty($_GET['startDate'])){ $filterStart = " AND rs.query_date >= '".$_GET['startDate']."' "; }
        if(!empty($_GET['endDate'])){ $filterEnd = " AND rs.query_date <='".$_GET['endDate']."' "; }

        $aColumns = array( 'r.id', 'r.name', 'r.num_click', 'SUM(rs.num_click)', 'SUM(rs.num_registration)','SUM(rs.num_registration)', 'r.id', 'SUM(rs.impressions)', 'ROUND(SUM(rs.revenue),2)', 'r.id', 'r.id');
        $Columns = array( 'id', 'name', 'num_click', 'SUM(rs.num_click)', 'SUM(rs.num_registration)','SUM(rs.num_registration)', 'r.id', 'SUM(rs.impressions)', 'ROUND(SUM(rs.revenue),2)', 'id', 'id');
        $likeColumns = array( 0 => 'r.id', 1 => 'r.name');

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "r.id";

        /* DB table to use */
        $sTable = "referral AS r";
        $sJoin = " LEFT JOIN referral_stat AS rs ON r.id = rs.refID ".$filterStart.$filterEnd;
        $sGroup = " GROUP BY r.id ";
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


        if(!empty($_GET['startDate'])){ $filterStart2 = " AND `created` >= '".$_GET['startDate']."' "; }
        if(!empty($_GET['endDate'])){ $filterEnd2 = " AND `created` <='".$_GET['endDate']."' "; }
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
            $sql_count = 'SELECT COUNT(*) FROM `users` WHERE `referral_id` = '.$row[0].$filterStart2.$filterEnd2;
            $result_count = mysql_query( $sql_count, $gaSql['link'] ) or die(mysql_error());
            $aresult_count = mysql_fetch_array($result_count);
            $row[4] = $aresult_count['COUNT(*)'];
            $row[6] = $aresult_count['COUNT(*)'];
            $output['aaData'][] = $row;
        }

        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    
    public function addAction()
    {

        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'ref_system';

        $tableReferral = new Application_Model_DbTable_Referral();
        $dataReferral = $tableReferral->createRow();
        $dataParent = $tableReferral->getArrOption();

        $form = new Application_Form_Referral_Add($dataReferral, $dataParent);

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->_getAllParams())) {

                $form->appendData();

                $dataReferral->setFromArray(array(
                    'num_click' => 0,
                    'num_registration' => 0
                ));

                $dataReferral->save();

                $this->_redirect('/administrator/referral/index');
            }

        }

        $this->view->form = $form;
    }
    
    public function editAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'ref_system';

        $referral_id = $this->getRequest()->getParam('id');

        $tableReferral = new Application_Model_DbTable_Referral();

        $sql = $tableReferral->select()
            ->where('id = ?', $referral_id);

        $dataReferral = $tableReferral->fetchRow($sql);
        $dataParent = $tableReferral->getArrOption();

        $form = new Application_Form_Referral_Edit($dataReferral, $dataParent);
        $form->showOldData();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->_getAllParams())) {

                $form->appendData();

                $dataReferral->save();

                $this->_redirect('/administrator/referral/index');
            }

        }

        $this->view->form = $form;
        $this->view->data = $dataReferral;
    }
    
    public function deleteAction()
    {
        $referral_id = $this->getRequest()->getParam('id');
        
        $tableReferral = new Application_Model_DbTable_Referral();
        
        $sql = $tableReferral->select()
                             ->where('id = ?', $referral_id);
        
        $dataReferral = $tableReferral->fetchRow($sql);
        $dataReferral->delete();
        
        $this->_redirect('/administrator/referral/index');        
    }
    
    public function userAction()
    {
        $referral_id = $this->getRequest()->getParam('ref_id');
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to');
        
        $referral_id ? TRUE : $referral_id = 0;  
        //$year ? TRUE : $year = date('Y');
        //$month ? TRUE : $month = date('n');
               
        $tableUser = new Application_Model_DbTable_Users();
        $tableReferral = new Application_Model_DbTable_Referral();
        
        $sql = $tableUser->select()->setIntegrityCheck(false)
                         ->from(array('u' => 'users'),
                                 array('u.id', 'u.name', 'u.email', 'u.referral_id'))
                         ->where('u.active = 1')
                         ->where('u.referral_id IS NOT NULL')
                         ->joinLeft('users_reports_final as rep', "u.id=rep.PubID", array('SUM(rep.revenue) as revenue'))
                         ->group("u.id")
                         ->order('u.id DESC');
        
        if($referral_id) $sql->where('u.referral_id = ?', $referral_id);
        if($from && $to){ 
	        $sql->where("DATE_FORMAT(u.created, '%Y-%m-%d') >= ?", $from); 
	        $sql->where("DATE_FORMAT(u.created, '%Y-%m-%d') <= ?", $to); 
	    }
         
        $dataUser = $tableUser->fetchAll($sql);

        $sql = $tableReferral->select()->order('id');
        $dataReferral = $tableReferral->fetchAll($sql);
        
        $this->view->data = $dataUser;
        $this->view->referral = $dataReferral;
        $this->view->id = $referral_id;
        $this->view->from = $from;
        $this->view->to = $to;
    }
    
    public function viewAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'ref_system';

        $tableStat = new Application_Model_DbTable_Sites_BaseStatistic();
        $firstDate = $tableStat->getFirstDateByRefID($this->_getParam('id'));
        $firstDate = ($firstDate ? $firstDate : date('Y-m-d'));

        $this->view->firstReportDate = $firstDate;
        $this->view->startDate = $this->_getParam('from');//date('Y-m-d', time()-60*60*24*365);//$firstDate /*$this->_getParam('from')*/;
        $this->view->endDate = $this->_getParam('to');//date('Y-m-d') /*$this->_getParam('to')*/;
        $this->view->refID = $this->_getParam('id');
    }
    
    public function getViewAjaxAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
                            /* 0         1                2                        3                  4                                            5*/         
        $aColumns = array( 'u.name', 'u.email', 'SUM(sb.impressions)', 'ROUND(SUM(sb.revenue),2)',  'u.id', 'GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ":", s.SiteName ) AS char ) SEPARATOR "," )');
         $Columns = array( 'name', 'email', 'SUM(sb.impressions)', 'ROUND(SUM(sb.revenue),2)',  'id', 'GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ":", s.SiteName ) AS char ) SEPARATOR "," )');
     $likeColumns = array( 0 => 'u.name', 1 => 'u.email');  
            
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "u.id";
	
	/* DB table to use */
	$sTable = " site_base_statistic AS sb ";
        $sJoin = " JOIN users AS u ON u.id = sb.PubID
                   JOIN sites AS s ON s.SiteID = sb.SiteID ";
	$sGroup = " GROUP BY u.id "; 
        $sHaving = " HAVING SUM(sb.impressions) > 0 "; 
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

        if($_GET['refID']){        
            if($sWhere == "") $sWhere = " WHERE u.referral_id = '".$_GET['refID']."' ";  
            else              $sWhere .= " AND u.referral_id ='".$_GET['refID']."' ";         
        }                
            
        if($_GET['startDate']){        
            if($sWhere == "") $sWhere = " WHERE sb.query_date >= '".$_GET['startDate']."' ";  
            else              $sWhere .= " AND sb.query_date >= '".$_GET['startDate']."' ";         
        }

        if($_GET['endDate']){        
            if($sWhere == "") $sWhere = " WHERE sb.query_date <= '".$_GET['endDate']."' ";  
            else              $sWhere .= " AND sb.query_date <='".$_GET['endDate']."' ";         
        }        
        
        if($sWhere == ""){ $sWhere = " WHERE sb.AdSize = 0 "; } 
                    else { $sWhere.= " AND sb.AdSize = 0 "; }
        
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

    public function signupAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'new-account';

        $this->view->startDate = $this->_getParam('startDate');
        $this->view->endDate = $this->_getParam('endDate');
        $this->view->referral_id = $this->_getParam('id');
    }


    public function getSignupAjaxAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        /* MySQL connection */
        $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

        $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

        mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        //                                   0                        1                 2               3                                                  4                                                                                                     5                    6                                                  7                                                8                         9                 10     11          12               13            14
        $aColumns = array('u.reg_AdExchage', 'u.adx_name', 'u.company', 'u.email', 'GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ":", s.SiteName ) AS char ) SEPARATOR "," )', 'u.revenue_today', 'u.revenue_1day_ago', 'DATE_FORMAT(u.created, "%Y-%m-%d")', 'u.enable_wire_transfer', 'u.reachout_mail', 'u.id', 'u.id', 'u.inviteRequest', 'u.url', 'u.active',  'u.id', 'u.id');
        $Columns = array('reg_AdExchage',    'adx_name',   'company',   'email',  'GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ":", s.SiteName ) AS char ) SEPARATOR "," )', 'revenue_today',    'revenue_1day_ago',  'DATE_FORMAT(u.created, "%Y-%m-%d")', 'enable_wire_transfer',    'reachout_mail', 'id',   'id',  'inviteRequest',   'url', 'active', 'id',   'id');
        $likeColumns = array( 0 => 'u.id', 1 => 'u.adx_name', 2 => 'u.company', 3 => 'u.email', 5 => 'DATE_FORMAT(u.created, "%Y-%m-%d")');


        $referral_id = $this->_getParam('referral_id');
        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "u.id";

        /* DB table to use */
        $sTable = " users AS u ";
        $sJoin = " LEFT JOIN sites AS s ON s.PubID = u.id ";
        $sGroup = " GROUP BY u.id ";

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
                    if($_GET['iSortCol_0'] == '10')
                        $sOrder .= " u.status ".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
                    else
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

        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];

        if(!empty($_GET['startDate'])){ $filterStart = " u.created >= '".$_GET['startDate']."' "; }
        if(!empty($_GET['endDate'])){ $filterEnd = " AND u.created <='".$_GET['endDate']."' "; }
        if($referral_id){ $filterReferral = " AND u.referral_id =".$referral_id." "; }
        $whereUser .= $filterStart.$filterEnd.$filterReferral;
        //if(empty($_GET['filterDeniedUser'])){ $whereUser = " u.status = 3 AND DATE_FORMAT(u.date_confirm, '%Y-%m-%d') = '".$confirmDate."' "; }
        //else{ $whereUser = " u.status = 2 AND DATE_FORMAT(u.date_reject_user, '%Y-%m-%d') = '".$confirmDate."' "; }

        if($sWhere == ""){

            $sWhere = "WHERE ".$whereUser;

        } else {

            $sWhere .= " AND ".$whereUser;
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
            $row['status'] = $row[14];
            $output['aaData'][] = $row;
        }


        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);

    }
}
