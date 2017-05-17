<?php


class Administrator_SitesController extends Zend_Controller_Action
{
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
    }

    public function indexAction()
    {

    }

    public function addAjaxAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'addSites';

        $usersModel = new Application_Model_DbTable_Users();
        $tableSize = new Application_Model_DbTable_Sizes();
        $tableCategory = new Application_Model_DbTable_Category();
        
        $this->view->size = $tableSize->getAllSize('pop');
        $this->view->category = $tableCategory->getAllData();

        $this->view->type = array(
            array('key'=>'1', 'value'=>'Website'),
            array('key'=>'2', 'value'=>'Tumblr'),
            array('key'=>'3', 'value'=>'Application'),
        );

        $this->view->rubicon_type = array(
        		array('key'=>'1', 'value'=>'MAM-Rubicon (Profile Customization)'),
        		array('key'=>'2', 'value'=>'MAM-Rubicon (Comics)'),
        		array('key'=>'3', 'value'=>'Exclude Rubicon')
        );

        $id = (int) $this->_getParam('id');

        if($id){
            $userInfo = $usersModel->getUserById($id);
            
			$url = str_replace("http://", "", $userInfo->url);
			$url = str_replace("https://", "", $url);
			$url = str_replace("www.", "", $url);
			$url = ucfirst($url);
			$url = parse_url('http://'.$url, PHP_URL_HOST);
            
            if($this->_getParam('url')){
                    $url = urldecode($this->_getParam('url'));
            }
            $this->view->formValues = array(
                            'user'=>$id,
                            'testUser'=>'(PubID: '.$id.') '.$userInfo['email'],
                            'name'=>$url,
                            'SiteURL'=>'http://'.strtolower($url),
                            'category'=>$userInfo['category'],
                            'pay_estimates'=>NULL,
                            'floor_pricing'=>NULL,
                            'auto_report_file'=>NULL,
                            'email_notlive_3day'=>NULL,
                            'desired_types'=>$userInfo['desired_types']);
        }

        $this->view->confirmUserID = $id;
        //if(APPLICATION_ENV == 'development') $this->render('add-ajax-dev');

    }

    public function getAjaxUserAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $output = array();
        $sitesModel = new Application_Model_DbTable_Sites();

        $search = $this->_getParam('term');

        $sql = $sitesModel->select()->setIntegrityCheck(false)
                            ->from(array('u' => 'users'),array(
                                'u.id AS id',
                                'u.email AS label',
                                'u.id AS value'))
                            ->where("u.id = ?", $search)
                            ->orWhere("u.email LIKE ?", '%'.$search.'%')
                            ->orWhere("u.name LIKE ?", '%'.$search.'%');

        $data = $sitesModel->fetchAll($sql);
        if($data){
            foreach ($data as $value) {
                $output[] = array(
                    'id'=>$value->id,
                    'label'=>$value->label,
                    'value'=>$value->id);
            }
        }

        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }

    public function confirmAjaxAction()
    {
        $this->view->siteID = $this->_getParam('SiteID');

        $tableSite = new Application_Model_DbTable_Sites();
        $tableUser = new Application_Model_DbTable_Users();
        $tableSize = new Application_Model_DbTable_Sizes();


        $this->view->size = $tableSize->getAllSize('pop');
        $this->view->site = $tableSite->getSiteInfoByID($this->view->siteID);
        $this->view->user = $tableUser->getUserById($this->view->site['PubID']);
        $this->view->category = $tableUser->querySelect('category');

        $this->view->type = array(
            array('key'=>'1', 'value'=>'Website'),
            array('key'=>'2', 'value'=>'Tumblr'),
            array('key'=>'3', 'value'=>'Application'),
        );

        $this->view->rubicon_type = array(
        		array('key'=>'1', 'value'=>'MAM-Rubicon (Profile Customization)'),
        		array('key'=>'2', 'value'=>'MAM-Rubicon (Comics)'),
        		array('key'=>'3', 'value'=>'Exclude Rubicon')
        );

    }

    public function addAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'addSites';

        $form = new Application_Form_NewSites();
        $usersModel = new Application_Model_DbTable_Users();
        $this->view->user = $usersModel->querySelectSites('users');
        $this->view->type = array(
            array('key'=>'1', 'value'=>'Website'),
            array('key'=>'2', 'value'=>'Tumblr'),
            array('key'=>'3', 'value'=>'Application'),
        );

        $this->view->rubicon_type = array(
        		array('key'=>'1', 'value'=>'MAM-Rubicon (Profile Customization)'),
        		array('key'=>'2', 'value'=>'MAM-Rubicon (Comics)'),
        		array('key'=>'3', 'value'=>'Exclude Rubicon')
        );

        $id = $this->_getParam('id');
        if($id){
            $form->getElement('name')->setValue($id);
        }

        $this->view->pricing = null;

        if($this->getRequest()->isPost()){

        	if($this->getRequest()->getPost('floor_price_date') || $this->getRequest()->getPost('floor_price')){
        		$priceDates = $this->getRequest()->getPost('floor_price_date');
        		$pricePrice = $this->getRequest()->getPost('floor_price');
        		$pricePercent = $this->getRequest()->getPost('floor_percent');
        		$newPricingArray = array();
        		$counter = 0;
        		foreach ($this->getRequest()->getPost('floor_price_date') as $key=>$value){
        			if(!empty($value) && !empty($pricePrice[$key]) && !empty($pricePercent[$key])){
	        			$newPricingArray[$counter]['date'] = $value;
	        			$newPricingArray[$counter]['price'] = str_replace(",", ".", $pricePrice[$key]);
	        			$newPricingArray[$counter]['percent'] = str_replace(",", ".", $pricePercent[$key]);
        			}
        			$counter++;
        		}
        		$this->view->pricing = $newPricingArray;
        	}

            $formData = $this->getRequest()->getPost();

            $domainInfo = null;

            if(isset($formData['name'])){
            	$domainInfo = $usersModel->getUserSiteByName($formData['name']);
            }

            if($form->isValid($formData) && !$domainInfo){
                $tags = new Application_Model_DbTable_Tags();

                $pay_estimates = $this->getRequest()->getPost('pay_estimates');
                $cpm = $this->getRequest()->getPost('cpm');

                if($pay_estimates==1 && !empty($cpm)){
                	$formData['cpm'] = $this->getRequest()->getPost('cpm');
                }else{
                	unset($formData['cpm']);
                }

                $siteInsertID = $tags->addSite($formData);

                if(count($newPricingArray)>0){
                	foreach($newPricingArray as $item){
                		$tags->addSitePricing($formData['user'], $siteInsertID, $item['date'], $item['price'], $item['percent']);
                	}
                }

                //$this->_setdfp($siteInsertID);

                //$this->_redirect('/administrator/tags/');
                $this->_redirect('/administrator/tags/placement/siteID/'.$siteInsertID);
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();

                if($domainInfo){
                	$this->view->formErrors['name']['dublicate'] = 'This domain already belong to publisher: ('.$domainInfo['PubID'].') ('.$domainInfo['name'].')';
                }

                $pay_estimates = $this->getRequest()->getPost('pay_estimates');
                $cpm = $this->getRequest()->getPost('cpm');
                if($pay_estimates==1 && !empty($cpm)){
                	$this->view->formValues['pay_estimates'] = $this->getRequest()->getPost('pay_estimates');
                	$this->view->formValues['cpm'] = $this->getRequest()->getPost('cpm');
                }
            }
        }else{
            $userInfo = $usersModel->getUserById($id);
            $url = str_replace("http://", "", $userInfo->url);
            $url = str_replace("www.", "", $url);
            if($this->_getParam('url')){
                $url = urldecode($this->_getParam('url'));
            }
            $this->view->formValues = array('user'=>$id,
                                            'name'=>$url,
                                            'SiteURL'=>'http://'.strtolower($url),
                                            'pay_estimates'=>NULL,
                                            'floor_pricing'=>NULL,
                                            'auto_report_file'=>NULL,
                                            'email_notlive_3day'=>NULL);
        }
    }

    public function approvedAction()
    {
    	/*
    	$layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'approveSites';

        $auth = Zend_Auth::getInstance()->getIdentity();
        $this->view->PubID = $this->_getParam('id');

        $tableSize = new Application_Model_DbTable_Sizes();
        $tableCategory = new Application_Model_DbTable_Category();

        $this->view->size = $tableSize->getAllSize();
        $this->view->category = $tableCategory->getAllData();

        $tableManager = new Application_Model_DbTable_ContactNotification();

        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;
        */
        
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'approveSites'; 
               
        $auth = Zend_Auth::getInstance()->getIdentity();
        $this->view->PubID = $this->_getParam('id'); 
        
        $date1 = $this->_getParam('date1');
        $date2 = $this->_getParam('date2');
        $this->view->compare = false;
        if(strlen($date1) AND strlen($date2))
        {
        	$this->view->compare = true;
        	$this->view->date1 = $date1;
        	$this->view->date2 = $date2;
        }
        $tableSize = new Application_Model_DbTable_Sizes();
        $tableManager = new Application_Model_DbTable_ContactNotification();
        $tableColumn = new Application_Model_DbTable_TableColumn_Site();

        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");
		$category_model = new Application_Model_DbTable_Category();
		$this->view->category = $category_model->getAllData();
        $this->view->dataAuth = $auth;
        $this->view->manager = $dataManager;
        $this->view->size = $tableSize->getAllSize();
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->column = $tableColumn->getDataByUser($auth->email);
        $this->view->filter = $this->_getParam('filter');                 
    }
	
    public function approvedColumnAction()
    {
        $this->_helper->layout()->disableLayout(); 
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $tableColumn = new Application_Model_DbTable_TableColumn_Site();
        
        $dataColumn = $tableColumn->getAllDataByUser($dataAuth->email);
        
        $form = new Application_Form_TableColumn_Site($dataColumn, $dataAuth, $tableColumn);
        $form->showOldData();
                               
        if($this->getRequest()->isPost()){
            if($form->isValid($this->_getAllParams())){
        
                $form->saveData();
                
                $this->view->message = '<h3>Data has been saved! Please close popup window for refresh Approved Websites page.</h3>';                
            }
        }
        
        $this->view->form = $form;
    }
        
    public function ajaxApprovedAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
                
        $filterDate1 = $_GET['filterDate1'];
        $filterDate2 = $_GET['filterDate2'];  

        if($_GET['filter'] == 'never_live_3' OR $_GET['filter'] == 'never_live_10')
        	$filterPrevNotif = $_GET['filterPrevNotif'];
        else 
        	$filterPrevNotif = 0;
        	
        $filterSize = isset($_GET['filterSize']) ? (int) $_GET['filterSize'] : 0;
        $filterValue = isset($_GET['filterValue']) ? $_GET['filterValue'] : 'paid';
        
        switch($filterValue){
            case 'total': $fieldImp = 'impressions_total'; break;
            case 'paid' : $fieldImp = 'impressions'; break;
        }
        
        
//      if( $filterSize > 0 || 
//          strtotime($filterDate) != strtotime(date('Y-m-d', strtotime('-2 day'))) ||
//          strtotime($filterDate2) != strtotime(date('Y-m-d', strtotime('-1 day'))) ){    
          

                              /*0            1          2            3                   4                             5                                          6                                                                7                                                       8                                 9                              10                          11                                   12                                                     13                                              14              15                 16                    17              18         19            20          21           22            23              24                 25             26                  27                             28                 29              30*/ 
         $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 'IFNULL(st1.'.$fieldImp.', 0)', 'IFNULL(st2.'.$fieldImp.', 0)', 'IFNULL(ROUND(st2.'.$fieldImp.' - st1.'.$fieldImp.'), 0)', 'IFNULL(ROUND((st2.'.$fieldImp.' - st1.'.$fieldImp.')/st1.'.$fieldImp.' * 100), 0)', 'IFNULL(st2.deni_impres, 0)', 'IFNULL(st2.impressions_avg_7day, 0)', 'IFNULL(st1.revenue, 0)', 'IFNULL(st2.revenue, 0)', 'IFNULL(ROUND(st2.revenue - st1.revenue, 2), 0)', 'IFNULL(ROUND((st2.revenue - st1.revenue)/st1.revenue * 100, 2), 0)', 's.alexaRank', 's.alexaRankUS', 'IFNULL(s.live, 0)', 'IFNULL(s.lived, 0)', 's.first_live', 't.created_at', 'GROUP_CONCAT( DISTINCT  cast( netw.short_name AS char ) SEPARATOR ", " )', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 'o.Value', 's.mail_ever_live', 'u.inviteRequest', 's.PubID', 'IFNULL(st1.'.$fieldImp.', 0)', 'IFNULL(st2.'.$fieldImp.', 0)','s.PubID', 's.alexa_country' );
          $Columns = array( 'PubID',    'SiteID',   'SiteName',   'email',  'IFNULL(st1.'.$fieldImp.', 0)', 'IFNULL(st2.'.$fieldImp.', 0)', 'IFNULL(ROUND(st2.'.$fieldImp.' - st1.'.$fieldImp.'), 0)', 'IFNULL(ROUND((st2.'.$fieldImp.' - st1.'.$fieldImp.')/st1.'.$fieldImp.' * 100), 0)', 'IFNULL(st2.deni_impres, 0)', 'IFNULL(st2.impressions_avg_7day, 0)', 'IFNULL(st1.revenue, 0)', 'IFNULL(st2.revenue, 0)', 'IFNULL(ROUND(st2.revenue - st1.revenue, 2), 0)', 'IFNULL(ROUND((st2.revenue - st1.revenue)/st1.revenue * 100, 2), 0)',  'alexaRank',   'alexaRankUS',  'IFNULL(s.live, 0)', 'IFNULL(s.lived, 0)', 'first_live', 'created_at', 'GROUP_CONCAT( DISTINCT  cast( netw.short_name AS char ) SEPARATOR ", " )', 'privacy', 'live_name',   'id',       'tag_id',     'Value',   'mail_ever_live',   'inviteRequest',    'PubID', 'IFNULL(st1.'.$fieldImp.', 0)', 'IFNULL(st2.'.$fieldImp.', 0)', 'PubID',   'alexa_country' );
         $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email' );

         
            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "s.SiteID";

            /* DB table to use */
            $sTable = "sites AS s";        
            
            $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID
                       LEFT JOIN sites_tags AS t ON t.site_id = s.SiteID
                       LEFT JOIN optimization_values AS o ON o.SiteID = s.SiteID  
                       LEFT JOIN sites_live AS sl ON sl.SiteID = s.SiteID AND sl.date = '".$filterDate2."'
                       LEFT JOIN site_base_statistic AS st1 ON st1.SiteID = s.SiteID AND st1.query_date = '".$filterDate1."' AND st1.AdSize = '".$filterSize."'
                       LEFT JOIN site_base_statistic AS st2 ON st2.SiteID = s.SiteID AND st2.query_date = '".$filterDate2."' AND st2.AdSize = '".$filterSize."' 
                       LEFT JOIN networks AS netw ON t.network_id=netw.id ";
            
            if($_GET['filter']=='live_no_passback'){
	            $sJoin.=" LEFT JOIN minimum_cpm AS mc ON s.SiteID=mc.SiteID ";
            }            

            if($_GET['siteType']==8 AND $_GET['publisherId']!=0){
	            $sJoin.=" LEFT JOIN pubmatic_sites AS ps ON ps.SiteID=s.SiteID ";
            }            
            
            $sGroup = " GROUP BY s.SiteID ";

//        } else {            
//                                 /*0           1            2           3                  4                        5                                                           6                                                                  7                          8                       9                            10                       11              12           13        14         15          16            17         18            19            20             21                  22              23                   24                                 25*/
//            $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.impressions_1day_ago', 's.impressions_2day_ago', 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 's.deni_impres_yesterday', 's.impressions_avg_7day', 'IFNULL(s.revenue, 0)',  'IFNULL(s.revenue_1day_ago, 0)',  's.alexaRank', 's.alexaRankUS', 's.live', 's.lived', 't.type', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 'o.Value', 's.mail_ever_live', 'u.inviteRequest', 's.PubID', 'IFNULL(s.paid_impressions, 0)', 'IFNULL(s.impressions_1day_ago, 0)' );
//             $Columns = array( 'PubID',    'SiteID',    'SiteName',  'email',   'impressions_1day_ago',   'impressions_2day_ago',   'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 'deni_impres_yesterday',   'impressions_avg_7day',  'IFNULL(s.revenue, 0)',  'IFNULL(s.revenue_1day_ago, 0)',  'alexaRank',    'alexaRankUS',  'live',    'lived',  'type',    'privacy',    'live_name',   'id',      'tag_id',      'Value',    'mail_ever_live',  'inviteRequest',   'PubID',  'IFNULL(s.paid_impressions, 0)', 'IFNULL(s.impressions_1day_ago, 0)' );
//         $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.impressions_1day_ago' , 5 => 's.impressions_2day_ago', 8 => 'impressions_avg_7day', 9 => 's.revenue', 10 => 's.alexaRank', 14 => 't.type', 19 => 'o.Value');
//
//            /* Indexed column (used for fast and accurate table cardinality) */
//            $sIndexColumn = "s.SiteID";
//
//            /* DB table to use */
//            $sTable = "sites AS s";
//            $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID
//            	       LEFT JOIN optimization_values AS o ON o.SiteID = s.SiteID
//                       LEFT JOIN tags AS t ON t.site_id = s.SiteID ";
//
//            $sGroup = " GROUP BY s.SiteID ";
//
//        }
	
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
	               
        
        if($_GET['PubID']){
        
            if($sWhere == "") $sWhere = " WHERE s.PubID = '".$_GET['PubID']."' ";  
            else              $sWhere .= " AND s.PubID = '".$_GET['PubID']."' "; 
        
        }
        
        if($sWhere == ""){
            
            switch($_GET['filter']){

                case 'live':       $sWhere = ' WHERE s.live = 1 ';                             break;
                case 'no_longer':  $sWhere = ' WHERE s.lived IS NOT NULL AND s.live IS NULL '; break;
                case 'never_live'   : $sWhere = ' WHERE s.lived IS NULL ';                     break;
                case 'store_tag_url': $sWhere = ' WHERE s.store_tag_url IS NOT NULL ';         break;
                case 'never_live_3' : $sWhere = ' WHERE s.lived IS NULL AND t.created < "'.date('Y-m-d', strtotime('-3 day')).'" '; break;
                case 'live_no_passback': $sWhere = ' WHERE s.live = 1 AND mc.id IS NULL ';     break;                
                case 'never_live_10' : $sWhere = ' WHERE s.lived IS NULL AND t.created < "'.date('Y-m-d', strtotime('-10 day')).'" '; break;
                default:break;

            }
          
        } else {
            
            switch($_GET['filter']){

                case 'live':       $sWhere .= ' AND s.live = 1 ';                             break;
                case 'no_longer':  $sWhere .= ' AND s.lived IS NOT NULL AND s.live IS NULL '; break;
                case 'never_live'   : $sWhere .= ' AND s.lived IS NULL ';                     break;
                case 'store_tag_url': $sWhere .= ' AND s.store_tag_url IS NOT NULL ';         break;
                case 'never_live_3' : $sWhere .= ' AND s.lived IS NULL AND t.created < "'.date('Y-m-d', strtotime('-3 day')).'" '; break;
                case 'live_no_passback': $sWhere .= ' AND s.live = 1 AND mc.id IS NULL ';    break;                                
                case 'never_live_10' : $sWhere .= ' AND s.lived IS NULL AND t.created < "'.date('Y-m-d', strtotime('-10 day')).'" '; break;
                default:break;

            }
        }

        if((int)$_GET['siteType']){
            if((int)$_GET['siteType']==1){
                if($sWhere == "")
                    $sWhere = ' WHERE t.network_id IS NULL ';
                else
                    $sWhere .= ' AND t.network_id IS NULL ';
            }else{
                if($sWhere == "")
                    $sWhere = ' WHERE t.network_id = '.(int)$_GET['siteType'].' ';
                else
                    $sWhere .= ' AND t.network_id = '.(int)$_GET['siteType'].' ';
            }
        }else{
            if($sWhere == "")
                $sWhere = ' WHERE t.network_id IS NOT NULL ';
            else
                $sWhere .= ' AND t.network_id IS NOT NULL ';
        }


           $account = (int)$_GET['accounts'];
        if($account > 0){
            if($sWhere == "")
                $sWhere = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere.= " AND u.account_manager_id='".$account."' ";
        }
        
        /*
           $filterAdxApprove = (int)$_GET['filterAdxApprove'];
        if($filterAdxApprove > 0){
            if($sWhere == "") $sWhere = " WHERE u.inviteRequest = 3 ";
                         else $sWhere.= " AND u.inviteRequest = 3 ";
        }
        */
        
        $filterCategory = (int)$_GET['filterCategory'];
        if($filterCategory > 0){
        	if($sWhere == "") $sWhere = " WHERE s.category = ".$filterCategory;
        	else $sWhere.= " AND s.category = ". $filterCategory;
        }
        
           
        if($filterPrevNotif){
        	if($_GET['filter'] == 'never_live_3')
        	{
        		if($sWhere == "") $sWhere = " WHERE s.mail_ever_live = 0 ";
        		else $sWhere.= " AND s.mail_ever_live = 0 ";
        	}
        	else 
        	{
        		if($sWhere == "") $sWhere = " WHERE s.mail_ever_live != 4 ";
        			else $sWhere.= " AND s.mail_ever_live != 4 ";
        	}
            
        }
        
	    if($_GET['siteType']==8 AND $_GET['publisherId']!=0){
        	if($sWhere == "") $sWhere = " WHERE ps.PubmaticID = ".(int)$_GET['publisherId'];
        	else $sWhere.= " AND ps.PubmaticID = ".(int)$_GET['publisherId'];
	    }            
        
        
        if($sWhere == ""){ 
            
            $sWhere = " WHERE s.status = 3 AND (u.reject = 0 OR u.reject IS NULL) ";
            
        } else {              
            
            $sWhere .= " AND s.status = 3 AND (u.reject = 0 OR u.reject IS NULL) ";
            
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
	
    public function ajaxApprovedAction__old()
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

        if(isset($_GET['filterSize']) && $_GET['filterSize'] != 'all'){

            $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 'sis.impressions_1day', 'sis.impressions_2day', 'IFNULL(ROUND((sis.impressions_1day - sis.impressions_2day)/sis.impressions_2day * 100), 0)', 'sis.impressions_1day_denied', 's.revenue', 's.alexaRank', 's.alexaRankUS', 's.live', 's.lived', 't.type', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 'o.Value' );
             $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions_1day', 'impressions_2day', 'IFNULL(ROUND((sis.impressions_1day - sis.impressions_2day)/sis.impressions_2day * 100), 0)', 'impressions_1day_denied', 'revenue','alexaRank', 'alexaRankUS', 'live', 'lived', 'type', 'privacy', 'live_name', 'id', 'tag_id', 'Value');
         $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 'sis.impressions_1day' , 5 => 'sis.impressions_2day', 6=> 's.revenue', 7 => 's.alexaRank', 10 => 't.type', 11=> 'o.Value');


            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "s.SiteID";

            /* DB table to use */
            $sTable = "sites AS s";
            $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID
            		   LEFT JOIN optimization_values AS o ON o.SiteID = s.SiteID
                       LEFT JOIN tags AS t ON t.site_id = s.SiteID
                            JOIN stats_impressions_size AS sis ON sis.SiteID = s.SiteID AND sis.AdSize = '".(int)$_GET['filterSize']."' AND ( sis.impressions_1day > 0 OR sis.impressions_2day > 0 ) ";

            $sGroup = " GROUP BY s.SiteID ";

        } else {

            $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.impressions_1day_ago', 's.impressions_2day_ago', 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 's.deni_impres_yesterday', 's.revenue' , 's.alexaRank', 's.alexaRankUS', 's.live', 's.lived', 't.type', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 'o.Value' );
             $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions_1day_ago', 'impressions_2day_ago', 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 'deni_impres_yesterday', 'revenue', 'alexaRank', 'alexaRankUS', 'live', 'lived', 'type', 'privacy', 'live_name', 'id', 'tag_id' ,'Value');
         $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.impressions_1day_ago' , 5 => 's.impressions_2day_ago', 6 => 's.revenue', 7 => 's.alexaRank', 10 => 't.type', 11 => 'o.Value');


            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "s.SiteID";

            /* DB table to use */
            $sTable = "sites AS s";
            $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID
            		   LEFT JOIN optimization_values AS o ON o.SiteID = s.SiteID
                       LEFT JOIN tags AS t ON t.site_id = s.SiteID ";

            $sGroup = " ";

        }

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

        $account = (int)$_GET['accounts'];
        if($account>0){
            if($sWhere == "")
                $sWhere = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere.= " AND u.account_manager_id='".$account."' ";
        }

        if($sWhere == ""){
            switch($_GET['filterType']){
                case '4':       $sWhere = ' WHERE t.type = 4 '; break;
                case '5':       $sWhere = ' WHERE t.type = 5 '; break;
                case '6':       $sWhere = ' WHERE t.type = 6 '; break;
                case '7':       $sWhere = ' WHERE t.type = 7 '; break;                
                case '8':       $sWhere = ' WHERE t.type = 8 '; break;                
                case '1':       $sWhere = ' WHERE t.type IS NULL '; break;                                
                default:break;
            }
        } else {
            switch($_GET['filterType']){
                case '4':  $sWhere .= ' AND t.type = 4 '; break;
                case '5':  $sWhere .= ' AND t.type = 5 '; break;
                case '6':  $sWhere .= ' AND t.type = 6 '; break;
                case '7':  $sWhere .= ' AND t.type = 7 '; break;                
                case '8':  $sWhere .= ' AND t.type = 8 '; break;                
                case '1':  $sWhere .= ' AND t.type IS NULL '; break;                                
                default:break;
            }
        }

        if($sWhere == ""){
            if($_GET['filterCategory'] != 'all'){ $sWhere = " WHERE s.category = '".$_GET['filterCategory']."' "; }
        } else {
            if($_GET['filterCategory'] != 'all'){ $sWhere .= " AND s.category = '".$_GET['filterCategory']."' "; }
        }

        /*if($sWhere == ""){
            switch($_GET['filterSize']){
                case 6  : $sWhere = ' WHERE s.pop_unders = 1 '; break;
                case 9  : $sWhere = ' WHERE s.baner_320 = 1 '; break;
                case 12 : $sWhere = ' WHERE s.video_ads = 1 '; break;

                default:break;
            }
        } else {
            switch($_GET['filterSize']){
                case 6  : $sWhere .= ' AND s.pop_unders = 1 '; break;
                case 9  : $sWhere .= ' AND s.baner_320 = 1 '; break;
                case 12 : $sWhere .= ' AND s.video_ads = 1 '; break;

                default:break;
            }
        }*/

        if($sWhere == ""){

            $sWhere = " WHERE s.status = 3 AND (u.reject = 0 OR u.reject IS NULL) ";

        } else {

            $sWhere .= " AND s.status = 3 AND (u.reject = 0 OR u.reject IS NULL) ";

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

    public function impressStatsAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'impress';

        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();
 
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;
    }

    public function ajaxStatsAction()
    {
        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();

        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );

		
		if($_GET['impress']=='impress'){
			$fieldImpress = 'impressions';
		}elseif($_GET['impress']=='paid'){
			$fieldImpress = 'paid_impressions';
		}
		
        $aColumns1 = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 'SUM(d1.'.$fieldImpress.') as '.$fieldImpress.'D1', '0 as '.$fieldImpress.'D2', 's.alexaRank', 's.live', 's.lived', 't.network_id', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id' );
         $Columns1 = array( 'PubID', 'SiteID', 'SiteName', 'email', 'SUM(d1.'.$fieldImpress.') as '.$fieldImpress.'D1', '0 as '.$fieldImpress.'D2', 'alexaRank', 'live', 'lived', 'network_id', 'privacy', 'live_name', 'id', 'tag_id' );
     $likeColumns1 = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.alexaRank', 5 => 't.type');

        $aColumns2 = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', '0 as '.$fieldImpress.'D1', 'SUM(d2.'.$fieldImpress.') as '.$fieldImpress.'D2', 's.alexaRank', 's.live', 's.lived', 't.network_id', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id' );
         $Columns2 = array( 'PubID', 'SiteID', 'SiteName', 'email', '0 as '.$fieldImpress.'D1', 'SUM(d2.'.$fieldImpress.') as '.$fieldImpress.'D2', 'alexaRank', 'live', 'lived', 'network_id', 'privacy', 'live_name', 'id', 'tag_id' );
     $likeColumns2 = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.alexaRank', 5 => 't.type');

        $aColumnsAll = array( 'PubID', 'SiteID', 'SiteName', 'email', 'SUM('.$fieldImpress.'D1)', 'SUM('.$fieldImpress.'D2)', 'SUM('.$fieldImpress.'D1)-SUM('.$fieldImpress.'D2)', 'alexaRank', 'live', 'lived', 'network_id', 'privacy', 'live_name', 'id', 'tag_id' );
         $ColumnsAll = array( 'PubID', 'SiteID', 'SiteName', 'email', 'SUM('.$fieldImpress.'D1)', 'SUM('.$fieldImpress.'D2)', 'SUM('.$fieldImpress.'D1)-SUM('.$fieldImpress.'D2)',  'alexaRank', 'live', 'lived', 'network_id', 'privacy', 'live_name', 'id', 'tag_id' );

	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "res.SiteID";

	/* DB table to use */
	$sTable = "sites AS s";

        $sJoin1 = "JOIN users AS u ON u.id = s.PubID
                   JOIN sites_tags AS t ON t.site_id = s.SiteID
                   LEFT JOIN users_reports_final AS d1 ON d1.SiteID=s.SiteID";
        $sJoin2 = "JOIN users AS u ON u.id = s.PubID
                   JOIN sites_tags AS t ON t.site_id = s.SiteID
                   LEFT JOIN users_reports_final AS d2 ON d2.SiteID=s.SiteID";

        $sGroup1 = "GROUP BY d1.SiteID";
        $sGroup2 = "GROUP BY d2.SiteID";
        $sGroupAll = "GROUP BY res.SiteID";

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
				$sOrder .= $aColumnsAll[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
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
	$sWhere1 = "";
        $sWhere2 = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere1 = "WHERE (";
		foreach ( $likeColumns1 as $key => $field )
		{
			$sWhere1 .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere1 = substr_replace( $sWhere1, "", -3 );
		$sWhere1 .= ')';

		$sWhere2 = "WHERE (";
		foreach ( $likeColumns2 as $key => $field )
		{
			$sWhere2 .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere2 = substr_replace( $sWhere2, "", -3 );
		$sWhere2 .= ')';

	}


	/* Individual column filtering */
	foreach( $likeColumns1 as $key => $field )
	{
		if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
		{
			if ( $sWhere1 == "" )
			{
				$sWhere1 = "WHERE ";
			}
			else
			{
				$sWhere1 .= " AND ";
			}
			$sWhere1 .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
		}
	}
	foreach( $likeColumns2 as $key => $field )
	{
		if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
		{
			if ( $sWhere2 == "" )
			{
				$sWhere2 = "WHERE ";
			}
			else
			{
				$sWhere2 .= " AND ";
			}
			$sWhere2 .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
		}
	}


        if($_GET['date1']){

            if($sWhere1 == "") $sWhere1 = " WHERE d1.query_date = '".$_GET['date1']."' ";
            else              $sWhere1 .= " AND d1.query_date = '".$_GET['date1']."' ";

        }

        if($_GET['date2']){

            if($sWhere2 == "") $sWhere2 = " WHERE d2.query_date = '".$_GET['date2']."' ";
            else              $sWhere2 .= " AND d2.query_date = '".$_GET['date2']."' ";

        }

        if($sWhere1 == ""){

            switch($_GET['filter']){

                case 'live':       $sWhere1 = ' WHERE s.live = 1 ';                             break;
                case 'no_longer':  $sWhere1 = ' WHERE s.lived IS NOT NULL AND s.live IS NULL '; break;
                case 'never_live'   : $sWhere1 = ' WHERE s.lived IS NULL ';                     break;
                case 'store_tag_url': $sWhere1 = ' WHERE s.store_tag_url IS NOT NULL ';         break;
                default:break;

            }

        } else {

            switch($_GET['filter']){

                case 'live':       $sWhere1 .= ' AND s.live = 1 ';                             break;
                case 'no_longer':  $sWhere1 .= ' AND s.lived IS NOT NULL AND s.live IS NULL '; break;
                case 'never_live'   : $sWhere1 .= ' AND s.lived IS NULL ';                     break;
                case 'store_tag_url': $sWhere1 .= ' AND s.store_tag_url IS NOT NULL ';         break;
                default:break;

            }
        }

        if($sWhere2 == ""){

            switch($_GET['filter']){

                case 'live':       $sWhere2 = ' WHERE s.live = 1 ';                             break;
                case 'no_longer':  $sWhere2 = ' WHERE s.lived IS NOT NULL AND s.live IS NULL '; break;
                case 'never_live'   : $sWhere2 = ' WHERE s.lived IS NULL ';                     break;
                case 'store_tag_url': $sWhere2 = ' WHERE s.store_tag_url IS NOT NULL ';         break;
                default:break;

            }

        } else {

            switch($_GET['filter']){

                case 'live':       $sWhere2 .= ' AND s.live = 1 ';                             break;
                case 'no_longer':  $sWhere2 .= ' AND s.lived IS NOT NULL AND s.live IS NULL '; break;
                case 'never_live'   : $sWhere2 .= ' AND s.lived IS NULL ';                     break;
                case 'store_tag_url': $sWhere2 .= ' AND s.store_tag_url IS NOT NULL ';         break;
                default:break;

            }
        }
        if((int)$_GET['filterType']){
            if($sWhere1 == "")
                $sWhere1 = ' WHERE t.network_id = '.(int)$_GET['filterType'].' ';
            else
                $sWhere1 .= ' AND t.network_id = '.(int)$_GET['filterType'].' ';

            if($sWhere2 == "")
                $sWhere2 = ' WHERE t.network_id = '.(int)$_GET['filterType'].' ';
            else
                $sWhere2 .= ' AND t.network_id = '.(int)$_GET['filterType'].' ';
        }


        if($sWhere1 == ""){

            $sWhere1 = " WHERE s.status = 3 AND u.reject = 0 ";

        } else {

            $sWhere1 .= " AND s.status = 3 AND u.reject = 0 ";

        }

        if($sWhere2 == ""){

            $sWhere2 = " WHERE s.status = 3 AND u.reject = 0 ";

        } else {

            $sWhere2 .= " AND s.status = 3 AND u.reject = 0 ";

        }

        if(isset($_GET['manager'])){

            if($_GET['sorting']=='desc') $sOrder = " ORDER BY SUM(".$fieldImpress."D1)-SUM(".$fieldImpress."D2) DESC";
                                    else $sOrder = " ORDER BY SUM(".$fieldImpress."D1)-SUM(".$fieldImpress."D2) ASC";

            if($_GET['manager'] != -1){

                if($sWhere1=="") $sWhere1 = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
                            else $sWhere1 .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";

                if($sWhere2=="") $sWhere2 = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
                            else $sWhere2 .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";
            }
        }

           $account = isset($_GET['accounts']) ? (int)$_GET['accounts'] : 0;
        if($account>0){
            if($sWhere1 == "")
                $sWhere1 = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere1.= " AND u.account_manager_id='".$account."' ";

            if($sWhere2 == "")
                $sWhere2 = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere2.= " AND u.account_manager_id='".$account."' ";
        }

        if($sWhere1 == "") $sWhere1 = " WHERE t.primary = 1 ";
        else $sWhere1.= " AND t.primary = 1 ";

        if($sWhere2 == "") $sWhere2 = " WHERE t.primary = 1 ";
        else $sWhere2.= " AND t.primary = 1 ";

	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumnsAll))."
		FROM (
                    SELECT ".str_replace(" , ", " ", implode(", ", $aColumns1))."
                    FROM $sTable
                        $sJoin1
                        $sWhere1
                        $sGroup1
                    UNION ALL
                    SELECT ".str_replace(" , ", " ", implode(", ", $aColumns2))."
                    FROM $sTable
                        $sJoin2
                        $sWhere2
                        $sGroup2
                ) as res
                $sGroupAll
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
		FROM (
                    SELECT ".str_replace(" , ", " ", implode(", ", $aColumns1))."
                    FROM $sTable
                        $sJoin1
                        $sWhere1
                        $sGroup1
                    UNION ALL
                    SELECT ".str_replace(" , ", " ", implode(", ", $aColumns2))."
                    FROM $sTable
                        $sJoin2
                        $sWhere2
                        $sGroup2
                )as res
                $sGroupAll
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
		for ( $i=0 ; $i<count($ColumnsAll) ; $i++ )
		{
			if ( $ColumnsAll[$i] == "version" )
			{
				/* Special output formatting for 'version' column */
				$row[] = ($aRow[ $ColumnsAll[$i] ]=="0") ? '-' : $aRow[ $ColumnsAll[$i] ];
			}
			else if ( $ColumnsAll[$i] != ' ' )
			{
				/* General output */
				$row[] = $aRow[ $ColumnsAll[$i] ];
			}
		}
		$output['aaData'][] = $row;
	}

        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }

    /**
     * @throws Zend_Db_Table_Row_Exception
     * @throws Zend_Form_Exception
     */
    public function viewAction()
    {
        ini_set("memory_limit", "256M");
        
        $id = $this->_getParam('id');

        $form = new Application_Form_Website();

        $sitesModel = new Application_Model_DbTable_Sites();
        $tagModel = new Application_Model_DbTable_Tags();
        $websiteLogsModel = new Application_Model_DbTable_WebsiteLogs();
        $tableSize = new Application_Model_DbTable_Sizes();
        $wantPubm = new Application_Model_DbTable_Sites_WantPubmatic();

        $this->view->logs = $websiteLogsModel->GetLogs($id);

        $tmpArr = array();
        $wantPubmArr = $wantPubm->fetchAll("SiteID = '".$id."'")->toArray();
        if($wantPubmArr){
            foreach($wantPubmArr as $size){
                $tmpArr[] = $size['size'];
            }
        }
        $this->view->wantPubm = $tmpArr;

        $siteInfo = $sitesModel->getSiteInfoByID($id);
        $tagInfo = $tagModel->getTagBySiteID($id);
		$dataSize = $tableSize->getActiveSizeBySite ( $id, true );
		$dataUserSize = $tableSize->getIndividualSize ( $id, true );

        $this->view->mobileName = $siteInfo['SiteName'];
        $arr = explode(".", $this->view->mobileName);
        $domen = end($arr);
        $this->view->mobileName = str_replace(".".$domen, "-Mobile.".$domen, $this->view->mobileName);

        if($this->getRequest()->isPost()){

        	if($this->getRequest()->getPost('floor_price_date') || $this->getRequest()->getPost('floor_price')){
        		$priceDates = $this->getRequest()->getPost('floor_price_date');
        		$pricePrice = $this->getRequest()->getPost('floor_price');
        		$pricePercent = $this->getRequest()->getPost('floor_percent');
        		$newPricingArray = array();
        		$counter = 0;
        		foreach ($this->getRequest()->getPost('floor_price_date') as $key=>$value){
        			if(!empty($value) && !empty($pricePrice[$key]) && !empty($pricePercent[$key])){
        				$newPricingArray[$counter]['date'] = $value;
        				$newPricingArray[$counter]['price'] = str_replace(",", ".", $pricePrice[$key]);
        				$newPricingArray[$counter]['percent'] = str_replace(",", ".", $pricePercent[$key]);
        			}
        			$counter++;
        		}
        		$this->view->pricing = $newPricingArray;
        	}

        	$formData = $this->getRequest()->getPost();
        	unset($formData['floor_price_date']);
        	unset($formData['floor_price']);
        	unset($formData['floor_percent']);

                if($this->getRequest()->getPost('define_url_check')!=1) $form->removeElement('define_url');

            if($form->isValid($formData)){
                $formData = array(
                    'SiteURL' =>$form->getValue('SiteURL'),
                    'ServingURL' =>$form->getValue('ServingURL'),
                    'BlockedURL' => $form->getValue ('BlockedURL'),
                    'floor_pricing'=>$form->getValue('floor_pricing'),
                    'email_notlive_3day'=>$form->getValue('email_notlive_3day') == 1 ? NULL : 1,
                    'iframe_tags'=>$form->getValue('iframe_tags') == 1 ? 1 : NULL,
                    'lock_tags'=>$form->getValue('lock_tags')==1 ? 1 : NULL,
                    'define_url'=>$this->_getParam('define_url_check')==1 ? $form->getValue('define_url') : NULL,
                    'factor_revshare'=>$form->getValue('factor_revshare')==1 ? 1 : NULL,
                    'video_ads'=>$form->getValue('video_ads')==1 ? 1 : NULL,
                    'disable_rubicon_revenue'=>$form->getValue('disable_rubicon_revenue')==1 ? 1 : NULL,
                    'date_disable_rubicon'=>$form->getValue('date_disable_rubicon') ? $form->getValue('date_disable_rubicon') : NULL,
                    'baner_320'=>$form->getValue('baner_320')==1 ? 1 : NULL,
                    'hide_question'=>$form->getValue('hide_question')==1 ? 1 : 0,
                    'blank_ref_serve'=>$form->getValue('blank_ref_serve')==1 ? 1 : 0,
                    'header_bidding'=>$form->getValue('header_bidding')==1 ? 1 : 0,
                    'burst_tag'=>$form->getValue('burst_tag')==1 ? 1 : 0,
                    'desired_types'=>$form->getValue('desired_types')
                );
                if($formData['date_disable_rubicon']) 
                	$formData['request_disable_impression'] = $formData['date_disable_rubicon'];
                else 
                	$formData['request_disable_impression'] = NULL;

                $formData['store_tag_url'] = $this->_getParam('store_tag_url');
                $formData['store_tag_url_start'] = $this->_getParam('store_tag_url_start');


                    if($formData['store_tag_url'] && empty($formData['store_tag_url_start'])) $formData['store_tag_url_start'] = date('Y-m-d');
                elseif(empty($formData['store_tag_url']))  $formData['store_tag_url_start'] = NULL;

                $tmpSiteURL = preg_split('/(\<br \/\>)/', $formData['SiteURL']);
                $resSiteURL = null;
                foreach($tmpSiteURL as $key => $iter){

                    $iter = trim(strip_tags($iter));

                    if(isset($tmpSiteURL[$key+1])) $iter = $iter."\n";

                    $resSiteURL .= $iter;
                }
                $formData['SiteURL'] = $resSiteURL;

                $tmpServingURL = preg_split('/(\<br \/\>)/', $formData['ServingURL']);
                $resServingURL = null;
                foreach($tmpServingURL as $key => $iter){

                       $iter = trim(strip_tags($iter));
                    if($iter){

                        if(isset($tmpServingURL[$key+1])) $iter = $iter."\n";

                        $resServingURL .= $iter;
                    }
                }
                $formData['ServingURL'] = $resServingURL;
				$tmpBlockedURL = preg_split ( '/(\<br \/\>)/', $formData ['BlockedURL'] );
				$resBlockedURL = null;
				foreach ( $tmpBlockedURL as $key => $iter ) {
					$iter = trim ( strip_tags ( $iter ) );
					if ($iter) {
						if (isset ( $tmpBlockedURL [$key + 1] ))
							$iter = $iter . "\n";
						$resBlockedURL .= $iter;
					}
				}
				$formData ['BlockedURL'] = $resBlockedURL;
				
				$sitesModel->logging($siteInfo['PubID'],$id,$formData);

                $sitesModel->save($id, $formData);

                $tags = new Application_Model_DbTable_Tags();
                $tags->deleteSitePricing($siteInfo['PubID'], $siteInfo['SiteID']);

                if(count($newPricingArray)>0 && $formData['floor_pricing']){
                	foreach($newPricingArray as $item){
                		$tags->addSitePricing($siteInfo['PubID'], $siteInfo['SiteID'], $item['date'], $item['price'], $item['percent']);
                	}
                }

                if(empty($formData['floor_pricing']) && $siteInfo['floor_pricing']){

                    $tableFile = new Application_Model_DbTable_Cpm_File();
                    $tableCpm = new Application_Model_DbTable_Cpm_Minimum();

                    $sqlCpm = $tableCpm->select()
                                       ->where('status = 3')
                                       ->where('SiteID = ?', $siteInfo['SiteID']);

                       $dataCpm = $tableCpm->fetchRow($sqlCpm);
                    if($dataCpm){

                        $tableFile->delete('minimum_cpm_id = '.$dataCpm->id);
                        $dataCpm->delete();

                    }

                }

                if($siteInfo['tag_type']==4 && $formData['factor_revshare']==1){
                    $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
                    $dataCpm = $tableCpm->fetchRow("status = 3 AND SiteID='".$siteInfo['SiteID']."'");
                    if($dataCpm->cpm && $dataCpm->cpm!='Max Fill'){
                        $tableRevshare = new Application_Model_DbTable_UserRevshare();
                        $dataRevshare = $tableRevshare->fetchRow("PubID='".$siteInfo['PubID']."'", "date DESC");
                    }
                }

                if($this->_getParam('update_tags')){
                    $siteTagsModel = new Application_Model_DbTable_SitesTags();
                    $siteTagsModel->changeAction($id, 'gen', APPLICATION_ENV);
                }

                if($formData['disable_rubicon_revenue']==1){
                    $imressDisTable = new Application_Model_DbTable_ImpressDisable();
                    $imressDisTable->insert(array(
                        'SiteID'=>$id,
                        'created'=>date("Y-m-d H:i:s"),
                        'rubicon_date'=>$formData['date_disable_rubicon'],
                        'google_date'=>NULL
                    ));
                }

				foreach ( $dataUserSize as $iter ) {
					$iterUserSize = array (
							'SiteID' => $id,
							'AdSize' => $iter ['id'],
							'value' => $this->_getParam ( 'itemSize_' . $iter ['id'] ) ? 1 : 0 
					);
					$tableSize->saveData ( $iterUserSize );
					if($siteInfo['tag_type']==8 && $this->_getParam('itemSize_'.$iter['id'])==1 && $iter['active']==0){
	                    $dataPubmatic = $wantPubm->createRow();
	                    $dataPubmatic->SiteID = $id;
	                    $dataPubmatic->size = $iter['description'];
	                    $dataPubmatic->width = $iter['width'];
	                    $dataPubmatic->height = $iter['height'];
	                    $dataPubmatic->created = date("Y-m-d H:i:s");
	                    $dataPubmatic->save();															
					}
				}

                if($siteInfo['tag_type']==5 && $formData['baner_320']==1){
                    $siteTagsModel = new Application_Model_DbTable_SitesTags();
                    $siteTagsModel->changeAction($id, 'gen', APPLICATION_ENV);
                }elseif($siteInfo['tag_type']==5){
                    unlink($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['PubID']."/".$siteInfo['SiteID']."/async/320x50.js");
                }

                $this->_redirect('/administrator/sites/approved/');

            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }else{
            $this->view->formValues = $siteInfo;
            $this->view->formValues['update_tags'] = null;
            
            $this->view->pricing = $sitesModel->getSiteFlorPricing($this->view->formValues['PubID'], $this->view->formValues['SiteID']);
        }

        $this->view->data = $siteInfo;
        $this->view->dataTag = $tagInfo;
        $this->view->userSize = $dataUserSize;
        $this->view->setHelperPath(LIB_PATH .'/My/Helper', 'My_Helper');

    }

    public function updateAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $action = $this->_getParam('act');
        $name = $this->_getParam('name');
        $id = $this->_getParam('id');
        $color = $this->_getParam('color');
        $sitesModel = new Application_Model_DbTable_Sites();
        $data = array(
            'name'=>$name,
            'id'=>$id,
            'color'=>$color
        );
        echo $action;
        switch ($action){
            case 'live':
            	$sitesModel->deleteLiveStat($id);
            	$sitesModel->setLiveStat($data);
                $sitesModel->setLive($data);
                break;

            case 'privacy':
                $sitesModel->setPrivacy($data);
                break;

            case 'popup':
            	if($id){
            		$tagsInfo = $sitesModel->getSiteTagsById($id);
            	}
            	if($tagsInfo && $color=='green'){

            		if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$tagsInfo['user_id']))
            			mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$tagsInfo['user_id']);

            		if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$tagsInfo['user_id']."/".$tagsInfo['site_id']))
            			mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$tagsInfo['user_id']."/".$tagsInfo['site_id']);

            		if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$tagsInfo['user_id']."/".$tagsInfo['site_id']."/async"))
            			mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$tagsInfo['user_id']."/".$tagsInfo['site_id']."/async");

                        $txt = "if(window.top !== window.self){ document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$tagsInfo['site_id']."&size=6&iframe=1' style='display:none'>\"); }else{ document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$tagsInfo['site_id']."&size=6&iframe=0' style='display:none'>\"); }";
            		$txt.= "document.write(\"<script type='text/javascript'>\");var rp_account='8223';var rp_site='14911';var rp_zonesize='59842-20';var rp_adtype='js';var rp_smartfile='[SMART FILE URL]';var rp_kw='".$tagsInfo['SiteName']."';document.write(\"<\/script>\");document.write(\"<script type='text/javascript' src='http://ads-by.madadsmedia.com/tags/ad/8223.js'>\");document.write(\"<\/script>\");";
            		file_put_contents($_SERVER['DOCUMENT_ROOT']."/tags/".$tagsInfo['user_id']."/".$tagsInfo['site_id']."/async/pop.js", $txt);

            	}elseif($tagsInfo && $color=='red'){
            		unlink($_SERVER['DOCUMENT_ROOT']."/tags/".$tagsInfo['user_id']."/".$tagsInfo['site_id']."/async/pop.js");
            	}
                $sitesModel->setPopup($data);
                break;

        }
        $this->_redirect($_SERVER['HTTP_REFERER']);
    }

    public function newAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'mewSites';

        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();

        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;

        //$sites = new Application_Model_DbTable_Sites();
        //$this->view->sites = $sites->getNewSites();
    }

    public function ajaxNewAction()
    {
        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();

        /* MySQL connection */
        $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

        $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

        mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );


        $aColumns = array( 's.PubID', 's.SiteName', 'u.email', 'DATE_FORMAT(s.created, "%Y-%m-%d")', 's.SiteID');
         $Columns = array( 'PubID', 'SiteName', 'email', 'DATE_FORMAT(s.created, "%Y-%m-%d")', 'SiteID');
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email');

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "s.SiteID";

        /* DB table to use */
        $sTable = "sites AS s";
        $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID ";

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
            $sWhere = ' WHERE s.status = 1 ';
        }else{
            $sWhere.= ' AND s.status = 1 ';
        }

        $account = (int)$_GET['accounts'];
        if($account>0){
            if($sWhere == "")
                $sWhere = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere.= " AND u.account_manager_id='".$account."' ";
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

    public function viewnewAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'mewSites';

		$dataAuth = Zend_Auth::getInstance()->getIdentity();

        $id = $this->_getParam('id');
        $sitesModel = new Application_Model_DbTable_Sites();
        $this->view->data = $sitesModel->getNewSiteInfoById($id);

        $form = new Application_Form_ConfirmSite();
        $form->removeElement('enable_wire_transfer');
        $form->removeElement('notification_control_admin');

        if($this->getRequest()->isPost()){
			if ($this->getRequest()->getPost('action') != 2) {
				$form->getElement ( 'represent_domain' )->clearValidators ();
				$form->getElement ( 'authorize_domain' )->clearValidators ();
                $form->removeElement('changed_url');
                $form->removeElement('desired_types');
			}

            if($this->getRequest()->getPost('changed_url')==$this->view->data['url']){
                $form->getElement ( 'changed_url' )->removeValidator('Db_NoRecordExists');
            }
	        
            if($form->isValid($this->getRequest()->getPost())){

				if($form->getValue ( 'action' ) != 2){
	                if(APPLICATION_ENV != 'development'){
	                    $headers  = 'MIME-Version: 1.0' . "\r\n";
	                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	                    $headers .= "From: Publisher Support <support@madadsmedia.com>\r\n";
	                    $to = $this->view->data['email'];
	                    $title = $form->getValue('title');
	                    $message = $form->getValue('message');
	                    mail($to, $title, $message, $headers);
	                }
				}
                if($form->getValue('action')==4){ /* delete */

                    if($this->view->data['id']) $sitesModel->deleteNewSite($this->view->data['id']);
                    $sitesModel->deleteSite($this->view->data['SiteID']);
					$coApprovedModel = new Application_Model_DbTable_CoSiteApprovals();
					$coApprovedModel->delete("SiteID=".$this->view->data ['SiteID']);
                    $this->_redirect('/administrator/sites/new');
                }elseif($form->getValue('action')==1){ /* reject */

                    if($this->view->data['id']) $sitesModel->deleteNewSite($this->view->data['id']);
                       $sitesModel->rejectSite($this->view->data['SiteID'], $this->_getParam('notes'));

					$coApprovedModel = new Application_Model_DbTable_CoSiteApprovals();
					$coApprovedModel->delete("SiteID=".$this->view->data ['SiteID']);
                    $this->_redirect('/administrator/sites/new');

                }elseif($form->getValue('action')==2){ /* confirm */

			        require_once $_SERVER['DOCUMENT_ROOT'].'/../lib/Alexa/alexa.class.php';
			        $sitename = str_replace(array("http://", "https://", "www."), '', $this->view->data['url']);				        
			        $AlexaRank = new AlexaRank($sitename);
			        $rank = $AlexaRank->get('rank');
			        $USrank = $AlexaRank->get('USrank');
			        $rank = $rank!='"rank" does not exist.' ? $rank : 'NULL';
			        $USrank = $USrank!='"USrank" does not exist.' ? $USrank : 'NULL';
			        if($USrank != 'NULL'){
			        
			            $tmpRankArr = explode("(", $USrank);
			            $USrank = $tmpRankArr[0];
			        }  
                                    
                    $rank = ($rank == 'NULL' ? NULL : $rank);
                    $USrank = ($USrank == 'NULL' ? NULL : $USrank);
					
					/*
                    $resBlockedURL = null;
                    if($this->_getParam('forum_exist')){
                        $tmpBlockedURL = preg_split('/(\<br \/\>)/', $this->_getParam('BlockedURL'));                                        
                        foreach($tmpBlockedURL as $key => $iter){
                                $iter = trim(strip_tags($iter));
                                if($iter){
                                        if(isset($tmpBlockedURL[$key + 1]))
                                                $iter = $iter . "\n";
                                        $resBlockedURL .= $iter;
                                }
                        } 
                    }
                    */
                                        
			        $dataUpdate = array (
			                'SiteName' => $form->getValue('changed_url'),
			                'SiteURL' => $this->_getParam('SiteURL_changed'),
			                'status' => 0,
			                'alexaRank' => $rank,
			                'alexaRankUS' => $USrank,
			                'approved' => date("Y-m-d H:i:s"),
			                'approved_by' => $dataAuth->email,
                            'desired_types' => $form->getValue('desired_types')
			        );
			        $sitesModel->updateNewSite ( $dataUpdate, $this->view->data ['SiteID'] );
			        	
			        	
			        $userTable = new Application_Model_DbTable_Users();
			        $sql = $userTable->select()->from('users', array('account_manager_id'=>'users.account_manager_id'))->where("id='".$this->view->data['PubID']."'");
			        $dataUsers = $userTable->fetchRow($sql);
			        $userAccManager = $dataUsers->account_manager_id;
			        	
			        $SiteApprovalsTable = new Application_Model_DbTable_CoSiteApprovals();
			        $tableManager = new Application_Model_DbTable_ContactNotification();
			        	
			        if($userAccManager>0)
			            $where = "id!=$userAccManager";
			        else
			            $where = "1=1";
			        	
			        $SiteApprovalsTable->insert(array('SiteID'=>$this->view->data['SiteID'], 'type'=> 'site', 'date_aded'=>date('Y-m-d'), 'manager'=>$tableManager->getIdByMail($dataAuth->email)));
			        	
			        $sitesModel->deleteNewSite($this->view->data ['SiteID']);
			        //if ($this->view->data ['id'])
			        //$sitesModel->deleteNewSite ( $this->view->data ['id'] );
			        //$this->_redirect ( '/administrator/sites/confirm-ajax/SiteID/' . $this->view->data ['SiteID'] );
                }

                $this->_redirect('/administrator/sites/new');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }

    }

    public function viewnewCoAction() {
                            
		$layout = Zend_Layout::getMvcInstance ();
		$layout->nav = 'mewSites';
                
		$id = $this->_getParam ( 'id' );
                
        $form = new Application_Form_ConfirmSite();
        $sitesModel = new Application_Model_DbTable_Sites();
                
		$dataAuth = Zend_Auth::getInstance()->getIdentity();		
		$this->view->data = $sitesModel->getNewSiteInfoById($id);	
                
		$form->removeElement('enable_wire_transfer');
		$form->removeElement('notification_control_admin');
                
		if($this->getRequest()->isPost()){
                    
			if($this->getRequest()->getPost('action') != 2){
				$form->getElement('represent_domain')->clearValidators();
				$form->getElement('authorize_domain')->clearValidators();
                $form->removeElement('changed_url');
                $form->removeElement('desired_types');
			}

            if($this->getRequest()->getPost('changed_url')==$this->view->data['url']){
                $form->getElement ( 'changed_url' )->removeValidator('Db_NoRecordExists');
            }
                        
			if($form->isValid($this->getRequest()->getPost())){
			    
			    //Send if it is not first confirmation
			    if($form->getValue('action') != 2)
			    {
			        if (APPLICATION_ENV != 'development') {
			            $headers = 'MIME-Version: 1.0' . "\r\n";
			            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			            $headers .= "From: Publisher Support <" . Application_Model_DbTable_Registry_Setting::getByName ( 'admin_email' ) . ">\r\n";
			            $to = $this->view->data ['email'];
			            $title = $form->getValue ( 'title' );
			            $message = $form->getValue ( 'message' );
			            mail ( $to, $title, $message, $headers );
			        }	
			    }
				if($form->getValue('action') == 4) { /* delete */
					if ($this->view->data ['id'])
					$sitesModel->deleteNewSite($this->view->data['id']);
					$sitesModel->deleteSite ( $this->view->data ['SiteID'] );
					$coApprovedModel = new Application_Model_DbTable_CoSiteApprovals();
					$coApprovedModel->delete("SiteID=".$this->view->data ['SiteID']);
					$this->_redirect ( '/administrator/sites/new' );
				} elseif ($form->getValue ( 'action' ) == 1) { /* reject */
					if ($this->view->data ['id'])
					$sitesModel->deleteNewSite ( $this->view->data ['id'] );
					$sitesModel->rejectSite ( $this->view->data ['SiteID'], $this->_getParam ( 'notes' ) );
					$coApprovedModel = new Application_Model_DbTable_CoSiteApprovals();
					$coApprovedModel->delete("SiteID=".$this->view->data ['SiteID']);
					$this->_redirect ( '/administrator/sites/new' );
				} elseif ($form->getValue ( 'action' ) == 2) { /* confirm */			    
                                      
			
				        $dataUpdate = array('status' => 3,
                                            'co_approved_date' => date("Y-m-d H:i:s"),
                                            'co_approved_by' => $dataAuth->email,
											'SiteName' => $form->getValue('changed_url'),
											'SiteURL' => $this->_getParam('SiteURL_changed'),
                                            'desired_types' => $form->getValue('desired_types'));
                                        
				        $sitesModel->updateNewSite($dataUpdate, $this->view->data['SiteID']);
				        $sitesModel->update(array('co-approved'=>$dataAuth->name, 'status' => 3), "SiteID=".$this->view->data ['SiteID']);				        
				        $coApprovedModel = new Application_Model_DbTable_CoSiteApprovals();
				        $coApprovedModel->delete("SiteID=".$this->view->data ['SiteID']);
                                        
				        $headers = 'MIME-Version: 1.0' . "\r\n";
				        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				        $headers .= "From: Publisher Support <" . Application_Model_DbTable_Registry_Setting::getByName ( 'admin_email' ) . ">\r\n";
				        $to = $this->view->data ['email'];
				        $title = $form->getValue ( 'title' );
				        $message = $form->getValue ( 'message' );
				        
                                        mail ( $to, $title, $message, $headers );
                                        
				        $this->_redirect('/administrator/sites/confirm-ajax/SiteID/'.$this->view->data['SiteID']);
				    
				
				}
				$this->_redirect ( '/administrator/sites/new' );
			} else {
				$this->view->formErrors = $form->getMessages ();
				$this->view->formValues = $form->getValues ();
			}
		}		   
	}

    public function paymentsAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'payDue';

        $this->view->year = $this->_getParam('year');
        $this->view->month = $this->_getParam('month');
        $this->view->filter = $this->_getParam('filter');

        $users = new Application_Model_DbTable_Users();
        $tableContact = new Application_Model_DbTable_User_NewContact();

        $this->view->users = $users->getPaymentsDueReport($this->view->year, $this->view->month, $this->view->filter);
        $this->view->contact = $tableContact->getNum();

    }

    public function userpaymentAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $id = $this->_getParam('id');
        $users = new Application_Model_DbTable_Users();
        $payment_change = new Application_Model_DbTable_User_NewPaymentInfoChanges();
        $this->view->userInfo['payment_last_change'] = $payment_change->getLastData($this->_getParam('id'));
        $this->view->userInfo = $users->getUserAllInfoById($id);
    }

    public function generateAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();

    }


    public function generateReportAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'payDue';

        $tableCronDue = new Application_Model_DbTable_Cron_Due();

        $str = preg_split('/[- :]/', date('Y-m-d H:i:s'));
        $time = date('Y-m-d H:i:s', mktime($str[3], $str[4] - 20, $str[5], $str[1], $str[2], $str[0]));

        $this->view->data = $tableCronDue->fetchAll();
        $this->view->time = $time;
    }

    public function setcomentAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();

    	$data = array();

    	if($this->_getParam('year') && $this->_getParam('month') && $this->_getParam('PubID')){
	    	$form = new Application_Form_PaymentComment();
	    	if($this->getRequest()->isPost()){
	    		if($form->isValid($this->getRequest()->getPost())){
	    			$siteModel = new Application_Model_DbTable_Sites();
	    			$siteModel->setPaymentComent($this->_getParam('month'), $this->_getParam('year'), $this->_getParam('PubID'), $form->getValue('comment'));
	    			$data = array('status'=>'OK', 'PubID'=>$this->_getParam('PubID'));
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

    	$siteModel = new Application_Model_DbTable_Sites();
    	$this->view->comments = $siteModel->getPaymentComent($this->_getParam('month'), $this->_getParam('year'), $this->_getParam('PubID'));

    	//print_r($this->view->comments);
    }

    public function commentsAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();

    	if($this->_getParam('year') && $this->_getParam('month') && $this->_getParam('PubID')){
    		$siteModel = new Application_Model_DbTable_Sites();
    		$form = new Application_Form_DueNotes();
    		if($this->getRequest()->isPost()){
    			if($form->isValid($this->getRequest()->getPost())){

    				if($this->_getParam('id'))
    					$siteModel->updatePaymentNote($this->_getParam('id'), $this->_getParam('month'), $this->_getParam('year'), $this->_getParam('PubID'), $form->getValue('comment'));
    				else
    					$siteModel->setPaymentNote($this->_getParam('month'), $this->_getParam('year'), $this->_getParam('PubID'), $form->getValue('comment'));
    				$this->view->message = 'Saved';
    			}
    		}else{
    			if($this->_getParam('id')){
    				$form->populate($siteModel->getPaymentNoteByID($this->_getParam('id')));
    			}
    		}

    		$this->view->form = $form;

    	}

    }

    public function sendmailAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$id = $this->_getParam('id');
    	$userModel = new Application_Model_DbTable_Users();
    	$form = new Application_Form_SendMail();
    	$userInfo = $userModel->getUserAllInfoById($id);

    	if($this->getRequest()->isPost()){
    		if($form->isValid($this->getRequest()->getPost())){
    			$this->view->message = 'OK';

    			$headers  = 'MIME-Version: 1.0' . "\r\n";
    			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    			$headers .= "From: Publisher Support <support@madadsmedia.com>\r\n";
    			$to = $form->getValue('email');
    			$title = $form->getValue('subject');
    			$message = $form->getValue('message');
    			mail($to, $title, $message, $headers);

    			//mail($form->getValue('email'), $form->getValue('subject'), $form->getValue('message'));
    		}
    	}else{
    		$form->getElement('email')->setValue($userInfo['email']);
    		$form->getElement('subject')->setValue('MadAdsMedia.com Payment Profile Missing');
    		$message='<p> Hello,</p>
    		<p>We\'re attempting to pay you for money you\'ve earned with us, but your payment profile is incomplete.  Please update your payment profile as soon as possible to ensure your payment isn\'t delayed.</p>
    		<p><a href="http://www.madadsmedia.com/payment"> Update Your Payment Profile Now</a></p>
			<p>Regards,<br>
			MadAdsMedia.com Publisher Team</p>
			<p><font style="font-size: 11px;">If you\'ve received this email in error, please let us know.</font></p>';
    		$form->getElement('message')->setValue($message);
    	}

    	$this->view->form = $form;
    }

    public function liveAction()
    {
    	$sites = new Application_Model_DbTable_Sites();
    	$date =  $this->_getParam('date');
    	$this->view->date = $date;

    	$liveSites = $sites->getLiveSitesByDate($date);

    	$this->view->sites = $liveSites;
    }

    private function _setdfp($id)
    {
        	set_time_limit(0);

        require_once LIB_PATH."/My/Google/Api/Ads/Dfp/Lib/DfpUser.php";
        require_once LIB_PATH.'/My/Google/Api/Ads/Dfp/Util/DateTimeUtils.php';

        $site = new Application_Model_DbTable_Sites();
        $siteInfo = $site->getSiteInfoByID($id);

        $units = array(
            '728x90'=>$siteInfo['SiteName'] .'-(ID:'. $siteInfo['PubID'] . ')-ROS-728x90',
            '160x600'=>$siteInfo['SiteName'] .'-(ID:'. $siteInfo['PubID'] . ')-ROS-160x600',
            '300x250'=>$siteInfo['SiteName'] .'-(ID:'. $siteInfo['PubID'] . ')-ROS-300x250',
        );

        // Get DfpUser from credentials in "../auth.ini"
        // relative to the DfpUser.php file's directory.
        $user = new DfpUser();

        // Log SOAP XML request and response.
        $user->LogDefaults();

        // Get the InventoryService.
        $inventoryService = $user->GetService('InventoryService', 'v201206');

        // Get the PlacementService.
        $placementService = $user->GetService('PlacementService', 'v201206');

        // Get the NetworkService.
        $networkService = $user->GetService('NetworkService', 'v201206');

        // Get the LineItemService.
        $lineItemService = $user->GetService('LineItemService', 'v201206');


        // Get the effective root ad unit's ID for all ad units to be created under.
        $network = $networkService->getCurrentNetwork();
        $effectiveRootAdUnitId = $network->effectiveRootAdUnitId;


        //----------------AdUnits------------------
        foreach($units as $key=>$value){

        	// Create a statement to select the children of the effective root ad unit.
        	$filterStatement =
        	new Statement("WHERE name = :nameUnit LIMIT 1",
        			MapUtils::GetMapEntries(array(
        					'nameUnit' => new TextValue($value))));

        	// Get ad units by statement.
        	$page = $inventoryService->getAdUnitsByStatement($filterStatement);

        	if(!isset($page->results)){

	            $adUnit = new AdUnit();
	            $adUnit->name = $value;
	            $adUnit->parentId = $effectiveRootAdUnitId;
	            $adUnit->description = '';
	            $adUnit->targetWindow = 'BLANK';

	            // Create ad unit size.
	            $adUnitSize = new AdUnitSize();
	            $sizes = explode("x", $key);
	            $adUnitSize->size = new Size($sizes[0], $sizes[1], FALSE);
	            $adUnitSize->environmentType = 'BROWSER';

	            // Set the size of possible creatives that can match this ad unit.
	            $adUnit->adUnitSizes = array($adUnitSize);

	            //$AdSenseSettings = new AdSenseSettingsInheritedProperty();
	            //$AdSenseSettings->value = new AdSenseSettings();
	      		//$adUnit->inheritedAdSenseSettings = $AdSenseSettings->value->adSenseEnabled = false;

	            $adUnit->inheritedAdSenseSettings->value->adSenseEnabled = false;

	            $inventoryService->createAdUnit($adUnit);
        	}
        }


        // Create a statement to select the children of the effective root ad unit.
        $filterStatement =
        new Statement("WHERE (name = :nameUnit728 OR name = :nameUnit300 OR name = :nameUnit160) AND status = :status LIMIT 10",
        		MapUtils::GetMapEntries(array(
        				'nameUnit728' => new TextValue($units['728x90']),
        				'nameUnit300' => new TextValue($units['300x250']),
        				'nameUnit160' => new TextValue($units['160x600']),
        				'status' => new TextValue('ACTIVE')
        				)));

        // Get ad units by statement.
        $adUnits = $inventoryService->getAdUnitsByStatement($filterStatement);



		$placementsArray = array();

		//----------------Placements------------------

       if($adUnits->results){
       		foreach($adUnits->results as $unit){

       			// Create a statement to select the children of the effective root ad unit.
       			$filterStatement =
       			new Statement("WHERE name = :namePlace LIMIT 1",
       					MapUtils::GetMapEntries(array(
       							'namePlace' => new TextValue($unit->name))));

       			// Get placements by statement.
       			$page = $placementService->getPlacementsByStatement($filterStatement);

       			if(!isset($page->results)){

	       			$placement = new Placement();
	       			$placement->name = $unit->name;
	       			$placement->description = $unit->name;
	       			$placement->targetedAdUnitIds[] = $unit->id;

	       			$created = $placementService->createPlacement($placement);

	       			unset($placement);
       			}
       		}
       }


       // Create a statement to select the children of the effective root ad unit.
        $filterStatement =
        new Statement("WHERE (name = :namePlace728 OR name = :namePlace300 OR name = :namePlace160) AND status = :status LIMIT 10",
        		MapUtils::GetMapEntries(array(
        				'namePlace728' => new TextValue($units['728x90']),
        				'namePlace300' => new TextValue($units['300x250']),
        				'namePlace160' => new TextValue($units['160x600']),
        				'status' => new TextValue('ACTIVE')
        				)));

       // Get placements by statement.
       $page = $placementService->getPlacementsByStatement($filterStatement);

       if(isset($page->results)){
			foreach ($page->results as $placement){
				$dataName = explode("-", $placement->name);
				$size = array_pop($dataName);
				$placementsArray[$size] = $placement->id;
			}
       }

       if($siteInfo['rubicon_type']!=3){

	       //----------------Order Rubicon------------------

	        // Set the ID of the order to get line items from.
			if($siteInfo['rubicon_type']==1)
	        	$orderId = '101986072';
			else
				$orderId = '106865872';

	        // Create bind variables.
	        $vars =
	        MapUtils::GetMapEntries(array('orderId' => new NumberValue($orderId)));

	        // Create a statement to only select line items that need creatives
	        // from a given order.
	        $filterStatement = new Statement("WHERE orderId = :orderId ", $vars);

	        // Get line items by statement.
	        $page = $lineItemService->getLineItemsByStatement($filterStatement);

	        // Display results.
	        if (isset($page->results)) {
	        	$i = $page->startIndex;

	        	$lineItems = $page->results;

	        	// Remove archived line items.
	        	array_filter($lineItems,
	        			create_function('$lineItem', 'return !$lineItem->isArchived;'));

	        	foreach ($lineItems as $lineItem) {
	        		if(!$lineItem->isArchived){
		        		//get sizes line
		        		if($lineItem->creativePlaceholders){
		        			foreach ($lineItem->creativePlaceholders as $item){
		        				$lineSize = $item->size->width.'x'.$item->size->height;
		        				//if isset size add
		        				if(isset($placementsArray[$lineSize]) && !in_array($placementsArray[$lineSize], $lineItem->targeting->inventoryTargeting->targetedPlacementIds)){
		        					array_push($lineItem->targeting->inventoryTargeting->targetedPlacementIds, $placementsArray[$lineSize]);
		        				}
		        			}
		        		}
						$lineItemService->updateLineItem($lineItem);
	        		}
	        	}
	        }

       }


        //----------------Google-AdExchange------------------

        // Set the order that all created line items will belong to and the placement
        // ID to target.
        $orderId = '139262752';



	        //-----------For 728x90----------

	       // Create a statement to select the children of the effective root ad unit.
	        $filterStatement =
	        new Statement("WHERE orderId = :orderId AND name = :name LIMIT 10",
	        		MapUtils::GetMapEntries(array(
	        				'orderId' => new NumberValue($orderId),
	        				'name' => new TextValue($siteInfo['SiteName'] .'-728x90')
	        				)));

	       // Get lineItem by statement.
	       $page = $lineItemService->getLineItemsByStatement($filterStatement);

	       if(!isset($page->results) && isset($placementsArray['728x90'])){

		        // Create the creative placeholder.
		        $creativePlaceholder = new CreativePlaceholder();
		        $creativePlaceholder->size = new Size(728, 90, FALSE);
		        $inventoryTargeting = new InventoryTargeting();
		        $inventoryTargeting->targetedPlacementIds = array($placementsArray['728x90']);
		        // Create targeting.
		        $targeting = new Targeting();
		        $targeting->inventoryTargeting = $inventoryTargeting;

	        	$lineItem = new LineItem();
	        	$lineItem->orderId = $orderId;

	        	$lineItem->name = $siteInfo['SiteName'] .'-728x90';
	        	$lineItem->targetPlatform = 'WEB';
	        	$lineItem->creativePlaceholders = array($creativePlaceholder);

	        	$lineItem->lineItemType = 'AD_EXCHANGE';
	        	$lineItem->unitType = 'IMPRESSIONS';
	        	$lineItem->duration = 'NONE';
	        	$lineItem->webPropertyCode = 'ca-pub-1032345577796158';
	        	$lineItem->startDateTimeType='IMMEDIATELY';
	        	$lineItem->unlimitedEndDateTime = true;

	        	$lineItem->targeting = $targeting;

	        	$lineItem->unitsBought = 1;
	        	$lineItem->costType = 'CPM';
	        	$lineItem->costPerUnit = new Money('USD', 0);


	        	$lineItemService->createLineItem($lineItem);
	       }




        	//-----------For 160x600----------

	       // Create a statement to select the children of the effective root ad unit.
	       $filterStatement =
	       new Statement("WHERE orderId = :orderId AND name = :name LIMIT 10",
	       		MapUtils::GetMapEntries(array(
	       				'orderId' => new NumberValue($orderId),
	       				'name' => new TextValue($siteInfo['SiteName'] .'-160x600')
	       		)));

	       // Get lineItem by statement.
	       $page = $lineItemService->getLineItemsByStatement($filterStatement);

	       if(!isset($page->results) && isset($placementsArray['160x600'])){
	        	// Create the creative placeholder.
	        	$creativePlaceholder = new CreativePlaceholder();
	        	$creativePlaceholder->size = new Size(160, 600, FALSE);
	        	$inventoryTargeting = new InventoryTargeting();
	        	$inventoryTargeting->targetedPlacementIds = array($placementsArray['160x600']);
	        	// Create targeting.
	        	$targeting = new Targeting();
	        	$targeting->inventoryTargeting = $inventoryTargeting;

	        	$lineItem = new LineItem();
	        	$lineItem->orderId = $orderId;

	        	$lineItem->name = $siteInfo['SiteName'] .'-160x600';
	        	$lineItem->targetPlatform = 'WEB';
	        	$lineItem->creativePlaceholders = array($creativePlaceholder);

	        	$lineItem->lineItemType = 'AD_EXCHANGE';
	        	$lineItem->unitType = 'IMPRESSIONS';
	        	$lineItem->duration = 'NONE';
	        	$lineItem->webPropertyCode = 'ca-pub-1032345577796158';
	        	$lineItem->startDateTimeType='IMMEDIATELY';
	        	$lineItem->unlimitedEndDateTime = true;

	        	$lineItem->targeting = $targeting;

	        	$lineItem->unitsBought = 1;
	        	$lineItem->costType = 'CPM';
	        	$lineItem->costPerUnit = new Money('USD', 0);

	        	$lineItemService->createLineItem($lineItem);
	       }



        	//-----------For 300x250----------
	       // Create a statement to select the children of the effective root ad unit.
	       $filterStatement =
	       new Statement("WHERE orderId = :orderId AND name = :name LIMIT 10",
	       		MapUtils::GetMapEntries(array(
	       				'orderId' => new NumberValue($orderId),
	       				'name' => new TextValue($siteInfo['SiteName'] .'-300x250')
	       		)));

	       // Get lineItem by statement.
	       $page = $lineItemService->getLineItemsByStatement($filterStatement);

	       if(!isset($page->results) && isset($placementsArray['300x250'])){
	        	// Create the creative placeholder.
	        	$creativePlaceholder = new CreativePlaceholder();
	        	$creativePlaceholder->size = new Size(300, 250, FALSE);
	        	$inventoryTargeting = new InventoryTargeting();
	        	$inventoryTargeting->targetedPlacementIds = array($placementsArray['300x250']);
	        	// Create targeting.
	        	$targeting = new Targeting();
	        	$targeting->inventoryTargeting = $inventoryTargeting;

	        	$lineItem = new LineItem();
	        	$lineItem->orderId = $orderId;

	        	$lineItem->name = $siteInfo['SiteName'] .'-300x250';
	        	$lineItem->targetPlatform = 'WEB';
	        	$lineItem->creativePlaceholders = array($creativePlaceholder);

	        	$lineItem->lineItemType = 'AD_EXCHANGE';
	        	$lineItem->unitType = 'IMPRESSIONS';
	        	$lineItem->duration = 'NONE';
	        	$lineItem->webPropertyCode = 'ca-pub-1032345577796158';
	        	$lineItem->startDateTimeType='IMMEDIATELY';
	        	$lineItem->unlimitedEndDateTime = true;

	        	$lineItem->targeting = $targeting;

	        	$lineItem->unitsBought = 1;
	        	$lineItem->costType = 'CPM';
	        	$lineItem->costPerUnit = new Money('USD', 0);

	        	$lineItemService->createLineItem($lineItem);
	       }
	}

        public function notificationAction()
        {
             $config['smtp'] = 0;

             $tableSite = new Application_Model_DbTable_Sites();

             $session = new Zend_Session_Namespace('Default');
            if($session->message){
                $this->view->message = $session->message;
                unset($session->message);
            }

             $form = new Application_Form_SendNotification();
             $form->setDefaults(array(
                     'admin_email'   => Application_Model_DbTable_Registry_Setting::getByName('admin_email'),
                     'admin_name'    => Application_Model_DbTable_Registry_Setting::getByName('admin_name')
             ));


             if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->_getAllParams())) {

                    $dataForm = array('filter' => $form->getValue('filter'),
                                      'subject' => $form->getValue('subject'),
                                      'message' => $form->getValue('message'),
                                      'admin_email' => $form->getValue('admin_email'),
                                      'admin_name'  => $form->getValue('admin_name'));

                    $tableSite->setNotification($dataForm);

                    $session->message = 'Your emails will be sent';

                    //$dataUser = $tableSite->getEmailForLetters($dataForm['filter']);

//                    $mail = new My_Mail($config);
//                    foreach($dataUser as $iter) $mail->addRecipient($iter['email']);
//                    $mail->send($dataForm['subject'], $dataForm['message'], $dataForm['admin_email'], $dataForm['admin_name']);
                    /*
                    $result = array();

                    foreach($dataUser as $iter){

                            $headers  = 'MIME-Version: 1.0' . "\r\n";
                            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                            $headers .= "From: ".$dataForm['admin_name']." <".$dataForm['admin_email'].">\r\n";
                            $to      = $iter['email'];
                            $title   = $dataForm['subject'];
                            $message = $dataForm['message'].'<br /><a href="http://'.$_SERVER['HTTP_HOST'].'/disable-notification/'.$iter['code'].'">Unsubscribe</a>';

                            $result[$iter['PubID']] = array('name' => $iter['name'],
                                                            'email' => $iter['email'],
                                                            'status' => mail($to, $title, $message, $headers));
                    }

                    $this->view->result = $result;
                    */
                    $this->_redirect('/administrator/sites/notification/');
                }
            }

             $this->view->form = $form;


        }


        public function csvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'csv';

            $form = new Application_Form_Csv();

            if($this->getRequest()->isPost()){
                    if($form->isValid($this->getRequest()->getPost())){
                            if($form->csv->receive()){
                                    $this->view->fileName = $form->getValue('csv');
                                    $this->view->type = $form->getValue('type');
                            }else{

                            }
                    }else{
                    $this->view->formErrors = $form->getMessages();
                    }
            }

            $this->view->form = $form;
        }

        public function csv2Action()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'csv2';
        }

        public function csv3Action()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'csv2';
        }

        public function pubmaticCsvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'pub_csv';
        }

        public function amazonCsvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'amazon_csv';
        }

        public function pulseCsvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'pulse_csv';
        }

        public function popCsvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'pulse_csv';
        }

        public function sekindoCsvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'sekindo_csv';
        }
        
        public function aolCsvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'aol_csv';
        }
        
        public function aolOutstreamCsvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'aol_outstream_csv';
        }
        
        public function bRealTimeCsvAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'b_real_time_csv';
        }

        public function csvuploadAction()
        {
        	set_time_limit(0);

        	$layout = Zend_Layout::getMvcInstance();
        	$layout->disableLayout();
        	$this->_helper->viewRenderer->setNoRender();
        	$siteModel = new Application_Model_DbTable_Sites();
        	$data = array();

        	$fileName = $_SERVER['DOCUMENT_ROOT'].'/rubicon_csv/'.$this->getRequest()->getParam('fileName');
        	$ftell =  $this->getRequest()->getParam('ftell');
        	$next = null;

        	if (($handle_f = fopen($fileName, "r")) !== FALSE)
        	{
        		//select display_size
        		$zonesArray = array();
        		$results = $siteModel->getAllZones();
        		if($results){
        			foreach($results as $zone){
        				$zonesArray[$zone['id']] = $zone['description'];
        			}
        		}


        		$data['status'] = 'OK';
        		$data['fileName'] = $this->getRequest()->getParam('fileName');
        		// �����������, ���� �� ���������� ������ � ������������� �����
        		// ���� ��, �� ��������� ������������ �� ��� �����
        		if($ftell!=0){
        			fseek($handle_f,$ftell);
        		}

        		$i=0;

        		// ���������� ���������� � ������ ����� �� �����
        		while ( ($data_f = fgetcsv($handle_f, 1000, ","))!== FALSE) {

        			if($data_f[0]!='Zone'){

        				if($data_f[2]=='Pop (Pop)'){
        					$zoneName = 'Pop (Pop)';
        				}else{
        					$zones = explode("(", $data_f[2]);
        					$zoneName = str_replace(")", "", trim($zones[1]));
        				}
        				//echo $zoneName.'<br>';
        				$display_zone = array_search($zoneName, $zonesArray);
        				$display_zone = $display_zone ? $display_zone : NULL;


        				$data['sites'][$i]['Zone'] = $data_f[0];
        				$data['sites'][$i]['Date'] = $data_f[1];
        				$data['sites'][$i]['Size'] = $data_f[2];
        				$data['sites'][$i]['Impressions'] = $data_f[3];
        				$data['sites'][$i]['Paid_Impressions'] = $data_f[4];
        				$data['sites'][$i]['Rate'] = $data_f[5];
        				$data['sites'][$i]['Revenue'] = $data_f[6];
        				$data['sites'][$i]['rCPM'] = $data_f[7];

        				$siteInfo = $siteModel->getSiteInfoByName($data_f[0]);
        				if($siteInfo['SiteID']){
        					$data['sites'][$i]['error'] = 'OK';
        					$data['sites'][$i]['SiteID'] = $siteInfo['SiteID'];
        					$data['sites'][$i]['AdSize'] = $display_zone;

        					if(isset($data['sites'][$i]['SiteID']) && isset($data['sites'][$i]['AdSize'])){
                                                        $rubiconInfo = $siteModel->getRubiconData($siteInfo['SiteID'], $data['sites'][$i]['AdSize'], $data['sites'][$i]['Date']);
                                                        if($rubiconInfo['lock_data']!=1){
                                                            $siteModel->deleteRubiconData($data_f[1], $data_f[0], $data_f[2]);
                                                            $siteModel->addRubiconData($data['sites'][$i]);
                                                        }else{
                                                            $data['sites'][$i]['error'] = 'Not updated. "lock_data" attribute is activated';
                                                        }
        					}
        				}else{
        					$data['sites'][$i]['error'] = 'ERROR! Site not found';
        				}

        				$i++;
        			}

        			if($i==100){
        				$data['ftell'] = ftell($handle_f);
        				$next = 1;
        				break;
        			}


        		}

        		fclose($handle_f);

        		if($next==1){
        			$data['next'] = 1;
        		}else{
        			$data['next'] = 0;
        			unlink($fileName);
        		}


        	}else{
        		$data = array('error'=>'File not found');
        	}

        	$json = Zend_Json::encode($data);
        	echo $json;
        }


        public function blacklistsAction()
        {
        	$layout = Zend_Layout::getMvcInstance();
        	$layout->nav = 'blacklists';

        	$form = new Application_Form_Blacklists();
        	$siteModel = new Application_Model_DbTable_Sites();

        	if($this->getRequest()->isPost()){
        		if($form->isValid($this->getRequest()->getPost())){
                    $tmp = preg_split('/(\<br \/\>)/', $form->getValue('list'));
                    $resList = null;
                    foreach($tmp as $key => $iter){

                        $iter = trim(strip_tags($iter));
                        if($iter){

                            if(isset($tmp[$key+1])) $iter = $iter."\n";

                            $resList .= $iter;
                        }
                    }

                    $siteModel->saveBlacklist($resList);
        			$this->_redirect('/administrator/sites/blacklists/');
        		}else{
        			$this->view->formErrors = $form->getMessages();
        			$this->view->formValues = $form->getValues();
        		}
        	}else{
        		$this->view->formValues = $siteModel->getBlacklist();
        	}
        }

        public function deniedAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'denySites';
        }

        public function ajaxDeniedAction()
        {
            set_time_limit(0);

            $this->_helper->layout()->disableLayout(); // disable layout
            $this->_helper->viewRenderer->setNoRender(); // disable view rendering
            $auth = Zend_Auth::getInstance()->getIdentity();

            /* MySQL connection */
            $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

            $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

            mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );


            $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.alexaRank', 's.alexaRankUS', 's.PubID', 's.SiteID', 's.SiteName', 's.reject_notes');
             $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'alexaRank', 'alexaRankUS', 'PubID', 'SiteID', 'SiteName', 'reject_notes');
         $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.alexaRank');

            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "s.SiteID";

            /* DB table to use */
            $sTable = "sites AS s";
            $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID ";

            if($_GET['accounts']=='my')
                $sJoin.= " LEFT JOIN contact_notification AS cn ON cn.id=u.account_manager_id ";

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
                    case 'status_approved':      $sWhere = ' WHERE (s.status = 2 OR u.reject = 1) AND s.status_approved = 1 '; break;
                    default:                     $sWhere = ' WHERE (s.status = 2 OR u.reject = 1) AND s.status_approved IS NULL '; break;
                }

            } else {

                switch($_GET['filter']){
                    case 'status_approved':      $sWhere.= ' AND (s.status = 2 OR u.reject = 1) AND s.status_approved = 1 '; break;
                    default:                     $sWhere.= ' AND (s.status = 2 OR u.reject = 1) AND s.status_approved IS NULL '; break;
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



        public function inviteDeniedAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->disableLayout();

            $id = $this->_getParam('id');
            $site =  $this->_getParam('site');

            $form = new Application_Form_Notified();

            if($this->getRequest()->isPost()){
                if($form->isValid($this->getRequest()->getPost())){

                        $users = new Application_Model_DbTable_Users();
                        $userData = $users->getUserById($id);

                        $mail = new Zend_Mail();

                        $mail->setFrom(Application_Model_DbTable_Registry_Setting::getByName('admin_email'), Application_Model_DbTable_Registry_Setting::getByName('admin_name'));
                        $mail->addTo($userData->email, $userData->name);
                        $mail->setSubject($form->getValue('subject'));
                        $mail->setBodyHtml($form->getValue('message'));

                        $mail->send();

                        $this->view->message = 'Invite has been sent!';
                    }
                }

$form->getElement('subject')->setValue('An Update Regarding Your MadAdsMedia.com Approval');
$form->getElement('message')->setValue("Hello,
<br><br>
After a re-review of your website, ".$site.", we've found that you now are eligible to become a publisher of the MadAdsMedia.com publisher network.
<br><br>
Please reply to this email at your earliest convenience and we will begin preparing your ad tags.
<br><br>
Welcome to the MadAds Media network!
<br><br>
Regards,
<br>
MadAdsMedia.com Publisher Team");

                $this->view->form = $form;
        }

        public function blockAction()
        {
                $layout = Zend_Layout::getMvcInstance();
                $layout->disableLayout();

                $siteID = $this->_getParam('SiteID');

                $tableSite = new Application_Model_DbTable_Sites();

                $sql = $tableSite->select()->setIntegrityCheck(false)
                                 ->from(array('s' => 'sites'), array('*'))
                                 ->join(array('u' => 'users'),('u.id = s.PubID'), array('u.email AS email', 'u.name AS UserName'))
                                 ->where('SiteID = ?', $siteID);

                $dataSite = $tableSite->fetchRow($sql);

                if($this->getRequest()->isPost()){

                    $resultMail = false;

                    if($this->_getParam('mail-status')){

                        $mail = new Zend_Mail();

                        $mail->setFrom(Application_Model_DbTable_Registry_Setting::getByName('admin_email'), Application_Model_DbTable_Registry_Setting::getByName('admin_name'));
                        $mail->addTo($dataSite->email, $dataSite->UserName);
                        $mail->setSubject($this->_getParam('subject'));
                        $mail->setBodyHtml($this->_getParam('text'));

                        $resultMail = $mail->send();
                    }

                    $tableSite->update(array('status' => 2, 
                    						'status_approved' => 1,
                    						'reject_date' => date("Y-m-d H:i:s")),'SiteID = '.$siteID);
                    $websiteLogsModel = new Application_Model_DbTable_WebsiteLogs();
                    $websiteLogsModel->logEnabledDisabledChanges($dataSite->PubID,$siteID, 2);//Log that site is blocked

                    $siteTagsModel = new Application_Model_DbTable_SitesTags();
                    $siteTagsModel->changeAction($dataSite->SiteID, 'remove_all', APPLICATION_ENV);

                    if($resultMail) $this->view->message = 'Site '.$dataSite->SiteName.' is denied, email send.';
                    else            $this->view->message = 'Site '.$dataSite->SiteName.' is denied, email not send.';

                }

                $this->view->data = $dataSite;
        }

        public function unBlockAction()
        {
            $siteID = $this->_getParam('SiteID');

            $tableSite = new Application_Model_DbTable_Sites();
            $tableTag = new Application_Model_DbTable_Tags();

            $sql = $tableSite->select()->where('SiteID = ?', $siteID);

            $dataSite = $tableSite->fetchRow($sql);
            $dataTag = $tableTag->getTagBySiteID($dataSite->SiteID);

            if($dataSite->approved == NULL) $this->_redirect('/administrator/sites/confirm-ajax/SiteID/'.$dataSite->SiteID);

            $dataSite->status = 3;
            $dataSite->status_approved = null;
            $dataSite->denied_no_tag = null;
            $dataSite->save();
            
            $websiteLogsModel = new Application_Model_DbTable_WebsiteLogs();
            $websiteLogsModel->logEnabledDisabledChanges($dataSite->PubID,$siteID, 1);//Log that site is blocked

            if($dataTag){
                $siteTagsModel = new Application_Model_DbTable_SitesTags();
                $siteTagsModel->changeAction($dataSite->SiteID, 'gen', APPLICATION_ENV);
            }

            $this->_redirect('/administrator/sites/view/id/'.$siteID);
        }

        public function loadUrlAction()
        {
            $layout = Zend_Layout::getMvcInstance();
    	    $layout->disableLayout();

            $this->view->id = $this->_getParam('id');
        }

        public function ajaxUrlAction()
        {
            set_time_limit(0);

            $this->_helper->layout()->disableLayout(); // disable layout
            $this->_helper->viewRenderer->setNoRender(); // disable view rendering

            /* MySQL connection */
            $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

            $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

            mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );


            $aColumns = array( 'mu.url', 'SUM(mu.num)');
             $Columns = array( 'url', 'SUM(mu.num)');
         $likeColumns = array( 0 => 'mu.url', 1 => 'SUM(mu.num)');

            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "mu.SiteID";

            /* DB table to use */
            $sTable = "madads_url AS mu ";
            $sGroup = "GROUP BY mu.url ";

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


            if($sWhere == "") $sWhere = " WHERE DATE_FORMAT(mu.query_date, '%Y-%m-%d')>='".$_GET['start_date']."' AND DATE_FORMAT(mu.query_date, '%Y-%m-%d')<='".$_GET['end_date']."' AND mu.SiteID = '".(int)$_GET['id']."'";
            else              $sWhere .= " AND DATE_FORMAT(mu.query_date, '%Y-%m-%d')>='".$_GET['start_date']."' AND DATE_FORMAT(mu.query_date, '%Y-%m-%d')<='".$_GET['end_date']."' AND mu.SiteID = '".(int)$_GET['id']."'";


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

        public function ajaxUrlIframeAction()
        {
            set_time_limit(0);

            $this->_helper->layout()->disableLayout(); // disable layout
            $this->_helper->viewRenderer->setNoRender(); // disable view rendering

            /* MySQL connection */
            $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

            $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

            mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );

            $aColumns = array( 'mui.url', 'mui.url_full', 'mui.src', 'SUM(mui.num)');
             $Columns = array( 'url', 'url_full', 'src', 'SUM(mui.num)');
         $likeColumns = array( 0 => 'mui.url', 1 => 'mui.url_full', 3 => 'SUM(mui.num)');

            /* Indexed column (used for fast and accurate table cardinality) */
            $sIndexColumn = "mui.SiteID";

            /* DB table to use */
            $sTable = "madads_url_iframe AS mui ";
            $sGroup = "GROUP BY mui.url, mui.url_full, mui.src ";

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


            if($sWhere == "") $sWhere = " WHERE DATE_FORMAT(mui.query_date, '%Y-%m-%d')>='".$_GET['start_date']."' AND DATE_FORMAT(mui.query_date, '%Y-%m-%d')<='".$_GET['end_date']."' AND mui.SiteID = '".(int)$_GET['id']."'";
            else              $sWhere .= " AND DATE_FORMAT(mui.query_date, '%Y-%m-%d')>='".$_GET['start_date']."' AND DATE_FORMAT(mui.query_date, '%Y-%m-%d')<='".$_GET['end_date']."' AND mui.SiteID = '".(int)$_GET['id']."'";


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

        public function deniedUrlAction()
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->nav = 'denied-url';
        }

        public function ajaxGetDeniedUrlAction()
        {
            header ('Content-Type: text/html; charset=UTF-8');

            set_time_limit(0);

            $this->_helper->layout()->disableLayout(); // disable layout
            $this->_helper->viewRenderer->setNoRender(); // disable view rendering

            /* MySQL connection */
            $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

            $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

            mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );


              if($_GET['filterSite'] == 'group'){

                      $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 'mb.hide', 'mb.hide', 'mb.hide', 'mb.hide', 'IF(mb.updated, mb.updated, mb.query_date)', 'GROUP_CONCAT( DISTINCT  cast( concat( IFNULL(mb.hide, 0), "::", IF(mb.iframe, mb.src_full, mb.url_full)) AS char ) SEPARATOR ";;" )');
                       $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'hide', 'hide', 'hide', 'hide', 'IF(mb.updated, mb.updated, mb.query_date)', 'GROUP_CONCAT( DISTINCT  cast( concat( IFNULL(mb.hide, 0), "::", IF(mb.iframe, mb.src_full, mb.url_full)) AS char ) SEPARATOR ";;" )');
                   $likeColumns = array( 0 => 's.PubID', 1 => 'mb.SiteID', 2 => 's.SiteName', 3 => 'u.email', 8 => 'IF(mb.updated, mb.updated, mb.query_date)');

                     /* Indexed column (used for fast and accurate table cardinality) */
                     $sIndexColumn = "s.SiteID";

                     /* DB table to use */
                     $sTable = "sites  AS s ";
                     $sJoin = "JOIN madads_blocked mb ON mb.SiteID = s.SiteID
                               JOIN users AS u ON u.id = s.PubID ";
                     $sGroup = "GROUP BY s.SiteID ";

              } else {

                    $aColumns = array( 's.PubID', 'mb.SiteID', 's.SiteName', 'u.email', 'mb.hide', 'mb.hide', 'mb.hide', 'mb.hide', 'IF(mb.updated, mb.updated, mb.query_date)', 'IF(mb.iframe, mb.src_full, mb.url_full)');
                     $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'hide', 'hide', 'hide', 'hide', 'IF(mb.updated, mb.updated, mb.query_date)', 'IF(mb.iframe, mb.src_full, mb.url_full)');
                 $likeColumns = array( 0 => 's.PubID', 1 => 'mb.SiteID', 2 => 's.SiteName', 3 => 'u.email', 8 => 'IF(mb.updated, mb.updated, mb.query_date)');

                    /* Indexed column (used for fast and accurate table cardinality) */
                    $sIndexColumn = "mb.SiteID";

                    /* DB table to use */
                    $sTable = "madads_blocked AS mb ";
                    $sJoin = "JOIN sites AS s ON s.SiteID = mb.SiteID
                              JOIN users AS u ON u.id = s.PubID ";
                    $sGroup = "GROUP BY mb.SiteID, IF(mb.iframe, mb.src_full, mb.url_full) ";
              }


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

                    case 'hide'  : $sWhere = ' WHERE mb.hide IS NULL AND mb.pending IS NULL '; break;
                    case 'hidden': $sWhere = ' WHERE mb.hide = 1 ';     break;
                    case 'pending': $sWhere = ' WHERE mb.pending = 1 ';     break;
                    default      : break;
                }

            } else {

                switch($_GET['filter']){

                    case 'hide'  : $sWhere .= ' AND mb.hide IS NULL AND mb.pending IS NULL '; break;
                    case 'hidden': $sWhere .= ' AND mb.hide = 1 ';     break;
                    case 'pending': $sWhere .= ' AND mb.pending = 1 ';     break;
                    default      : break;
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

    public function sendAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $siteID = (int) $this->_getParam('id');
        $url = $this->_getParam('url');
        $url = urldecode($url);

        $tableSites = new Application_Model_DbTable_Sites();

        $sqlSite = $tableSites->select()->setIntegrityCheck(false)
                           ->from(array('s' => 'sites'),
                                  array('s.SiteName', 's.SiteID', 's.PubID'))
                           ->where('s.SiteID = ?', $siteID)
                           ->join(array('u' => 'users'),('s.PubID = u.id'),
                                  array('u.email', 'u.name'));

        $dataSite = $tableSites->fetchRow($sqlSite);

        if($this->getRequest()->isPost()){
            if($this->_getParam('subject') && $this->_getParam('text')){
                    $dbAdapter = Zend_Db_Table::getDefaultAdapter();

                    /*$sql = 'UPDATE madads_blocked SET pending = 1 WHERE (url_full = "'.$url.'" OR src_full = "'.$url.'")';*/

                    $sql = 'UPDATE madads_blocked SET pending = 1 WHERE (url_full LIKE "%'.$url.'%" OR src_full LIKE "%'.$url.'%")';

                    $dbAdapter->query($sql);

                    $mail = new Zend_Mail();
                    $tableNotifi = new Application_Model_DbTable_Sites_NotifiMessage();

                    $type = $this->_getParam('type');
                    $text = $this->_getParam('text');
                    $subject = $this->_getParam('subject');

                    $mail->setFrom(Application_Model_DbTable_Registry_Setting::getByName('admin_email'), Application_Model_DbTable_Registry_Setting::getByName('admin_name'));
                    $mail->addTo($dataSite->email, $dataSite->name);
                    $mail->setSubject($subject);
                    $mail->setBodyHtml($text);

                    $mail->send();

                    if($type == 1){

                       $tableNotifi->saveMessage(array('PubID' => $dataSite['PubID'], 'SiteID' => $dataSite['SiteID'], 'page' => 1, 'mail' => $dataSite['email'], 'name' => $dataSite['name'], 'subject' => $subject, 'text' => $text));
                    }

                    $this->view->message = 'Message has been sent!';
                }
            }


        $this->view->data = $dataSite;
        $this->view->url = $url;

        $this->render('send');
    }

    public function inactiveAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'inactiveSites';

        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();

        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;
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
//                             0          1             2           3                   4                        5                                                              6                                                          7                  8               9                   10                     11             12           13           14         15           16                     17
        $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.impressions_1day_ago', 's.impressions_2day_ago', 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 's.highest_impres', 's.alexaRank', 's.inactive_date', 's.manual_followup', 't.type', 's.privacy', 's.live_name', 'u.id', 't.id as tag_id', 's.date_highest_impres', 's.hide' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'impressions_1day_ago', 'impressions_2day_ago', 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 'highest_impres', 'alexaRank', 'inactive_date', 'manual_followup', 'type', 'privacy', 'live_name', 'id', 'tag_id', 'date_highest_impres', 'hide' );
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.impressions_1day_ago' , 5 => 's.impressions_2day_ago', 6 => 'IFNULL(ROUND((s.impressions_1day_ago - s.impressions_2day_ago)/s.impressions_2day_ago * 100), 0)', 7 => 's.highest_impres', 8 => 's.alexaRank', 12 => 't.type');

	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";

	/* DB table to use */
	$sTable = "sites AS s";
        $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID
                   LEFT JOIN tags AS t ON t.site_id = s.SiteID ";

	$sOrder = " ORDER BY s.inactive_date DESC";
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
                        AND u.reject = 0
                        AND s.lived IS NOT NULL
                        AND s.live IS NULL
                        AND s.impressions_1day_ago < 100
                        AND s.period_more_100_impression = 0
                        AND s.once_more_1000_impression = 1 ";

        } else {

            $sWhere .= " AND s.status = 3
                         AND u.reject = 0
                         AND s.lived IS NOT NULL
                         AND s.live IS NULL
                         AND s.impressions_1day_ago < 100
                         AND s.period_more_100_impression = 0
                         AND s.once_more_1000_impression = 1 ";

        }


        if($sWhere == ""){
            switch($_GET['filterType']){
                case '4'  : $sWhere = ' WHERE t.type=4 '; break;
                case '5': $sWhere = ' WHERE t.type=5 ';     break;
                case '6': $sWhere = ' WHERE t.type=6 ';     break;
                case '7': $sWhere = ' WHERE t.type=7 ';     break;                
                case '8': $sWhere = ' WHERE t.type=8 ';     break;
                case '9': $sWhere = ' WHERE t.type=9 ';     break;
                case '10': $sWhere = ' WHERE t.type=10 ';     break;
                default      : break;
            }
        } else {
            switch($_GET['filterType']){
                case '4'  : $sWhere .= ' AND t.type=4 '; break;
                case '5': $sWhere .= ' AND t.type=5 ';     break;
                case '6': $sWhere .= ' AND t.type=6 ';     break;
                case '7': $sWhere .= ' AND t.type=7 ';     break;                
                case '8': $sWhere .= ' AND t.type=8 ';     break;
                case '9': $sWhere = ' AND t.type=9 ';     break;
                case '10': $sWhere = ' AND t.type=10 ';     break;
                default      : break;
            }
        }

        if(isset($_GET['manager']) && $_GET['manager'] != -1){
            //$sLimit = " LIMIT 0,10 ";

            if($sWhere=="")
                $sWhere = " WHERE u.account_manager_id = '".(int)$_GET['manager']."' ";
            else
                $sWhere .= " AND u.account_manager_id = '".(int)$_GET['manager']."' ";
        }

        $account = (int)$_GET['accounts'];
        if($account>0){
            if($sWhere == "")
                $sWhere = " WHERE u.account_manager_id='".$account."' ";
            else
                $sWhere.= " AND u.account_manager_id='".$account."' ";
        }

		$hide = (int) $_GET['hidden'];
		if ($hide == 0 || $hide == 1) {
			$sWhere .= ($sWhere == "" ? ' WHERE ' : ' AND ').'s.hide = '.$hide;
		}

		$sWhere .= ($sWhere == "" ? ' WHERE ' : ' AND ').'s.impressions_1day_ago >= 0 AND s.impressions_2day_ago >= 0';

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

	public function ajaxHideAction()
	{
		$this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

		$output['status']	= false;
		$sid				= (int) $this->_getParam('iSId');
		$model				= new Application_Model_DbTable_Sites();

		if ($site = $model->find($sid)) {
			if ($model->update(array('hide' => ($site[0]->hide == 0 ? 1 : 0)), 'SiteID = ' . $sid)) {
				$output['status']	= true;
			}
		}
		$this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
	}

    public function followupAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $siteID = (int) $this->_getParam('id');
        $actions = (int) $this->_getParam('actions');

        if($siteID){

            $sitesModel = new Application_Model_DbTable_Sites();

            if($actions == 1) $sitesModel->update(array('manual_followup' => 1, 'email_notlive_3day' => NULL), "SiteID='".$siteID."'");
                         else $sitesModel->update(array('manual_followup' => NULL), "SiteID='".$siteID."'");
        }

        $this->_redirect($_SERVER['HTTP_REFERER']);
    }

    public function deductedAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'deductedSites';

        $this->view->headTitle('Deducted Revenue');
    }

    public function ajaxDeductedAction()
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

        $aColumns = array( 's.PubID', 's.SiteID', 's.SiteName', 'u.email', 's.request_disable_impression', 'IFNULL(s.date_disable_rubicon, s.date_disable_google)', 'IF(s.date_disable_rubicon, "Rubicon", "Google")', 'ROUND(SUM(urf.disable_revenue_Total), 2)', 'IF(id.id, "Waiting", "Executed")' );
         $Columns = array( 'PubID', 'SiteID', 'SiteName', 'email', 'request_disable_impression', 'IFNULL(s.date_disable_rubicon, s.date_disable_google)', 'IF(s.date_disable_rubicon, "Rubicon", "Google")', 'ROUND(SUM(urf.disable_revenue_Total), 2)', 'IF(id.id, "Waiting", "Executed")' );
     $likeColumns = array( 0 => 's.PubID', 1 => 's.SiteID', 2 => 's.SiteName', 3 => 'u.email', 4 => 's.request_disable_impression' , 5 => 'IFNULL(s.date_disable_rubicon, s.date_disable_google)', 6 => 'IF(s.date_disable_rubicon, "Rubicon", "Google")', 8 => 'IF(id.id, "Waiting", "Executed")');

	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "s.SiteID";

	/* DB table to use */
	$sTable = "sites AS s";
        $sJoin = " LEFT JOIN users AS u ON u.id = s.PubID
                   LEFT JOIN tags AS t ON t.site_id = s.SiteID
                   LEFT JOIN impress_disable AS id ON id.SiteID = s.SiteID
                   LEFT JOIN users_reports_final AS urf ON urf.SiteID = s.SiteID ";

        $sGroup = " GROUP BY s.SiteID ";

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
                        AND u.reject = 0
                        AND (s.date_disable_rubicon IS NOT NULL OR s.date_disable_google IS NOT NULL) ";
        } else {

            $sWhere .= " AND s.status = 3
                         AND u.reject = 0
                         AND (s.date_disable_rubicon IS NOT NULL OR s.date_disable_google IS NOT NULL) ";
        }

        switch($_GET['filterType']){
            case '4': $sWhere .= ' AND t.type=4 '; break;
            case '5': $sWhere .= ' AND t.type=5 '; break;
            case '6': $sWhere .= ' AND t.type=6 '; break;
            case '7': $sWhere .= ' AND t.type=7 '; break;
            case '8': $sWhere .= ' AND t.type=8 '; break;
            case '9': $sWhere .= ' AND t.type=9 '; break;
            case '10': $sWhere .= ' AND t.type=10 '; break;
            default      : break;
        }

        if(isset($_GET['accounts']) && $_GET['accounts']=='my'){

           $sWhere .= " AND cn.mail = '".$auth->email."' AND cn.status=1 ";
           $sJoin  .= " LEFT JOIN contact_notification AS cn ON cn.id=u.account_manager_id ";
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
    
    public function ajaxOptimizationGetValuesAction()
    {
    	$this->_helper->layout()->disableLayout(); // disable layout
    	$this->_helper->viewRenderer->setNoRender(); //disable layout
    	$siteId = $this->_getParam('siteId');
    	if(isset($siteId) AND (intval($siteId) != 0))
    	{
    		$optimization_model = new Application_Model_DbTable_OptimizationValues();
    		$result = $optimization_model->getData($siteId);
    		$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    	}
    }
    
    public function ajaxOptimizationSetValuesAction()
    {
    	$this->_helper->layout()->disableLayout(); // disable layout
    	$this->_helper->viewRenderer->setNoRender(); //disable layout
    	$siteId = $this->_getParam('siteId');
    	$value = $this->_getParam('value');
    	$notes = $this->_getParam('notes');
    	if(isset($siteId) AND (intval($siteId) != 0)	AND isset($value) AND isset($notes))
    	{
			if(is_numeric($value))
			{
				$optimization_model = new Application_Model_DbTable_OptimizationValues();
				$result = $optimization_model->updateData($siteId, $value, $notes);
			} 
			else
				$result = array('error' => true);
    		$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    		
    	}    	
    }
    
    public function addNoteAction()
    {
    	$this->_helper->layout()->disableLayout(); // disable layout
    	$this->_helper->viewRenderer->setNoRender(); //disable layout
    	$params = $this->_getAllParams();
		$websiteLogsModel = new Application_Model_DbTable_WebsiteLogs();
		$result = $websiteLogsModel->AddNote($params);
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function deleteNoteAction()
    {
    	$this->_helper->layout()->disableLayout(); // disable layout
    	$this->_helper->viewRenderer->setNoRender(); //disable layout
    	$params = $this->_getAllParams();
    	$websiteLogsModel = new Application_Model_DbTable_WebsiteLogs();
    	$result = $websiteLogsModel->DeleteNote($params);
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function mailEverLiveAction()
    {
        $this->_helper->layout()->disableLayout();
        
        $siteID = (int) $this->_getParam('id');
        $never = (int) $this->_getParam('never');
        
        $tableUser = new Application_Model_DbTable_Users();
        $tableSite = new Application_Model_DbTable_Sites();
        $tableContact = new Application_Model_DbTable_ContactNotification();
        $tableStatic = new Application_Model_DbTable_StaticPage();
        $tableMail = new Application_Model_DbTable_Mail();
        
        $dataSite = $tableSite->getSiteInfoByID($siteID);
        $dataContact = $tableContact->getDataByID($dataSite['account_manager_id']);
        $dataSignature = $tableStatic->getDataByName('signature'); 
   
        $dataSignature['content'] = str_replace('{ADMIN_NAME_HERE}', $dataContact['name'], $dataSignature['content']);

        $dataSignature['content'] = str_replace('{ADMIN_EMAIL_HERE}', $dataContact['mail'], $dataSignature['content']);
        
        if($dataSite && $dataContact){
            
            $dataUser = $tableUser->getUserById($dataSite['PubID']); 
        
            if($this->getRequest()->isPost()){

                $status = $this->_getParam('status');
                $message = $this->_getParam('text');
                $subject = $this->_getParam('subject');               

                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";    
                $headers .= "From: ".$dataContact['name']." <".$dataContact['mail'].">\r\n";                                    
                $to = $dataSite['email']; 
                $message .= $dataSignature['content'];  
                
                $resultInfo = 'Message has been sent!';
                
                $dataInsert = array('PubID'   => $dataSite['PubID'],
                		'subject' => $subject,
                		'text'    => $message,
                		'author'  => $dataContact['mail'],
                		'account_manager' => 1,
                		'created' => date('Y-m-d H:i:s'));                   
                
                switch($status){
                    
                    case 1 : 
                    		 if($dataSite['mail_ever_live'] == 1 OR $dataSite['mail_ever_live'] == 2 OR $dataSite['mail_ever_live'] == 3)
                    		 {	
                    		 	$tableSite->update(array('mail_ever_live' => 4), array('SiteID = ?' => $dataSite['SiteID']));
                    		 	$tableUser->update(array('notifi_ever_live' => 1), array('id = ?' => $dataSite['PubID']));
                    		 }
                    		 else
                    	     	$tableSite->update(array('mail_ever_live' => 1), array('SiteID = ?' => $dataSite['SiteID']));   
                             mail($to, $subject, $message, $headers);
                             $tableMail->insert($dataInsert);
                             break;
                         
                    case 2 : 
                        	 if($dataSite['mail_ever_live'] == 1 OR $dataSite['mail_ever_live'] == 2 OR $dataSite['mail_ever_live'] == 3)
                        	 {	
                        	 	$tableSite->update(array('mail_ever_live' => 4), array('SiteID = ?' => $dataSite['SiteID']));
                        	 }
                        	 else
                        		$tableSite->update(array('mail_ever_live' => 2), array('SiteID = ?' => $dataSite['SiteID']));
                             $tableMail->insert($dataInsert);

                             //$tableUser->update(array('notifi_ever_live' => 1), array('id = ?' => $dataSite['PubID']));
                             mail($to, $subject, $message, $headers); 

                             break;
                         
                    case 3 : 
                        	 if($dataSite['mail_ever_live'] == 1 OR $dataSite['mail_ever_live'] == 2 OR $dataSite['mail_ever_live'] == 3)
                        	 {
                        	 	$tableSite->update(array('mail_ever_live' => 4), array('SiteID = ?' => $dataSite['SiteID']));
                        	 	$tableUser->update(array('notifi_ever_live' => 1), array('id = ?' => $dataSite['PubID']));
                        	 }
                        	 else
                    		 $tableSite->update(array('mail_ever_live' => 3), array('SiteID = ?' => $dataSite['SiteID']));   
                             $resultInfo = $dataSite['SiteName'].' has been marked as notified!';
                             break;
                    
                    case 4 : 
                        	 if($dataSite['mail_ever_live'] == 1 OR $dataSite['mail_ever_live'] == 2 OR $dataSite['mail_ever_live'] == 3)
                        	 {	
                        	 	$tableSite->update(array('mail_ever_live' => 4), array('SiteID = ?' => $dataSite['SiteID']));
                        	 	$tableUser->update(array('notifi_ever_live' => 1), array('id = ?' => $dataSite['PubID']));
                        	 }
                        	 else
                    		 $tableSite->update(array('mail_ever_live' => 3), array('SiteID = ?' => $dataSite['SiteID']));   
                             mail($to, $subject, $message, $headers);
                             $tableMail->insert($dataInsert);
                             $resultInfo = 'Message has been sent! And '.$dataSite['SiteName'].' has been marked as notified!';
                             break;
                }              

                $this->view->message = $resultInfo;                
            }
        
        $this->view->data = $dataSite;
        $this->view->contact = $dataContact;
        $this->view->mailActive = $dataUser['notifi_ever_live'];
        $this->view->lived = $tableSite->everLiveByUser($dataSite['PubID']);
        $this->view->mail_data = $tableMail->getDataByUser($dataSite['PubID']);
        $this->view->never = $never;

        } else $this->view->message = 'Data for this site not found !';
    }    
    
    public function contactAction()
    {
        set_time_limit ( 0 );
        $this->_helper->layout ()->disableLayout ();
        $session = new Zend_Session_Namespace('Default');
        if($session->message)
        {
            $this->view->message = $session->message;
            unset($session->message);
        }
        $PubID = ( int ) $this->_getParam ( 'PubID' );
        $tableUser = new Application_Model_DbTable_Users ();
        $tableStatic = new Application_Model_DbTable_StaticPage ();
        $tableMail = new Application_Model_DbTable_Mail ();
        $tableManager = new Application_Model_DbTable_ContactNotification ();
        $dataUser = $tableUser->getUserById ( $PubID );
        $tmpManager = $tableManager->getActiveContact ();
        $dataAuth = Zend_Auth::getInstance ()->getIdentity ();
        $dataManager = array ();
        foreach ( $tmpManager as $iter ) {
            $dataManager [$iter ['id']] = $iter;
        }
        if ($this->getRequest ()->isPost ()) {
            $menegerID = ( int ) $this->_getParam ( 'accountManager' );
             
            $status = (int) $this->_getParam('status');
            $staff_id = (int) $this->_getParam('staff');
            $client = (int) $this->_getParam('client');
            $bcc = (int) $this->_getParam('bcc');
             
            $to = trim(strip_tags($this->_getParam('to')));
            $subject = $this->_getParam ( 'subject' );
            $text = $this->_getParam ( 'text' );
            $dbAdapter = Zend_Db_Table::getDefaultAdapter ();
            $dataSignature = $tableStatic->getDataByName ( 'signature' );
            $dataSignature ['content'] = str_replace ( '{ADMIN_NAME_HERE}', $dataManager [$menegerID] ['name'], $dataSignature ['content'] );
            $dataSignature ['content'] = str_replace ( '{ADMIN_EMAIL_HERE}', $dataManager [$menegerID] ['mail'], $dataSignature ['content'] );
             
             
            $arrEmailvalid = array();
            $arrEmail = preg_split("[,]", $to);
            foreach($arrEmail as $iterEmail){
                 
                $iterEmail = trim($iterEmail);
                if(filter_var($iterEmail, FILTER_VALIDATE_EMAIL)){ $arrEmailvalid[] = $iterEmail; }
                 
            } $to = count($arrEmailvalid) ? implode(",", $arrEmailvalid) : "";
             
            $text = $text . $dataSignature ['content'];
            switch($status){
                case 1 :
                    foreach($arrEmailvalid as $iterEmail){
                        $classMail = new Zend_Mail ();
                        $classMail->setFrom ( $dataManager [$menegerID] ['mail'], $dataManager [$menegerID] ['name'] );
                        $classMail->addTo ( $iterEmail, $dataUser->name );
                        $classMail->setSubject ( $subject );
                        $classMail->setBodyHtml ( $text);
                        if($bcc) $classMail->addBcc($dataManager [$menegerID] ['mail']);
                        $classMail->send ();
                    }
                    $session->message = 'Data has been save, and Email sent !';
                    break;
                case 2 :
                    $session->message = 'Data has been save, without Email !';
                    $subject =  $client ? 'Note (Client\'s Response)' : 'Note';
                    $text = $text ? $text : '';
                    break;
                default :
                    $session->message = 'Request return error, Please try again !';
                    $this->_redirect('/administrator/sites/contact/PubID/'.$PubID);
                    break;
            }
             
            $dataInsert = array (
                    'PubID' => $PubID,
                    'subject' => $subject,
                    'text' => $text,
                    'author' => $dataAuth->email,
                    'account_manager' => $dataManager [$menegerID] ['id'],
                    'created' => date ( 'Y-m-d H:i:s' )
            );
            $tableMail->insert ( $dataInsert );
            $this->_redirect ( '/administrator/sites/contact/PubID/' . $PubID );
        }
        $this->view->dataUser = $dataUser;
        $this->view->contactManager = $dataManager;
        $this->view->data = $tableMail->getDataByUserWithManager ( $PubID );
    }    
    
	public function mailContactedAction() {
		$this->_helper->layout ()->disableLayout ();
		$dataAuth = Zend_Auth::getInstance()->getIdentity();
		$siteID = ( int ) $this->_getParam ( 'id' );
		$tableSite = new Application_Model_DbTable_Sites ();
		$tableContact = new Application_Model_DbTable_ContactNotification ();
		$tableStatic = new Application_Model_DbTable_StaticPage ();
		$tableMail = new Application_Model_DbTable_Mail ();
		$dataSite = $tableSite->getSiteInfoByID ( $siteID );
		//$dataContact = $tableContact->getDataByID ( $dataSite ['account_manager_id'] );
		$dataSignature = $tableStatic->getDataByName ( 'signature' );
		
		$tmpManager = $tableContact->getActiveContact();
		$dataManager = array();
    	foreach($tmpManager as $iter){ $dataManager[$iter['id']] = $iter; }
		/*
		$dataSignature['content'] = str_replace('{ADMIN_NAME_HERE}', $dataContact['name'], $dataSignature['content']);

		$dataSignature['content'] = str_replace('{ADMIN_EMAIL_HERE}', $dataContact['mail'], $dataSignature['content']);
		*/
		
		if ($dataSite) {
			if ($this->getRequest ()->isPost ()) {
				$status_mail = $this->_getParam ( 'status' );
				$menegerID = (int) $this->_getParam('accountManager');
				$message = $this->_getParam ( 'text' );
				$subject = $this->_getParam ( 'subject' );
				$headers = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= "From: " . $dataManager[$menegerID]['name'] . " <" . $dataManager[$menegerID]['mail'] . ">\r\n";
				$to = $dataSite ['email'];
				
	    		$dataSignature['content'] = str_replace('{ADMIN_NAME_HERE}', $dataManager[$menegerID]['name'], $dataSignature['content']);
	
	    		$dataSignature['content'] = str_replace('{ADMIN_EMAIL_HERE}', $dataManager[$menegerID]['mail'], $dataSignature['content']);
								
				$message .= $dataSignature['content'];
				if ($status_mail == 1) {
					mail ( $to, $subject, $message, $headers );
					$this->view->message = 'Message has been sent and marked as notified!';
					$dataInsert = array (
							'PubID' => $dataSite ['PubID'],
							'subject' => $subject,
							'text' => $message,
							'author' => $dataAuth->email,
							'account_manager' => $dataManager[$menegerID]['id'],
							'created' => date ( 'Y-m-d H:i:s' ) 
					);
					$tableMail->insert ( $dataInsert );
				} else { /* Marked as notified */
					$this->view->message = 'Marked as notified!';
				}
				$tableSite->setTable ();
				$tableSite->update ( array (
						'mail_ever_live' => 3,
						'manual_followup' => 1,
						'email_notlive_3day' => NULL 
				), "SiteID='" . $siteID . "'" );
			}
			$this->view->data = $dataSite;
			$this->view->contactManager = $dataManager;
			$this->view->mail_data = $tableMail->getDataByUser ( $dataSite ['PubID'] );
		} else
			$this->view->message = 'Data for this site not found !';
	}    
}