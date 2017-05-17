<?php
class Administrator_PaymentsController extends Zend_Controller_Action
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }

    public function indexAction()
    {
        $this->_layout->nav = 'payDue';

        $tableContact = new Application_Model_DbTable_User_NewContact();
        $tablePayments = new Application_Model_DbTable_User_NewPaymentInfo();
        $this->view->paymentPending = $tablePayments->getNum();
        $this->view->contact = $tableContact->getNum();
    }

    public function betaAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin_v2');

        $this->_layout->nav = 'payDueBeta';
    }

    public function ajaxGetAction()
    {
      /*UPDATE payments_due
        SET payments_due.paymentMinimum = 50
        WHERE payments_due.paymentMinimum = 0
        AND DATE_FORMAT(payments_due.date, '%Y-%m') >= '2013-07'
        AND payments_due.PubID IN (SELECT users.id FROM users WHERE users.paymentBy IS NOT NULL)*/

        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        /* MySQL connection */
        $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

        $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

        mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );


        $aColumns = array( 'p.PubID', 'p.PubID', 'u.name', 'u.email', 'p.revenue', 'p.paymentProf', 'u.paymentBy', 'p.paymentMinimum', 'p.carried', 'p.total', 'u.payment_approved', 'p.PubID', 'p.note', "GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ':', s.SiteName ) AS char ) SEPARATOR ', ' )", 'p.paid', 'p.PubID', 'p.date', 'p.paymentProf');
         $Columns = array( 'PubID', 'PubID', 'name', 'email', 'revenue', 'paymentProf', 'paymentBy', 'paymentMinimum', 'carried', 'total', 'payment_approved', 'PubID', 'note', "GROUP_CONCAT( DISTINCT  cast(concat( s.SiteID, ':', s.SiteName ) AS char ) SEPARATOR ', ' )", 'paid', 'PubID', 'date', 'paymentProf');
        $likeColumns = array( 0 => 'p.PubID', 1=>'p.PubID', 2=>'u.name', 3=>'u.email');

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "p.PubID";

        /* DB table to use */
        $sTable = "payments_due AS p";
        $sJoin = " LEFT JOIN users AS u ON u.id = p.PubID LEFT JOIN sites AS s ON s.PubID=u.id ";
        $sGroup = " GROUP BY u.id";
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
            switch($_GET['filter']){
                case 'paid':                 $sWhere = ' WHERE p.paid=1 '; break;
                case 'unpaid':               $sWhere = ' WHERE p.paid IS NULL '; break;
                case 'minimum':              $sWhere = ' WHERE p.revenue<p.paymentMinimum '; break;
                case 'unpaid_minimum':       $sWhere = ' WHERE p.revenue>p.paymentMinimum AND p.paid IS NULL '; break;
            }
        } else {
            switch($_GET['filter']){
                case 'paid':                 $sWhere.= ' AND p.paid=1 '; break;
                case 'unpaid':               $sWhere.= ' AND p.paid IS NULL '; break;
                case 'minimum':              $sWhere.= ' AND p.revenue<p.paymentMinimum '; break;
                case 'unpaid_minimum':       $sWhere.= ' AND p.revenue>p.paymentMinimum AND p.paid IS NULL '; break;
            }
        }

        $year = $_GET['filterYear'];
        $month = $_GET['filterMonth'];

        if($sWhere=="")
            $sWhere = " WHERE u.active = 1 AND DATE_FORMAT(p.date, '%Y-%c')='$year-$month' ";
        else
            $sWhere.= " AND u.active = 1 AND DATE_FORMAT(p.date, '%Y-%c')='$year-$month' ";

        if($sWhere == ""){
            switch($_GET['filterPaymentType']){
                case '1':       $sWhere = ' WHERE u.paymentBy=1 '; break;
                case '2':       $sWhere = ' WHERE u.paymentBy=2 '; break;
                case '3':       $sWhere = ' WHERE u.paymentBy=3 '; break;
                case '4':       $sWhere = ' WHERE u.paymentBy=4 '; break;
            }
        } else {
            switch($_GET['filterPaymentType']){
                case '1':       $sWhere.= ' AND u.paymentBy=1 '; break;
                case '2':       $sWhere.= ' AND u.paymentBy=2 '; break;
                case '3':       $sWhere.= ' AND u.paymentBy=3 '; break;
                case '4':       $sWhere.= ' AND u.paymentBy=4 '; break;
            }
        }

        if($sWhere == ""){ if($_GET['filterDue'] == 0) $sWhere = ' WHERE p.total > 0 '; }
                    else { if($_GET['filterDue'] == 0) $sWhere.= ' AND p.total > 0 '; }

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
        $tmp = $sQuery;
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

    public function ajaxGetBetaAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        if($this->getRequest()->isPost()){
            $userReports = new Application_Model_DbTable_UsersReport();

            $columns = [];
            $orders = [];
            $where = null;

            $start = (int)$this->getRequest()->getPost('start');
            $lenght = (int)$this->getRequest()->getPost('length');
            $search = $this->getRequest()->getPost('search')['value'];

            $year = (int)$this->getRequest()->getPost('year');
            $month = (int)$this->getRequest()->getPost('month');
            $width_zero = $this->getRequest()->getPost('width_zero');
            $pay_type = (int)$this->getRequest()->getPost('pay_type');
            $status = (int)$this->getRequest()->getPost('status');

            $date = new DateTime();
            $date->setDate($year, $month, 1);
            $startDate = $date->format('Y-m-d');
            $endDate = $date->modify('last day of')->format('Y-m-d');

            $checkquery = $userReports->select()
                ->from('users_report AS ur', ['pub_id'=>'ur.pub_id'])
                ->join('users AS u', 'u.id=ur.pub_id', [])
                ->joinLeft('pdf_entity AS pdf', 'pdf.PubID=ur.pub_id', [])
                ->joinLeft('payment AS py', 'py.id = u.paymentAmout', [])
                ->where('ur.query_date >= ?', $startDate)
                ->where('ur.query_date <= ?', $endDate)
                //->orWhere('ur.query_date <= ? AND ur.status=0', $startDate)
                //->where("(ur.query_date >= '".$startDate."' AND ur.query_date <= '".$endDate."') OR (ur.query_date <= '".$startDate."' AND ur.status=0)")
                ->group([
                    'ur.pub_id',
                    'ur.status'
                ])
                ->setIntegrityCheck(false);

            $dataquery = $userReports->select()
                ->from('users_report AS ur', [
                    'pub_id'=>'ur.pub_id',
                    'site_id'=>'ur.site_id',
                    'email'=>'u.email',
                    'name'=>'u.name',
                    'sum_revenue'=>'SUM(ur.revenue_paid)',
                    'profile'=>'pdf.PubID',
                    'paymentBy'=>'u.paymentBy',
                    'minimum'=>'py.name',
                    'sum'=>"IFNULL((SELECT SUM(revenue_paid) FROM users_report
                                WHERE pub_id=ur.pub_id AND query_date<'".$startDate."' AND (status=0 OR date_paid=ur.date_paid)),0)",
                    'total'=>'(SELECT(`sum`)+SUM(ur.revenue_paid))',
                    'country'=>'u.country',
                    'status'=>'ur.status'
                ])
                ->join('users AS u', 'u.id=ur.pub_id', [])
                ->joinLeft('pdf_entity AS pdf', 'pdf.PubID=ur.pub_id', [])
                ->joinLeft('payment AS py', 'py.id = u.paymentAmout', [])
                ->where('ur.query_date >= ?', $startDate)
                ->where('ur.query_date <= ?', $endDate)
                //->orWhere('ur.query_date <= ? AND ur.status=0', $startDate)
                //->where("(ur.query_date >= '".$startDate."' AND ur.query_date <= '".$endDate."') OR (ur.query_date <= '".$startDate."' AND ur.status=0)")
                ->group([
                    'ur.pub_id',
                    'ur.status'
                ])
                ->setIntegrityCheck(false);


            if($search){
                foreach($this->getRequest()->getPost('columns') as $column){
                    if($column['searchable']=='true'){
                        $where .= $column['name'] . " LIKE'%$search%' OR ";
                    }
                }
                $where = substr_replace( $where, "", -3 );
                $dataquery->where($where);
                $checkquery->where($where);
            }

            if($this->getRequest()->getPost('order')){
                foreach($this->getRequest()->getPost('order') as $order){
                    if($this->getRequest()->getPost('columns')[$order['column']]['name'])
                        $orders[] = $this->getRequest()->getPost('columns')[$order['column']]['name']." ".$order['dir'];
                }
                $dataquery->order($orders);
            }

            if($width_zero=="true"){
                $dataquery->having('SUM(ur.revenue_paid) = 0');
                $checkquery->having('SUM(ur.revenue_paid) = 0');
            }

            if($pay_type>0){
                $dataquery->where('u.paymentBy = ?', $pay_type);
                $checkquery->where('u.paymentBy = ?', $pay_type);
            }

            $dataquery->limit($lenght, $start);

            $datarequest = $userReports->fetchAll($dataquery);
            $checkrequest = $userReports->fetchAll($checkquery);

            $result = [
                "draw"=>(int)$this->getRequest()->getPost('draw'),
                "recordsTotal"=>$checkrequest->count(),
                "recordsFiltered"=>$checkrequest->count(),
                "test"=>$dataquery->__toString(),
                "data"=>$datarequest->toArray()
            ];
        }else{
            $result = [
                "draw"=>0,
                "recordsTotal"=>0,
                "recordsFiltered"=>0,
                "data"=>[],
                "error"=>"No post query"
            ];

        }
        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }

    public function paidBetaAction(){
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('modal_v2');

        $form = new Application_Form_PaymentPaid();

        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $userReports = new Application_Model_DbTable_UsersReport();

                $date = new DateTime();
                $date->setDate($form->getValue('year'), $form->getValue('month'), 1);
                $endDate = $date->modify('last day of')->format('Y-m-d');

                $datePaid = date("Y-m-d H:i:s");

                $userReports->update([
                    'status'=>1,
                    'coment_paid'=>$form->getValue('comment'),
                    'date_paid'=>$datePaid
                ], [
                    $userReports->getAdapter()->quoteInto('pub_id = ?', $form->getValue('pub_id')),
                    $userReports->getAdapter()->quoteInto('query_date <= ?', $endDate),
                    $userReports->getAdapter()->quoteInto('status <> ?', 1)
                ]);

            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }else{
            $this->view->formValues = [
                'year'=>$this->getRequest()->getParam('year'),
                'month'=>$this->getRequest()->getParam('month'),
                'pub_id'=>$this->getRequest()->getParam('pub_id')
            ];
        }

    }

    public function setcomentAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();

    	$data = array();

    	if($this->getRequest()->getPost('date') && $this->getRequest()->getPost('id') && $this->getRequest()->getPost('comment')){
            $form = new Application_Form_PaymentComment();
            if($this->getRequest()->isPost()){
                if($form->isValid($this->getRequest()->getPost())){
                    $tablePayments = new Application_Model_DbTable_Payments();
                    $tablePayments->update(array('paid'=>1, 'comment'=>$form->getValue('comment')), "PubID='".$form->getValue('id')."' AND date='".$form->getValue('date')."'");
                    $data = array('status'=>'OK', 'PubID'=>$this->getRequest()->getPost('id'), 'date'=>$this->getRequest()->getPost('date'));
                }else{
                    $data = array('error'=>'ERROR!!! All fields are required.');
                }
            }
    	}else{
    		$data = array('error'=>'ERROR!!! Please try again.');
    	}

    	$json = Zend_Json::encode($data);
    	echo $json;
    }

    public function viewcommentAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();

    	$tablePayments = new Application_Model_DbTable_Payments();
    	$this->view->comments = $tablePayments->fetchRow("PubID='".$this->_getParam('PubID')."' AND date='".$this->_getParam('date')."'");
    }

    public function getcomentAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();

    	$data = array();
		
    	if($this->getRequest()->getPost('date') && $this->getRequest()->getPost('id')){
        	$tablePayments = new Application_Model_DbTable_Payments();
        	$dataComent = $tablePayments->fetchRow("PubID='".$this->_getParam('id')."' AND date='".$this->_getParam('date')."'");
        	$data = array('status'=>'OK', 'PubID'=>$this->getRequest()->getPost('id'), 'date'=>$this->getRequest()->getPost('date'), 'comment'=>$dataComent->comment);
    	}else{
    		$data = array('error'=>'ERROR!!! Please try again.');
    	}

    	$json = Zend_Json::encode($data);
    	echo $json;		
    }

    public function revertAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();

    	$data = array();

    	if($this->getRequest()->getPost('date') && $this->getRequest()->getPost('id') && $this->getRequest()->getPost('comment')){
            $form = new Application_Form_PaymentComment();
            if($this->getRequest()->isPost()){
                if($form->isValid($this->getRequest()->getPost())){
                    $tablePayments = new Application_Model_DbTable_Payments();
                    $tablePayments->update(array('paid'=>NULL, 'comment'=>$form->getValue('comment')), "PubID='".$form->getValue('id')."' AND date='".$form->getValue('date')."'");
                    $data = array('status'=>'OK', 'PubID'=>$this->getRequest()->getPost('id'), 'date'=>$this->getRequest()->getPost('date'));
                }else{
                    $data = array('error'=>'ERROR!!! All fields are required.');
                }
            }
    	}else{
    		$data = array('error'=>'ERROR!!! Please try again.');
    	}

    	$json = Zend_Json::encode($data);
    	echo $json;
    }

    /*
     *Confirmation changed pay information
     * */
    public function pendingAction()
    {
        $this->_layout->nav = 'payDue';
    }

    public function pendingAjaxAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $pending_model = new Application_Model_DbTable_User_NewPaymentInfo();
        $output = $pending_model->getNotApprovedPayInfos();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }

    public function pendingViewAction()
    {
        $this->_layout->nav = 'payDue';
        $pending_model = new Application_Model_DbTable_User_NewPaymentInfo();
        $this->view->data = $pending_model->getData((int)$this->_getParam('id'));
    }

    public function pendingApproveAction()
    {
        $this->_layout->nav = 'payDue';
        $pending_model = new Application_Model_DbTable_User_NewPaymentInfo();
        $pending_info = $pending_model->getData($this->_getParam('id'),false);
        if($pending_info)
        {
            $user_model = new Application_Model_DbTable_Users();
            $payment_changes = new Application_Model_DbTable_User_NewPaymentInfoChanges();
            if (getenv(HTTP_X_FORWARDED_FOR)) {
            	$ipaddress = getenv(HTTP_X_FORWARDED_FOR);
            } else {
            	$ipaddress = getenv(REMOTE_ADDR);
            }
            $payment_changes->insert(
            		array(
            				'PubID' => $this->_getParam('id'),
            				'changed' => date('Y-m-d H:i:s',time()),
            				'ip' => $ipaddress,
            		));
            $pending_info['new']['payment_approved'] = date('Y-m-d H:i:s',time());
            unset($pending_info['new']['PubID']);
            unset($pending_info['new']['created']);
            $user_model->update($pending_info['new'], 'id = ' . $this->_getParam('id'));
            $db = Zend_Db_Table::getDefaultAdapter();
            $where = $db->quoteInto('PubID = ?',intval($this->_getParam('id')));
            $db->delete('user_new_payment_info',$where);
        }
        $this->_redirect('/administrator/payments/pending');
    }

    public function pendingDeleteAction()
    {
        $this->_layout->nav = 'payDue';
        $db = Zend_Db_Table::getDefaultAdapter();
        $where = $db->quoteInto('PubID = ?',intval($this->_getParam('id')));
        $db->delete('user_new_payment_info',$where);
        $this->_redirect('/administrator/payments/pending');
    }
    
    public function levelAction()
    {
        $this->_layout->nav = 'payDue';   
        
        $tableLevel = new Application_Model_DbTable_Payment_Level();
        
        $this->view->data = $tableLevel->getAllData();
        
        $this->view->min = $tableLevel->_min;
        $this->view->max = $tableLevel->_max;
    }

    public function levelAddAction()
    {
        $tableLevel = new Application_Model_DbTable_Payment_Level();
        
        $form = new Application_Form_Payment_Level();
        
        if($this->getRequest()->isPost()){        	        	            
            if($form->isValid($this->getRequest()->getPost())){ 
                
                $dataForm['min'] = $form->getValue('min');
                $dataForm['max'] = $form->getValue('max');
                
                $dataForm['min'] = $dataForm['min'] == '-' ? $tableLevel->_min : number_format($dataForm['min'], 3, '.', '');
                $dataForm['max'] = $dataForm['max'] == '+' ? $tableLevel->_max : number_format($dataForm['max'], 3, '.', '');
                
                $data = array('name' => $form->getValue('name'),
                              'min'  => $dataForm['min'],
                              'max'  => $dataForm['max'],
                              'position' => $form->getValue('position'));
                
                $tableLevel->insert($data);
                
                $this->_redirect('/administrator/payments/level');
                
            } else {
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();    
            }
        }
        
        $this->view->form = $form; 
    }
    
    public function levelEditAction()
    {
        $id = (int) $this->_getParam('id');
        
        $tableLevel = new Application_Model_DbTable_Payment_Level();
        
        $form = new Application_Form_Payment_Level();
        
        $dataLevel = $tableLevel->getDataByID($id);
        
        if($this->getRequest()->isPost()){        	        	            
            if($form->isValid($this->getRequest()->getPost())){ 
             
                  $dataForm['min'] = $form->getValue('min');
                  $dataForm['max'] = $form->getValue('max');

                  $dataForm['min'] = $dataForm['min'] == '-' ? $tableLevel->_min : number_format($dataForm['min'], 3, '.', '');
                  $dataForm['max'] = $dataForm['max'] == '+' ? $tableLevel->_max : number_format($dataForm['max'], 3, '.', '');
                
                  $data = array('name' => $form->getValue('name'),
                                'min'  => $dataForm['min'],
                                'max'  => $dataForm['max'],
                                'position' => $form->getValue('position'));
                  
                  $tableLevel->update($data, array('id = ?' => $id));
                
                  $this->_redirect('/administrator/payments/level');
                  
            } else {
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        } else {
            $this->view->formValues = array('name' => $dataLevel['name'],
                                            'min'  => $dataLevel['min'] == $tableLevel->_min ? '-' : $dataLevel['min'],
                                            'max'  => $dataLevel['max'] == $tableLevel->_max ? '+' : $dataLevel['max'],
                                            'position' => $dataLevel['position']);
        }
        
        $this->view->form = $form;
   
    }
   
    public function levelDeleteAction()
    {
        $id = (int) $this->_getParam('id');
        
        $tableLevel = new Application_Model_DbTable_Payment_Level();
        $tableLevel->delete(array('id = ?' => $id));
        
        $this->_redirect('/administrator/payments/level');
    }
    
    public function generateReportAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();

    	$data = array();
    	$val = (int)$this->getRequest()->getPost('val');
		
    	if($val){
	        $tableGenerateDue = new Application_Model_DbTable_GenerateDue();

			switch ($val){
				case 1:
					$tableGenerateDue->insert(array(
						'date'=>date("Y-m-d H:i:s"),
						'year'=>date("Y"),
						'month'=>date("n"),
						'status'=>1
					));
					break;
				case 2:
					$tableGenerateDue->insert(array(
						'date'=>date("Y-m-d H:i:s"),
						'year'=>date("Y", mktime(0,0,0,date("n")-1,date("j"),date("Y"))),
						'month'=>date("n", mktime(0,0,0,date("n")-1,date("j"),date("Y"))),
						'status'=>1
					));

					$tableGenerateDue->insert(array(
						'date'=>date("Y-m-d H:i:s"),
						'year'=>date("Y"),
						'month'=>date("n"),
						'status'=>1
					));
					
					break;
				case 3:
					$tableGenerateDue->insert(array(
						'date'=>date("Y-m-d H:i:s"),
						'year'=>date("Y", mktime(0,0,0,date("n")-2,date("j"),date("Y"))),
						'month'=>date("n", mktime(0,0,0,date("n")-2,date("j"),date("Y"))),
						'status'=>1
					));

					$tableGenerateDue->insert(array(
						'date'=>date("Y-m-d H:i:s"),
						'year'=>date("Y", mktime(0,0,0,date("n")-1,date("j"),date("Y"))),
						'month'=>date("n", mktime(0,0,0,date("n")-1,date("j"),date("Y"))),
						'status'=>1
					));

					$tableGenerateDue->insert(array(
						'date'=>date("Y-m-d H:i:s"),
						'year'=>date("Y"),
						'month'=>date("n"),
						'status'=>1
					));
					
					break;					
			}
			
			$data['status'] = 'OK';
    	}else{
    		$data = array('error'=>'ERROR!!! Please try again.');
    	}
					
		$this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);
    }
}