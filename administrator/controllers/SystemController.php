<?php

//require_once("Net/SSH2.php");

class Administrator_SystemController extends Zend_Controller_Action {
	
/*	protected $_varnishLogins = array(
			
			array("name"=>"cache3.ord.madadsmedia.com", "ip"=>"66.55.80.189","login"=>"root","password"=>"6d634b61"),
			array("name"=>"cache2.ord.madadsmedia.com", "ip"=>"66.55.80.139","login"=>"root","password"=>"1edc05ca"),
			array("name"=>"cache1.ord.madadsmedia.com", "ip"=>"66.55.80.153","login"=>"root","password"=>"1d280049"),
			array("name"=>"cache1-la.madadsmedia.com", "ip"=>"66.55.90.6","login"=>"root","password"=>"72a959ed"),
			
			array("name"=>"Dedicated LA Server 1", "ip"=>"199.168.112.40","login"=>"root","password"=>"fr33c0d3"),
			array("name"=>"Dedicated LA Server 2", "ip"=>"199.168.112.41","login"=>"root","password"=>"fr33c0d3"),
			array("name"=>"Dedicated LA Server 3", "ip"=>"199.168.112.42","login"=>"root","password"=>"fr33c0d3"),
			array("name"=>"Dedicated LA Server 4", "ip"=>"199.168.112.43","login"=>"root","password"=>"fr33c0d3"),
				
			);
*/	
	public function init() {
		
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('admin');
		$this->view->headTitle('System');
	}

	public function indexAction() {

		
	}
	
	public function phpinfoAction(){
		
	}
	
/*	public function restartvarnishAction(){
		
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$index = $this->getRequest()->getParam("index");

		$output = array();
		$varnishLogin = $this->_varnishLogins[$index];
		$output = $this->_loginAndRestartVarnish($varnishLogin);
						
		$response = array("error"=>false, "data" =>$output);
		
		$this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
	}*/
	public function dataAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $status = $this->getRequest()->getParam("status");
        $loginTable = new Zend_Db_Table('madads_login_activity');
        $select = $loginTable->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                    ->setIntegrityCheck(false);
        
        $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");
            $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");

            $orderArray = array();
            if (is_numeric($iSortCol_0)):
                switch ($iSortCol_0):
                    case 0 :
                        $orderArray = array('id ' . $sSortDir_0);
                        break;
                    case 1 :
                        $orderArray = array('ip_address ' . $sSortDir_0);
                        break;
                    case 2:
                        $orderArray = array('activity_timestamp ' . $sSortDir_0);
                        break;
                    case 3:
                        $orderArray = array('activity_type ' . $sSortDir_0);
                        break;
                    case 4:
                        $orderArray = array('user ' . $sSortDir_0);
                        break;
                    default:
                        $orderArray = array('id ' . $sSortDir_0);
                        break;
                endswitch;
            endif;


            $select->join('users', 'madads_login_activity.user_id=users.id', array('id' => 'madads_login_activity.id',
                "ip_address" => "madads_login_activity.ip_address",
                'activity_timestamp' => 'madads_login_activity.activity_timestamp', 'activity_type' => 'madads_login_activity.activity_type',
                'user' => 'users.email'
                    //,
                    //'countWS' => 'count(DISTINCT website)'))
            ))
            ->order($orderArray)
            //->limit((int)$_GET['iDisplayLength'],(int)$_GET['iDisplayStart'])
            ;
            //$select->from(array("e" => "madads_login_activity"))->setIntegrityCheck(false)
            //        ->join(array("s" => "users"), '`s`.id = `e`.user_id', "s.email AS user");
        $sSearch = $this->getRequest()->getParam("sSearch");
//
        if (strlen($sSearch) >= 3):
            $select->where("madads_login_activity.ip_address LIKE '%" . $sSearch . "%' OR madads_login_activity.activity_type LIKE '%" . $sSearch . "%' OR users.email LIKE '%" . $sSearch . "%'");
        endif;

            $loginActivities = $loginTable->fetchAll($select);
//        $loginActivities = $loginTable->fetchAll();

            $sEcho = (int) $_GET['sEcho'];
            $output = array(
                "select" => $select->__toString(),
                "sEcho" => $sEcho++,
                "iTotalRecords" => count($loginActivities),
                "iTotalDisplayRecords" => count($loginActivities),
                "sColumns" => 7,
                "aaData" => array()
            );

            $aaData = array();
        if (count($loginActivities) > 0):

            $select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
            $select->assemble();
            $loginActivities = $loginTable->fetchAll($select);

            foreach ($loginActivities as $loginActivity):
                $loginData = array();
                $loginData[0] = $loginActivity->id;
                $loginData["id"] = $loginActivity->id;
                $loginData[1] = $loginActivity->ip_address;
                $loginData["ip_address"] = $loginActivity->ip_address;
                $loginData[2] = $loginActivity->activity_timestamp;
                $loginData["activity_timestamp"] = $loginActivity->activity_timestamp;
                $loginData[3] = $loginActivity->activity_type;
                $loginData["activity_type"] = $loginActivity->activity_type;
                $loginData[4] = $loginActivity->user;
                $loginData["user"] = $loginActivity->user;
                $aaData[] = $loginData;
            endforeach;

            $output['aaData'] = $aaData;

        endif;
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
        }
        
    public function activityAction()
    {
	    $layout = Zend_Layout::getMvcInstance();
	    $layout->nav = 'system';  
	    //$activitiesTable = new Zend_Db_Table('madads_login_activity');
	    //$loginActivities = $activitiesTable->fetchAll();
	    //$this->view->loginActivities = $loginActivities;
	    $this->render('new-activity');
    }
    
    public function ajaxActivityAction()
    {
        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();

        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );

            $aColumns = array( 'u.id', 'mla.ip_address', 'mla.activity_timestamp', 'mla.activity_type', 'u.email');
             $Columns = array( 'id', 'ip_address', 'activity_timestamp', 'activity_type', 'email');
         $likeColumns = array( 0 => 'u.id', 1 => 'mla.ip_address', 2 => 'mla.activity_timestamp', 3 => 'mla.activity_type', 4 => 'u.email');


            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "mla.id";

            $sJoin = " LEFT JOIN users AS u ON u.id = mla.user_id ";

            /* DB table to use */
            $sTable = "madads_login_activity AS mla";

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
	if($_GET['iSortCol_0'] == 13)
	{
		$sOrder = "ORDER BY o.Value ".$_GET['sSortDir_0']." ";
	}
	else
	{
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
			else if($Columns[$i] == "Value")
			{
				if($aRow[ $Columns[$i] ] == null)
					$row[] = "Add Value";
				else
					$row[] = $aRow[ $Columns[$i] ];
			}
			else if ( $Columns[$i] != ' ' )
			{
				/* General output */
				$row[] = $aRow[ $Columns[$i] ];
			}
		}
		//$row[] = 'Add Value';
		$output['aaData'][] = $row;
	}


        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
	    
    }	
    /*
	protected function _loginAndRestartVarnish($varnishLogin){
		
		$name = $varnishLogin['name'];
		$ip = $varnishLogin['ip'];
		$login =$varnishLogin['login'];
		$password = $varnishLogin['password'];
		
		$cmd = "service varnish restart";
		$output = array();
				
		$ssh = new Net_SSH2($ip);
		$output[] = "Connecting to $name...\n";
		
		if (!$ssh->login($login, $password)) :
			$output[] = "Login Failed\n";
		else :
			$output[] = "$ $cmd\n";
			$output[] = $ssh->exec($cmd);
			$output[] = "Disconnecting from $name\n";
			$output[] = "\n";
		endif;
		
		$ssh->disconnect();
		
		return $output;
	}

	*/

}
?>
