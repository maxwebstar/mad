<?php
class Administrator_AjaxController extends Zend_Controller_Action
{
    
    public function init() {
                
        $this->_helper
                ->AjaxContext
                ->addActionContext('index', 'json')
                ->initContext('json');
       
        $this->_helper
                ->AjaxContext
                ->addActionContext('csv', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('validator-site-name', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('validator-site-name-exist', 'json')
                ->initContext('json');
  
        $this->_helper
                ->AjaxContext
                ->addActionContext('add-site', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('confirm-site', 'json')
                ->initContext('json');
    
        $this->_helper
                ->AjaxContext
                ->addActionContext('sent-for-approval', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('optimization-upload', 'json')
                ->initContext('json');
            
        $this->_helper
                ->AjaxContext
                ->addActionContext('site-want-api', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('generate-due', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('denied-url-hide', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('denied-site-hide', 'json')
                ->initContext('json');        
                 
        $this->_helper
                ->AjaxContext
                ->addActionContext('approve-psa-url', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('fixed-site', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('save-optimization-status', 'json')
                ->initContext('json');

    }

    public function indexAction() 
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        $wire = $this->getRequest()->getParam('wire');
        $userId = $this->getRequest()->getParam('id');
                       
        $tableUser = new Application_Model_DbTable_Users();
        
        $sql = $tableUser->select()->where('id = ?', (int) $userId);
        $dataUser = $tableUser->fetchRow($sql);
        
        if($dataUser){
            
            $dataUser->enable_wire_transfer = $wire==1 ? 1 : NULL;
            $dataUser->save();
            
            $this->view->result = array('status' => 1, 'wire' => $wire );
            
        } else {
            
            $this->view->result = array('status' => 0, 'error' => 'user not found' );
        }
    }
    
    public function csvAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        $id = $this->getRequest()->getParam('id');
        
        if(is_file('csv/'.$id.'/'.date('Y-m-d').'.csv') == false){
        
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        
        $today = date("Y-m-d");
        $str = preg_split('/[-]/', $today);
                
        $start = date("Y-m-d", mktime(0, 0, 0, $str[1], $str[2] - 7 /* day */, $str[0]));
        $end = date("Y-m-d", mktime(0, 0, 0, $str[1], $str[2] - 1 /* day */, $str[0]));
        
        $sql = "SELECT  uf.query_date as Date,
                        uf.SiteID as SiteID,
                        u.email AS user_email,
                        s.SiteName AS site_name,
                        s.auto_report_file AS Admeld,
                        s.tag_name AS tag_name,

                        ROUND(SUM(uf.paid_impressions)) AS impressions,
                        ROUND(SUM(uf.revenue)) AS revenue

                FROM users_reports_final AS uf

                LEFT JOIN sites AS s ON s.SiteID=uf.SiteID
                LEFT JOIN tags AS t ON t.site_id=uf.SiteID
                LEFT JOIN users AS u ON u.id=uf.PubID

                WHERE uf.SiteID = '".$id."' 
                  AND DATE_FORMAT(uf.query_date, '%Y-%m-%d') >= '".$start."'
                  AND DATE_FORMAT(uf.query_date, '%Y-%m-%d') <= '".$end."'

                GROUP BY uf.query_date, uf.SiteID
                ORDER BY uf.query_date DESC";
        
        $arrSite = $dbAdapter->query($sql)->fetchAll();
          
        if(count($arrSite)){
        
            $path = 'csv/'.$id;    

            if (!is_dir($path)) {
                  mkdir($path);
                  chmod($path, 0777);
            }

            $file = $path.'/'.date('Y-m-d').'.csv';
            $fileOpen = fopen($file, 'w');

            if($arrSite[0]['Admeld']){
                
                fputcsv($fileOpen, array('Date', 'Publisher', 'Site Name', 'Tag Name', 'Impressions', 'CPM', 'Revenue'));
    
                foreach($arrSite as $row){
                  
                       $cpm = number_format(($row['revenue'] * 1000 / $row['impressions']), 2, '.', '');
                    
                       fputcsv($fileOpen, array($row['Date'], $row['user_email'], $row['site_name'], $row['tag_name'], $row['impressions'], $cpm, $row['revenue']));
                }
                
            } else {
                
                fputcsv($fileOpen, array('Date', 'Publisher', 'Site Name', 'Impressions', 'CPM', 'Revenue'));
    
                foreach($arrSite as $row){
                  
                       $cpm = number_format(($row['revenue'] * 1000 / $row['impressions']), 2, '.', '');
                    
                       fputcsv($fileOpen, array($row['Date'], $row['user_email'], $row['site_name'], $row['impressions'], $cpm, $row['revenue']));
                }
    
            }
            
            
            fclose($fileOpen);
            
                 $this->view->result = array('status' => 1, 'file' => '/'.$file, 'data' => $arrSite );
        
        } else { $this->view->result = array('status' => 0, 'error' => 'Site not found in data base'); }
        
        } else { $this->view->result = array('status' => 1, 'file' => '/csv/'.$id.'/'.date('Y-m-d').'.csv'); }
    }
    
    public function validatorSiteNameAction()
    {
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(); 
               
        $tableUser = new Application_Model_DbTable_Users();
        
        $this->view->result = $tableUser->getUserSiteByName($this->_getParam('siteName'));
       
    }
    
    public function validatorSiteNameExistAction()
    {
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(); 
               
        $tableUser = new Application_Model_DbTable_Users();
        
        $this->view->result = $tableUser->getUserSiteByNameExist($this->_getParam('siteName'), $this->_getParam('PubID'));
       
    }
    
    public function addSiteAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        /* Get input data */
        
	$params = $this->_getAllParams();
        /*$autoCreateTag = $this->_getParam('auto_create_tag');*/
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/../lib/Alexa/alexa.class.php';
        $sitename = str_replace(array("http://", "https://", "www."), '',trim($this->_getParam('SiteURL')));				        
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
     
                
        $formData = array('user' => $this->_getParam('user'),
                          'name' => trim($this->_getParam('SiteName')),
                          'SiteURL' => trim($this->_getParam('SiteURL')),
                          'category'=> $this->_getParam('SiteCategory'), 
                          'notes'   => $this->_getParam('notes'),
                          'type'    => $this->_getParam('siteType'),
                          'floor_pricing' => $this->_getParam('floor_pricing'),
                          'auto_report_file'  => $this->_getParam('auto_report_file'),
                          'email_notlive_3day' => $this->_getParam('email_notlive_3day'),
                          'limited_demand_tag' => $this->_getParam('limited_demand_tag'),
                          'create_dfp_passbacks' => $this->_getParam('create_dfp_passbacks'),
                          'creative_passback' => $this->_getParam('creativePassback') ? serialize($this->_getParam('creativePassback')) : null,
                          'status' => 0,
                          'admin_email'=>$auth->email,
                          'alexaRank' => $rank,
                          'alexaRankUS' => $USrank,
                          'approved_by' => $auth->email,
                            'desired_types'=>$this->_getParam('desired_types'));
                        
                $arrSiteURL = preg_split('/[\n]/', $formData['SiteURL']); 
        foreach($arrSiteURL as $key => $SiteURL){
            
            if($SiteURL){ 
                
                $formData['define_url'] = strtolower($SiteURL);
                $formData['define_url'] = str_replace(array("https://", "http://", "www.", " "), "", $formData['define_url']);
                $strLength = strlen($formData['define_url']);                
                $formData['define_url'] = $formData['define_url'][$strLength - 1] == '/' ? substr($formData['define_url'], 0, ($strLength - 1)) : $formData['define_url'];   
                     
                break;
            }            
        }     

        $pay_estimates = $this->getRequest()->getPost('pay_estimates');
        $cpm = $this->getRequest()->getPost('cpm');

        if($pay_estimates == 1 && !empty($cpm)){ $formData['cpm'] = $cpm; } else  $formData['cpm'] = 0;

        $io = $this->getRequest()->getPost('io');
        $rub_io = $this->getRequest()->getPost('rub_io');

        if($io == 1 && !empty($rub_io)){ $formData['rub_io'] = $rub_io; } else  $formData['rub_io'] = 0;            

        $objFloorPricing = $this->getRequest()->getParam('objFloorPricing');

        /* End input data */

        //$objGoogle = new My_Google_Site();
        $tableTags = new Application_Model_DbTable_Tags();
        $tableSiteApi = new Application_Model_DbTable_Sites_WantApi();
		/*
        require_once LIB_PATH.'/Alexa/alexa.scraper.php';
        $site = str_replace("http://", '', $formData['name']);
        $site = str_replace("https://", '', $site);
        $alexaData = Scrape($site);                                              
          */      
        /* Save data in DB */
        $siteInsertID = $tableTags->addSite($formData, $alexaData);

        if(count($objFloorPricing)>0){
                foreach($objFloorPricing as $item){

                        $item['price'] = str_replace(",", ".", $item['price']);
                        $item['percent'] = str_replace(",", ".", $item['percent']);

                        $tableTags->addSitePricing($formData['user'], $siteInsertID, $item['date'], $item['price'], $item['percent']);
                }
        }

        /* Create tags for site */
		/*
        $dataSiteApi = $tableSiteApi->createRow();
        $dataSiteApi->SiteID = $siteInsertID;
        $dataSiteApi->created = date('Y-m-d H:i:s');
        $dataSiteApi->creative_passback = $formData['creative_passback'];
        $dataSiteApi->save();
		*/
        //$objGoogle->placement($siteInsertID);

        /* Return result */

        $userTable = new Application_Model_DbTable_Users();
        $sql = $userTable->select()->from('users', array('account_manager_id'=>'users.account_manager_id'))->where("id='".$formData['user']."'");
        $dataUsers = $userTable->fetchRow($sql);
        $userAccManager = $dataUsers->account_manager_id;                        
                
        $SiteApprovalsTable = new Application_Model_DbTable_CoSiteApprovals();
        $tableManager = new Application_Model_DbTable_ContactNotification();
        
        if($userAccManager>0)
            $where = "id!=$userAccManager";
        else
            $where = "1=1";
        
        $SiteApprovalsTable->insert(array('SiteID'=>$siteInsertID, 'type'=> 'user', 'date_aded'=>date('Y-m-d'), 'manager'=>$tableManager->getIdByMail($auth->email) ));
        
        /*auto create tag with out api*/
        /*if($autoCreateTag == 6){
                        
            $dataTag = array('site_id' => $siteInsertID,
                             'user_id' => $formData['user'],
                             'type' => 6,
                             'rubiconType' => 1,
                             'site_name' => $formData['name']);
            
            $tableTags->save($dataTag);
            
            $classTag = new My_Tag('live', APPLICATION_ENV);           
            $classTag->generate($siteInsertID); 
        }*/
        /* end auto create tag */
        
        $this->view->result = array('SiteID' => $siteInsertID, 'SiteName' => $formData['name']);
        
    }
    
    
    public function confirmSiteAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();

        $auth = Zend_Auth::getInstance()->getIdentity();
        /* Get input data */

        $siteID = $this->_getParam('SiteID');
        /*$autoCreateTag = $this->_getParam('auto_create_tag');*/

        $formData = array('SiteName' => $this->_getParam('SiteName'),
                          'SiteURL'  => $this->_getParam('SiteURL'),
                          'category' => $this->_getParam('SiteCategory'),
                          'type'     => $this->_getParam('siteType'),
                          'status' => 3,
                          'admin_email'=>$auth->email);

                $arrSiteURL = preg_split('/[\n]/', $formData['SiteURL']); 
        foreach($arrSiteURL as $key => $SiteURL){
            
            if($SiteURL){ 
                
                $formData['define_url'] = strtolower($SiteURL);
                $formData['define_url'] = str_replace(array("https://", "http://", "www.", " "), "", $formData['define_url']);
                $strLength = strlen($formData['define_url']);                
                $formData['define_url'] = $formData['define_url'][$strLength - 1] == '/' ? substr($formData['define_url'], 0, ($strLength - 1)) : $formData['define_url'];   
                
                break;
            }            
        }

        /* End input data */

        $tableSite = new Application_Model_DbTable_Sites();

        /* Save data in DB */

        $resultConfirm = $tableSite->confirmSite($siteID, $formData);

        $this->view->result = $resultConfirm;

    }
    
    
    public function sentForApprovalAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $tagsModel = new Application_Model_DbTable_Tags();
               
        $this->view->result = $tagsModel->saveSent($this->_getParam('SiteID'), $this->_getParam('status'));
    }    
	
    
    public function optimizationUploadAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(); 
        
        $name = $this->_getParam('file');
        $path = $_SERVER['DOCUMENT_ROOT'].'/rubicon_csv/';
        $file = $path . $name;
        
        $ftell = $this->_getParam('ftell');
        $next = null;
        $data = array();
        
        $type = null;
        
        $tableSite = new Application_Model_DbTable_Sites();
        $tableOptimization = new Application_Model_DbTable_Rubicon_Optimization();
        
        
        if(is_file($file)){
        if(($handle_f = fopen($file, "r")) !== FALSE){
            
                $data['status'] = 'OK';
                $data['fileName'] = $name;
                
                if($ftell!=0){
                        fseek($handle_f,$ftell);
                }

                $i=0;

                while (($data_f = fgetcsv($handle_f, 1000, ","))!== FALSE) {
                   
                        if($data_f[0]!='Zone' && $data_f[0]!='Keyword'){
                           
                           if(count($data_f) == 9){ $type = 'zone';
                             
                                $data['sites'][$i]['Zone'] = $data_f[0];
                                $data['sites'][$i]['Date'] = $data_f[1];
                                $data['sites'][$i]['Impressions'] = $data_f[2];
                                $data['sites'][$i]['Paid_Impressions'] = $data_f[3];
                                $data['sites'][$i]['Rate'] = $data_f[4];
                                $data['sites'][$i]['Revenue'] = $data_f[5];
                                $data['sites'][$i]['eCPM'] = $data_f[6];

                                $siteInfo = $tableSite->getSiteInfoByName($data_f[0]);
                                if($siteInfo['SiteID']){
                                        $data['sites'][$i]['error'] = 'OK';
                                        $data['sites'][$i]['SiteID'] = $siteInfo['SiteID'];
                                        
                                        $tableOptimization->deleteData($data['sites'][$i]['SiteID'], $data['sites'][$i]['Date']);
                                        $tableOptimization->insertDataZone($data['sites'][$i]);

                                }else{
                                        $data['sites'][$i]['error'] = 'ERROR! Site not found';
                                }
                                
                           } else { $type = 'keyword';
                               
                                $data['sites'][$i]['Zone'] = $data_f[0];
                                $data['sites'][$i]['Date'] = $data_f[1];
                                $data['sites'][$i]['Impressions'] = $data_f[2];
                                
                                $siteInfo = $tableSite->getSiteInfoByName($data_f[0]);
                                if($siteInfo['SiteID']){
                                        $data['sites'][$i]['error'] = 'OK';
                                        $data['sites'][$i]['SiteID'] = $siteInfo['SiteID'];
                                        
                                        $tableOptimization->deleteData($data['sites'][$i]['SiteID'], $data['sites'][$i]['Date']);
                                        $tableOptimization->insertDataKeyword($data['sites'][$i]);

                                }else{
                                        $data['sites'][$i]['error'] = 'ERROR! Site not found';
                                }
                                                              
                           }

                                $i++;
                        
                        }

                        if($i==100){ $data['ftell'] = ftell($handle_f);
                                     $next = 1;
                                
                                     break;
                        }

                }

                fclose($handle_f);

                               $data['type'] = $type;
                if($next == 1) $data['next'] = 1;        			
                else{          $data['next'] = 0; unlink($file); }


        }else $data = array('error'=>'File not access');
        }else $data = array('error'=>'File not found', 'file' => $file); 

        $this->view->result = $data;
    }
    
    
    public function siteWantApiAction()
    {
        $SiteID = (int) $this->_getParam('SiteID');
        
        $tableSiteApi = new Application_Model_DbTable_Sites_WantApi();
        $sql = $tableSiteApi->select()->where('SiteID = ?', $SiteID);
        
        $dataExist = $tableSiteApi->fetchRow($sql);
        
        if($dataExist == false){
        
                $dataSiteApi = $tableSiteApi->createRow();
                $dataSiteApi->setFromArray(array(
                    'SiteID' => $SiteID,
                    'created' => date('Y-m-d H:i:s'),
                ));

                //$apiID = $dataSiteApi->save();
                
                $this->view->result = array('data' => 1, 'exist' => 0);
        } else  $this->view->result = array('data' => 0, 'exist' => 1); 
        
    }
    
    
    public function generateDueAction()
    {        
        $curentMonth = $this->_getParam('month') ? $this->_getParam('month') : date("n"); 
        $curentYear = $this->_getParam('year') ? $this->_getParam('year') : date("Y");  
        
        $tableCronDue = new Application_Model_DbTable_Cron_Due();
        
        $sqlCronDue = $tableCronDue->select()
                                   ->where('year = ?', $curentYear)
                                   ->where('month = ?', $curentMonth)
                                   ->where('status = 1');

        $dataCronDue = $tableCronDue->fetchRow($sqlCronDue);
        
        if(empty($dataCronDue)){
            
            $dataCronDue = $tableCronDue->createRow();
            $dataCronDue->setFromArray(array(
                'date' => date("Y-m-d H:i:s"),
                'year' => $curentYear,
                'month' => $curentMonth,
                'status' => 1,
            ));
            
            $dataCronDue->save();
            
                 $this->view->result = array('status' => 1);
        } else { $this->view->result = array('status' => 0); }         

    }
    
    public function deniedUrlHideAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();        

        $url = $this->_getParam('url');
        $value = $this->_getParam('value') ? "1" : "NULL";

        $dbAdapter = Zend_Db_Table::getDefaultAdapter(); 

        /*$sql = 'UPDATE madads_blocked SET hide = '.$value.' WHERE (url_full = "'.$url.'" OR src_full = "'.$url.'")';*/
        $sql = 'UPDATE madads_blocked SET hide = '.$value.' WHERE (url_full LIKE "%'.$url.'%" OR src_full LIKE "%'.$url.'%")';    

        $dbAdapter->query($sql);
        
        $this->view->result = true;
    }

    public function deniedSiteHideAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(); 

        $SiteID = $this->_getParam('SiteID');
        $value = $this->_getParam('value') ? "1" : "NULL";

        $dbAdapter = Zend_Db_Table::getDefaultAdapter(); 

        $sql = 'UPDATE madads_blocked SET hide = '.$value.' WHERE SiteID = "'.$SiteID.'"';     

        $dbAdapter->query($sql);
        
        $this->view->result = true;
    }    
        
    public function approvePsaUrlAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $url = $this->_getParam('url');
        $siteID = $this->_getParam('SiteID');
        
        $clearURL = $url;
        $url = str_replace("www.", "", $url);
                
        $tableSite = new Application_Model_DbTable_Sites();
        $tablePsa = new Application_Model_DbTable_Madads_Psa();
        
        $dataSite = $tableSite->getDataByID($siteID);
        
        if(strpos($dataSite['SiteURL'], $url) === false){
        
            $url = (strpos($url, "http://") === false) ? "http://".$url : $url;
            
            $dataSite['SiteURL'] .= $dataSite['SiteURL'] ? "\n".$url : $url;

            $tableSite->setSiteTable();
            $tableSite->update(array('SiteURL' => $dataSite['SiteURL']), 'SiteID = '.$siteID);

            $tablePsa->deleteData($siteID, $clearURL);
            
            $siteTagsModel = new Application_Model_DbTable_SitesTags();
            $siteTagsModel->changeAction($siteID, 'gen', APPLICATION_ENV);

        } else $tablePsa->deleteData($siteID, $clearURL);  
        
        $this->view->result = true;
        
    }
    
    public function fixedSiteAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $id = (int) $this->_getParam('id');
        
        $tableNotifi = new Application_Model_DbTable_Sites_NotifiMessage();
        
        $sql = $tableNotifi->select()->where('id = ?', $id);
        
        $dataNotifi = $tableNotifi->fetchRow($sql); 
        $dataNotifi->fixed = 1;
        
        $this->view->result = $dataNotifi->save(); 
    }
    
    public function saveOptimizationStatusAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $data = array('SiteID' => $this->_getParam('SiteID'),
                      'ValueClick' => $this->_getParam('ValueClick'),
                      'Advertising' => $this->_getParam('Advertising'),
                      'Media' => $this->_getParam('Media'),
                      'UnderDog' => $this->_getParam('UnderDog'),
                      'AudienceScience' => $this->_getParam('AudienceScience'));
        
        $tableCaseSite = new Application_Model_DbTable_Optimization_CaseSite();
        
        $this->view->result = $tableCaseSite->saveData($data);  
    }
    
    public function ajaxMakeCsvAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$params = $this->_getAllParams();    	
        if(is_array($params) AND count($params))
    	{
    		header('Content-Type: text/csv; charset=utf-8');
    		header('Content-Disposition: attachment; filename='.$params['csv_filename'].'.csv');
    		$output = fopen('php://output', 'w');
    		$csv_header = json_decode($params['csv_header']);
    		$csv_data = json_decode($params['csv_data']);
    		$output = fopen('php://output', 'w');
    		//CSV HEADER
    		fputcsv($output, $csv_header);
    		//CSV DATA
    		foreach($csv_data as $row)
    		{
    			foreach($row as &$item)
    			{
    				if(!$item OR $item == 'NULL' OR $item == 'Add Value')
    					$item = 0;	
    			}
    			fputcsv($output, $row);
    		}    		
    		fclose($output);
    	}
    }    

    public function setTaskStatusAction()
    {
        $this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
        
        $id = (int) $this->_getParam('id');
        $status = (int) $this->_getParam('status');
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity(); 
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        if($status){
            
            $sql = "INSERT INTO check_task_user (email, task_id, date) VALUES ('".$dataAuth->email."', '".$id."', '".date('Y-m-d')."') ON DUPLICATE KEY UPDATE email = '".$dataAuth->email."'";
            $dbAdapter->query($sql);
            
        } else {
            
            $sql = "DELETE FROM check_task_user WHERE email = '".$dataAuth->email."' AND task_id = '".$id."' AND date = '".date('Y-m-d')."'";
            $dbAdapter->query($sql);
        }  
        
        $this->view->result = true;
    }   
}