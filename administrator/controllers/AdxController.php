<?php
class Administrator_AdxController extends Zend_Controller_Action
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $this->_layout->nav = 'adx_registration';
        
        $this->view->filter = $this->_getParam('filter');        
    }
    
    public function changeAction()
    {
        $tableUser = new Application_Model_DbTable_Users();
        
        $sql = $tableUser->select()->where('id = ?', $this->_getParam('PubID'));
        
        $dataUser = $tableUser->fetchRow($sql);
        
        switch($this->_getParam('do')){
            
            case '2':      $dataUser->inviteRequest = 2;
                           $dataUser->date_reject = date("Y-m-d");
                           $dataUser->reg_AdExchage = null; break;
            case '3':      $dataUser->inviteRequest = 3; 
                           $dataUser->date_approve = date("Y-m-d");
                           $dataUser->reg_AdExchage = 1;    break;
            case 'delete': $dataUser->inviteRequest = null; break;
            
            default: $this->_redirect('/administrator/adx/index'); break;   
            
        }
        
        $dataUser->save();
        
        $this->_redirect('/administrator/adx/index');
    }
    
    public function editInviteUrlAction()
    {
        $this->_helper->layout()->disableLayout();
        
        $PubID = (int) $this->_getParam('id');
        
        $form = new Application_Form_Adx_EditUrl();
        $tableUser = new Application_Model_DbTable_Users();
        
        $dataUser = $tableUser->getUserAllInfoById($PubID);
        
        if($this->getRequest()->isPost()){ 
            if($form->isValid($this->getRequest()->getPost())){
                
                $tableUser->update(array('inviteURL' => $form->getValue('inviteURL'),
                                         'mail_send_check_invite_url' => 0, 
                                         'inviteRequest' => 0), array('id = ?' => $PubID));
                
                $this->view->message = 'Data has been saved';
                
            } else { $this->view->formErrors = $form->getMessages();
                     $this->view->formValues = $form->getValues(); }
            
        } else { $this->view->formValues = array('inviteURL' => $dataUser['inviteURL']); }
    }
    
    public function ajaxGetAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
        
        $aColumns = array('u.id', 'u.name', 'u.company', 'u.email', 'IF(u.inviteRequest=1,DATE_FORMAT(u.date_pending, "%Y %b %d"),IF(u.inviteRequest=2,DATE_FORMAT(u.date_reject, "%Y %b %d"),IF(u.inviteRequest=3,DATE_FORMAT(u.date_approve, "%Y %b %d"),DATE_FORMAT(u.date_invited, "%Y %b %d"))))', 'u.inviteRequest', 'u.inviteRequest', 'u.id', 'DATE_FORMAT(u.date_pending, "%Y %b %d")', 'DATE_FORMAT(u.date_reject, "%Y %b %d")', 'DATE_FORMAT(u.date_approve, "%Y %b %d")', 'DATE_FORMAT(u.date_invited, "%Y %b %d")', 'u.url');
         $Columns = array('id', 'name', 'company', 'email', 'IF(u.inviteRequest=1,DATE_FORMAT(u.date_pending, "%Y %b %d"),IF(u.inviteRequest=2,DATE_FORMAT(u.date_reject, "%Y %b %d"),IF(u.inviteRequest=3,DATE_FORMAT(u.date_approve, "%Y %b %d"),DATE_FORMAT(u.date_invited, "%Y %b %d"))))', 'inviteRequest', 'inviteRequest', 'id', 'DATE_FORMAT(u.date_pending, "%Y %b %d")', 'DATE_FORMAT(u.date_reject, "%Y %b %d")', 'DATE_FORMAT(u.date_approve, "%Y %b %d")', 'DATE_FORMAT(u.date_invited, "%Y %b %d")', 'url');
     $likeColumns = array(1 => 'u.name', 2 => 'u.company', 3 => 'u.email');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "u.id";
	
	/* DB table to use */
	$sTable = "users AS u";
	
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
	
	$sGroup = "GROUP BY u.id ";
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
            
            $sWhere = "WHERE u.active = 1 AND u.inviteRequest IS NOT NULL AND u.inviteRequest != 0 ";
            
        } else {
            
            $sWhere .= " AND u.active = 1 AND u.inviteRequest IS NOT NULL AND u.inviteRequest != 0 ";
        }
        
        if($sWhere == ""){
            
        switch($_GET['filter']){

                case 'new':      $sWhere = ' WHERE u.inviteRequest = 1 '; break;
                case 'reject':   $sWhere = ' WHERE u.inviteRequest = 2 '; break;
                case 'approved': $sWhere = ' WHERE u.inviteRequest = 3 '; break;
                case 'invited':  $sWhere = ' WHERE u.inviteRequest = 4 '; break;
                case 'expired':  $sWhere = ' WHERE u.inviteRequest = 5 '; break;
                default:break;

            }
          
        } else {
            
            switch($_GET['filter']){

                case 'new':      $sWhere .= ' AND u.inviteRequest = 1 '; break;
                case 'reject':   $sWhere .= ' AND u.inviteRequest = 2 '; break;
                case 'approved': $sWhere .= ' AND u.inviteRequest = 3 '; break;
                case 'invited':  $sWhere .= ' AND u.inviteRequest = 4 '; break;
                case 'expired':  $sWhere .= ' AND u.inviteRequest = 5 '; break;
                default:break;

            }
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
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		"; 
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
	"; 
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
                "sql"=>$tmp,
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
