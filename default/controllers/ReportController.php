<?php

class ReportController extends Zend_Controller_Action 
{
        
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'report';                
          
    	$auth = Zend_Auth::getInstance()->getIdentity();
    	$sizeModel = new Application_Model_DbTable_Sizes();
    	$sitesModel = new Application_Model_DbTable_Sites();
        $reportModel = new Application_Model_DbTable_Report();
		$newsModel = new Application_Model_DbTable_UserNews();

		$this->view->news = $newsModel->getPreparedNews($auth, 1, 2);

    	$dirty_params = $this->getRequest()->getParams();
    	$params = array();
		foreach($dirty_params as $key => $value)
		{
			$params[$key] = $this->_helper->Xss->xss_clean($value);
			if($key == 'site')
			{
				$pattern = '/^(?:[;\/?:@&=+$,]|(?:[^\W_]|[-_.!~*\()\[\] ])|(?:%[\da-fA-F]{2}))*$/';
				if( preg_match( $pattern, $value ) != 1 ) {
					$params[$key] = '';
				}	
			}
			if($key == 'ad_size')
			{
				if(!is_numeric($value))
					$params[$key] = 0;
			}
		}
    	
    	$this->view->ad_sizes = $sizeModel->fetchAll(null, 'position');
    	//
    	$users = new Application_Model_DbTable_Users();
    	$userData = $users->getUserAllInfoById($auth->id);
    	if($userData['referral_system'])
    		$this->view->referral_system = true;
    	else 
    		$this->view->referral_system = false;
    	//
    	$this->view->sites = $sitesModel->getApprovedSitesByUserId($auth->id, false, 'sites.SiteName ASC');    	 
    	$this->view->floor_pricing = null;
    	$this->view->burst_tag = null;
    	$this->view->absEcpm = null;
        $this->view->auth = $auth;
    	$this->view->previosAllocted = null;
    	$this->view->previosImpressoin = null;
    	$sitesArray = array(0);
    	
    	$this->view->type = '';
    	$this->view->differentTypes = false;
    	
    	if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"])) {
    		if($this->view->sites){
	    		$tmpCountSites = 0;	    		
    			foreach($this->view->sites as $site){
    				$sitesArray[] = $site['SiteID'];
    				if($site['floor_pricing']==1)
    					$this->view->floor_pricing = 1;
    				if($site['tag_type']==6)
    					$this->view->burst_tag = 1;  
    					
    				if($this->view->type!=$site['tag_type'] && $tmpCountSites!=0){
	    				$this->view->type = $site['tag_type'];
    					$this->view->differentTypes = true;
    				}else
    					$this->view->type = $site['tag_type'];
    					
    				$tmpCountSites++;    					  					
    			}
    			
    		}
    	}else{
    		if($this->view->sites){
    			foreach($this->view->sites as $site){
    				if($params["site"]==$site['SiteID']){
		    			$this->view->type = $site['tag_type'];	    				
                        $this->view->siteID = $site['SiteID'];
    					$sitesArray[] = $site['SiteID'];
    					if($site['floor_pricing']==1)
    						$this->view->floor_pricing = 1;   						
    					//break;
    					if($site['tag_type']==6)
    						$this->view->burst_tag = 1;   						    					
    				}	
    			}
    			 
    		} 
    	}
    	
    	if (!isset($params["start_date"]) || !is_string($params["start_date"])) {
    		$params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
    	}
    	 
    	$params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));
    	 
    	if (!isset($params["end_date"]) || !is_string($params["end_date"])) {
    		$params["end_date"] = gmdate( 'm/d/Y',time());
    	}
    	 
    	$params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));
    	 
    	if (!isset($params["ad_size"]) || strlen($params["ad_size"]) < 1) {
    		$params["ad_size"] = null;
    	}
    	
    	$this->view->report_params = $params;
                
    	$finalUserReport = array();
        if(($auth->role=='admin' || $auth->role=='super') && $params['show_demand']==1){
            $finalUserReport = $reportModel->getUserReportShowDemand($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"]);
        }else
        {
        	//If referral is selected we get referral revenue by days
        	if($params['ad_size'] == -1)
        	{
        		$referral_model = new Application_Model_DbTable_Referral();
        		$finalUserReport = $referral_model->getReferralsReport($auth->email, $params['start_date'], $params['end_date']);
        	}
        	else
            	$finalUserReport = $reportModel->getUserReport($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"]);       
        }
    	$this->view->report = $finalUserReport;
    	
    	$this->view->tooltip = '';
    	
    	if(count($this->view->sites)>1 && $this->view->differentTypes===true){
	    	
	    	$this->view->tooltip = 'Your finalized impressions and revenue will be displayed 36 hours after the end of this date.';	
    	}else{
	    	if($this->view->type==8)
	    		$this->view->tooltip = 'Your finalized impressions and revenue will be displayed the following day at about 12:00 EST.';		
	    	elseif($this->view->type==5)
	    		$this->view->tooltip = 'Your finalized impressions and revenue will be displayed 36 hours after the end of this date.';
	    	elseif($this->view->type==6)
	    		$this->view->tooltip = 'Your finalized impressions and revenue will be displayed 36 hours after the end of this date.';
	    	else
				$this->view->tooltip = 'Your finalized impressions and revenue will be displayed 36 hours after the end of this date.';	    		
	    		
    	}
    	
    	
        /*
        //remove estimated data
    	$count = 0;
    	foreach ($finalUserReport as $key=>$item){
    		$count++;
    		if($count==8){
    			break;
    		}else{
    			if($item['estimated']==1){
    				unset($finalUserReport[$key]);
    			}
    		}
    	}
        
    	// abs ecpm
    	$ecpm = 0;   	
    	if(count($finalUserReport)>3){ 
    		$count = 0;
    	
    		foreach ($finalUserReport as $item){ 
    			if($count==0 && $item['paid_impressions'] && $item['impressions']){ 
                            $this->view->previosAllocted = $item['paid_impressions'];
                            $this->view->previosImpressoin = $item['impressions'];
    			}                    
    			$count++;
    			if($count==4){
                            break;
    			}else{  
                            if($item['revenue'] && $item['paid_impressions']){
                                $ecpm = $ecpm + ($item['revenue']*1000/$item['paid_impressions']);
                            }
    			}
                }
    	}
    	
    	if($ecpm>0) $this->view->absEcpm = round($ecpm/3, 2);
        */

        if(($auth->role=='admin' || $auth->role=='super') && $params['show_demand']==1){	
            if($this->view->floor_pricing==1)
                $this->render('index-tab-floor');
            else
                $this->render('index-tab-floor');
        }else{                         		                
            if($this->view->floor_pricing==1 || $this->view->burst_tag==1)
                $this->render('index-floor');
            else
                $this->render('index');
        }
    }
    
    public function indexbAction(){ 
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
        $layout->nav = 'admin';   
        $auth = Zend_Auth::getInstance()->getIdentity();
    	$sizeModel = new Application_Model_DbTable_Sizes();
    	$sitesModel = new Application_Model_DbTable_Sites();
        $reportModel = new Application_Model_DbTable_Report();
    	$params = $this->getRequest()->getParams();
    	
    	$this->view->ad_sizes = $sizeModel->fetchAll(null, 'position');
    	$this->view->sites = $sitesModel->getApprovedSitesByUserId($auth->id, false, 'sites.SiteName ASC');    	 
    	$this->view->floor_pricing = null;
    	$this->view->absEcpm = null;
        $this->view->auth = $auth;
    	$this->view->previosAllocted = null;
    	$this->view->previosImpressoin = null;
    	
    	$sitesArray = array(0);
    	
    	if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"])) {
    		if($this->view->sites){
    			foreach($this->view->sites as $site){
    				$sitesArray[] = $site['SiteID'];
    				if($site['floor_pricing']==1)
    					$this->view->floor_pricing = 1;
    			}
    			
    		}
    	}else{
    		if($this->view->sites){
    			foreach($this->view->sites as $site){
    				if($params["site"]==$site['SiteID']){
                                    $this->view->siteID = $site['SiteID'];
    					$sitesArray[] = $site['SiteID'];
    					if($site['floor_pricing']==1)
    						$this->view->floor_pricing = 1;   						
    					break;
    				}	
    			}
    			 
    		} 
    	}
    	
    	if (!isset($params["start_date"]) || !is_string($params["start_date"])) {
    		$params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
    	}
    	 
    	$params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));
    	 
    	if (!isset($params["end_date"]) || !is_string($params["end_date"])) {
    		$params["end_date"] = gmdate( 'm/d/Y',time());
    	}
    	 
    	$params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));
    	 
    	if (!isset($params["ad_size"]) || strlen($params["ad_size"]) < 1) {
    		$params["ad_size"] = null;
    	}
    	
    	$this->view->report_params = $params;
                
    	$finalUserReport = array();
        if(($auth->role=='admin' || $auth->role=='super') && $params['show_demand']==1){
            $finalUserReport = $reportModel->getUserReportShowDemand($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"]);
        }else{
            $finalUserReport = $reportModel->getUserReport($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"]);
        }
    	$this->view->report = $finalUserReport;
    }
  
    public function viewAction()
    {
        $layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
        
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $date = $this->getRequest()->getParam('date');
        $ad_size = $this->getRequest()->getParam('ad_size');
        $ad_size = $this->_helper->Xss->xss_clean($ad_size);
        if(!is_numeric($ad_size))
        	$ad_size = 0;
        
        $user_id = $auth->id;
                
        $date = date('Y-m-d', strtotime($date));
         
        $tableSite = new Application_Model_DbTable_Sites();
        $reportModel = new Application_Model_DbTable_Report();
        $sizeModel = new Application_Model_DbTable_Sizes();
           
        $sql = $tableSite->select()->setIntegrityCheck(false)
                         ->from(array('s' => 'sites'), array('s.SiteID', 's.floor_pricing'))
                         ->where('PubID = ?', $auth->id);
        
        $dataSite = $tableSite->fetchAll($sql, 's.SiteName');
               

        $siteIDs = array();
        $this->view->absEcpm = null;
        $this->view->floor_pricing = NULL;
        $this->view->previosAllocted = null;
    	$this->view->previosImpressoin = null;
         
        foreach($dataSite as $iter){
            
            $siteIDs[]= $iter['SiteID'];
            
            if($iter['floor_pricing'] == 1) $this->view->floor_pricing = 1;
            
        }   
                
        $dataReport = $reportModel->getReportByDate($siteIDs, $date, $ad_size);        
        
    	$ecpm = 0;                  
        
        $this->view->report = $dataReport;
        
        $this->view->curent = array('date' => $date, 'ad_size' => $ad_size);
                
        $this->view->ad_sizes = $sizeModel->fetchAll(null, 'position');        
        
        /*
        if(count($dataReport) && $dataReport[0]['estimated']){
            $ecpm = 0;
            $today = date('Y-m-d');
            $str1 = preg_split('/[-]/', $date);
            $str2 = preg_split('/[-]/', $today);
            $intDate1 = mktime(0, 0, 0, $str2[1], $str2[2], $str2[0]); 
            $intDate2 = mktime(0, 0, 0, $str2[1], $str2[2] - 12, $str2[0]);

            $dataFinal = $reportModel->getUserReport($siteIDs, date('m/d/Y', $intDate2), date('m/d/Y', $intDate1), $ad_size);
            
            foreach($dataFinal as $key => $iter){ if($iter['estimated']) unset($dataFinal[$key]); }
            
            if(count($dataFinal) > 3){
                $count = 0;
                foreach($dataFinal as $key => $item){ 
                    if($count==0 && $item['paid_impressions'] && $item['impressions']){ 
                        $this->view->previosAllocted = $item['paid_impressions'];
                        $this->view->previosImpressoin = $item['impressions'];
                    }                    
                    $count++; if($count > 3) break;                   
                    if($item['revenue'] && $item['paid_impressions']){ 
                        $ecpm = $ecpm + ($item['revenue']*1000/$item['paid_impressions']);
                    } 

                }
            }
            if($ecpm>0) $this->view->absEcpm = round($ecpm/3, 2);
        }
        */
        
        if($this->view->floor_pricing==1)
            $this->render('view-floor');
        else
            $this->render('view');
        
    }
    
    public function csvAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        header('Content-Type: text/csv'); 
        header("Content-Disposition: attachment; filename=report.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        
    	$auth = Zend_Auth::getInstance()->getIdentity();
        $sitesModel = new Application_Model_DbTable_Sites();
        $reportModel = new Application_Model_DbTable_Report();
    	$params = $this->getRequest()->getParams();
        
        $Content = "";
        $sites = $sitesModel->getApprovedSitesByUserId($auth->id, false, 'sites.SiteName ASC');
    	$floor_pricing = null;
    	$absEcpm = null;
    	$previosAllocted = null;
    	$previosImpressoin = null;
        
        
    	$sitesArray = array(0);
    	
    	if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"]) || $params['site']==0) {
    		if($sites){
    			foreach($sites as $site){
    				$sitesArray[] = $site['SiteID'];
    				if($site['floor_pricing']==1)
    					$floor_pricing = 1;
    			}
    			
    		}
    	}else{
    		if($sites){
    			foreach($sites as $site){
    				if($params["site"]==$site['SiteID']){
    					$sitesArray[] = $site['SiteID'];
    					if($site['floor_pricing']==1)
    						$floor_pricing = 1;   						
    					break;
    				}	
    			}
    			 
    		} 
    	}
    	
    	if (!isset($params["start_date"]) || !strtotime($params["start_date"])) {
    		$params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
    	}
    	 
    	$params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));
    	 
    	if (!isset($params["end_date"]) || !strtotime($params["end_date"])) {
    		$params["end_date"] = gmdate( 'm/d/Y',time());
    	}
    	 
    	$params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));
    	 
    	if (!isset($params["ad_size"]) || !is_int($params["ad_size"]) || $params['ad_size']==0) {
    		$params["ad_size"] = null;
    	}
    	        
        /*
    	$finalUserReport = array();
        $finalUserReport = $reportModel->getUserReport($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"]);
        
        //remove estimated data
    	$count = 0;
    	foreach ($finalUserReport as $key=>$item){
    		$count++;
    		if($count==8){
    			break;
    		}else{
    			if($item['estimated']==1){
    				unset($finalUserReport[$key]);
    			}
    		}
    	}
        
    	// abs ecpm
    	$ecpm = 0;   	
    	if(count($finalUserReport)>3){ 
    		$count = 0;
    	
    		foreach ($finalUserReport as $item){ 
    			if($count==0 && $item['paid_impressions'] && $item['impressions']){ 
                            $previosAllocted = $item['paid_impressions'];
                            $previosImpressoin = $item['impressions'];
    			}                    
    			$count++;
    			if($count==4){
                            break;
    			}else{  
                            if($item['revenue'] && $item['paid_impressions']){
                                $ecpm = $ecpm + ($item['revenue']*1000/$item['paid_impressions']);
                            }
    			}
                }
    	}
    	
    	if($ecpm>0) $absEcpm = round($ecpm/3, 2);        
        */
        
    	$finalUserReportCsv = array();        
    	if($params['break_size']==1)
    		$finalUserReportCsv = $reportModel->getUserReportCsv($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"], 1);
    	else
        	$finalUserReportCsv = $reportModel->getUserReportCsv($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"]);
        
        
        if($floor_pricing==1){
            if($params['break_size']==1)
            	$Content = "Date, Site Name, Ad Size, Impressions, Paid Impressions, CPM, Revenue \n";
            else
            	$Content = "Date, Site Name, Impressions, Paid Impressions, CPM, Revenue \n";	        
            if($finalUserReportCsv){
                $impressions = 0;
                $paid_impressions = 0;
                $revenue = 0;
                $ecpm = 0;

                $count = 0;
                $asterics = '';
                $pending = false;

                foreach ($finalUserReportCsv as $item) {
                    $count++;
                    $impressions = $item['impressions'];
                    $paid_impressions = $item['paid_impressions'];
                    $revenue = $item['revenue'];
                    $ecpm = '-';
                    $pending = false;

                    if($params['break_size']==1)
                    	$adSizeName = $item['AdSizeName'];

                    if($item['estimated']==1){
	                    if($item['type']==6 || $item['type']==7 || $item['type']==8){
		                	$ecpm = '-';
		                	$revenue=0;
		                	$pending=true;
		                	$asterics = '*';										                    
	                    }else{
	                        $asterics='*';
	                        if($paid_impressions>0 && $revenue>0){
	                            $ecpm = $revenue*1000/$paid_impressions;
	                        }elseif($absEcpm>0 && $paid_impressions>0){
	                            $ecpm = $absEcpm;
	                            $revenue = $absEcpm*$paid_impressions/1000;                    
	                        }else{
	                            $pending = true;
	                        }                    		                    
	                    }
                    }else{
                        $asterics='';
                        if($impressions>0 && $revenue>0)
                            $ecpm = $revenue*1000/$paid_impressions;
                    }
                    
                    $finalDate = $item['query_date'].$asterics;
                    $finalSite = $item['SiteName'];
                    $finalImpressions = !$impressions ? '-'.$asterics : round($impressions).$asterics;
                    //$finalPaidImpressions = !$paid_impressions ? '-'.$asterics : round($paid_impressions).$asterics;
					$finalPaidImpressions = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($paid_impressions).$asterics;                    
                    $finalEcpm = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($ecpm,2).$asterics;
                    $finalRevenue = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($revenue,2).$asterics;
                    
                    if($params['break_size']==1)
                    	$Content.="$finalDate,$finalSite,$adSizeName,$finalImpressions,$finalPaidImpressions,$finalEcpm,$finalRevenue\n";
                    else
                    	$Content.="$finalDate,$finalSite,$finalImpressions,$finalPaidImpressions,$finalEcpm,$finalRevenue\n";
                }
            }            
        }  else {
            if($params['break_size']==1)
            	$Content = "Date, Site Name, Ad Size, Impressions, CPM, Revenue \n";
            else
            	$Content = "Date, Site Name, Impressions, CPM, Revenue \n";	        	        
            if($finalUserReportCsv){
                $impressions = 0;
                $revenue = 0;
                $ecpm = 0;

                $count = 0;
                $asterics = '';
                $pending = false;

                foreach ($finalUserReportCsv as $item) {
                    /*
                    if($item['impressions'] && $item['estimated']==1){
                        if($previosAllocted>0 && $previosImpressoin>0){
                            $item['impressions'] = ($previosAllocted/$previosImpressoin)*$item['impressions'];
                            $item['impressions'] = round($item['impressions']);
                        }
                    }
                    */
                    $count++;
                    $impressions = $item['impressions'];
                    $revenue = $item['revenue'];
                    $ecpm = '-';
                    $pending = false;

	                if($params['break_size']==1)
	                	$adSizeName = $item['AdSizeName'];

                    if($item['estimated']==1){
	                    if($item['type']==6 || $item['type']==7 || $item['type']==8){
		                	$ecpm = '-';
		                	$revenue=0;
		                	$pending=true;
		                	$asterics = '*';										                    
	                    }else{
	                        $asterics='*';
	                        if($paid_impressions>0 && $revenue>0){
	                            $ecpm = $revenue*1000/$paid_impressions;
	                        }elseif($absEcpm>0 && $paid_impressions>0){
	                            $ecpm = $absEcpm;
	                            $revenue = $absEcpm*$paid_impressions/1000;                    
	                        }else{
	                            $pending = true;
	                        }                    		                    
	                    }
                    }else{
                        $asterics='';
                        if($impressions>0 && $revenue>0)
                            $ecpm = $revenue*1000/$impressions;
                    }
                    
                    $finalDate = $item['query_date'].$asterics;
                    $finalSite = $item['SiteName'];
                    $finalImpressions = !$impressions ? '-'.$asterics : round($impressions).$asterics;
                    $finalEcpm = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($ecpm,2).$asterics;
                    $finalRevenue = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($revenue,2).$asterics;
          
                    if($params['break_size']==1)
                    	$Content.="$finalDate,$finalSite,$adSizeName,$finalImpressions,$finalEcpm,$finalRevenue\n";
                    else
                    	$Content.="$finalDate,$finalSite,$finalImpressions,$finalEcpm,$finalRevenue\n";                    
                }
            }
        }
        
        echo $Content;        
        //print_r($finalUserReport);
    }
    
    public function csvviewAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        header('Content-Type: text/csv'); 
        header("Content-Disposition: attachment; filename=report.csv");
        header("Pragma: no-cache");
        header("Expires: 0");        
        
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $date = $this->getRequest()->getParam('date');
        $ad_size = (int)$this->getRequest()->getParam('ad_size');
        $user_id = $auth->id;
                
        $date = date('Y-m-d', strtotime($date));
         
        $tableSite = new Application_Model_DbTable_Sites();
        $reportModel = new Application_Model_DbTable_Report();
           
        $sql = $tableSite->select()->setIntegrityCheck(false)
                         ->from(array('s' => 'sites'), array('s.SiteID', 's.floor_pricing'))
                         ->where('PubID = ?', $auth->id);
        
        $dataSite = $tableSite->fetchAll($sql, 's.SiteName');
               
        $Content = "";
        $siteIDs = array();
        $absEcpm = null;
        $floor_pricing = NULL;
        $previosAllocted = null;
    	$previosImpressoin = null;
         
        foreach($dataSite as $iter){
            
            $siteIDs[]= $iter['SiteID'];
            
            if($iter['floor_pricing'] == 1) $floor_pricing = 1;
            
        }   
                
       $dataReport = $reportModel->getReportByDate($siteIDs, $date, $ad_size);        
        
    	$ecpm = 0;                  
                
        $curent = array('date' => $date, 'ad_size' => $ad_size);
        /*                
        if(count($dataReport) && $dataReport[0]['estimated']){
            $ecpm = 0;
            $today = date('Y-m-d');
            $str1 = preg_split('/[-]/', $date);
            $str2 = preg_split('/[-]/', $today);
            $intDate1 = mktime(0, 0, 0, $str2[1], $str2[2], $str2[0]); 
            $intDate2 = mktime(0, 0, 0, $str2[1], $str2[2] - 12, $str2[0]);

            $dataFinal = $reportModel->getUserReport($siteIDs, date('m/d/Y', $intDate2), date('m/d/Y', $intDate1), $ad_size);
            
            foreach($dataFinal as $key => $iter){ if($iter['estimated']) unset($dataFinal[$key]); }
            
            if(count($dataFinal) > 3){
                $count = 0;
                foreach($dataFinal as $key => $item){ 
                    if($count==0 && $item['paid_impressions'] && $item['impressions']){ 
                        $previosAllocted = $item['paid_impressions'];
                        $previosImpressoin = $item['impressions'];
                    }                    
                    $count++; if($count > 3) break;                   
                    if($item['revenue'] && $item['paid_impressions']){ 
                        $ecpm = $ecpm + ($item['revenue']*1000/$item['paid_impressions']);
                    } 

                }
            }
            if($ecpm>0) $absEcpm = round($ecpm/3, 2);
        }
        */
        if($floor_pricing==1){
            $Content = "Date, Site Name, Impressions, Paid Impressions, CPM, Revenue \n";
            if($dataReport){
                $impressions = 0;
                $paid_impressions = 0;
                $revenue = 0;
                $ecpm = 0;

                $count = 0;
                $asterics = '';
                $pending = false;

                foreach ($dataReport as $item) {
                    $count++;
                    $impressions = $item['impressions'];
                    $paid_impressions = $item['paid_impressions'];
                    $revenue = $item['revenue'];
                    $ecpm = '-';
                    $pending = false;

                    if($item['estimated']==1){
						if($item['type']==6 || $item['type']==7 || $item['type']==8){
		                	$ecpm = '-';
		                	$revenue=0;
		                	$pending=true;
		                	$asterics = '*';
	                	}else{	                    
	                        $asterics='*';
	                        if($paid_impressions>0 && $revenue>0){
	                            $ecpm = $revenue*1000/$paid_impressions;
	                        }elseif($absEcpm>0 && $paid_impressions>0){
	                            $ecpm = $absEcpm;
	                            $revenue = $absEcpm*$paid_impressions/1000;                    
	                        }else{
	                            $pending = true;
	                        }     
                        }               
                    }else{
                        $asterics='';
                        if($impressions>0 && $revenue>0)
                            $ecpm = $revenue*1000/$paid_impressions;
                    }
                    
                    $finalDate = $item['query_date'].$asterics;
                    $finalSite = $item['SiteName'];
                    $finalImpressions = !$impressions ? '-'.$asterics : round($impressions).$asterics;
                    //$finalPaidImpressions = !$paid_impressions ? '-'.$asterics : round($paid_impressions).$asterics;
                    $finalPaidImpressions = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($paid_impressions).$asterics;
                    $finalEcpm = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($ecpm,2).$asterics;
                    $finalRevenue = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($revenue,2).$asterics;
                    
                    $Content.="$finalDate,$finalSite,$finalImpressions,$finalPaidImpressions,$finalEcpm,$finalRevenue\n";
                }
            }            
        }  else {
            $Content = "Date, Site Name, Impressions, CPM, Revenue \n";
            if($dataReport){
                $impressions = 0;
                $revenue = 0;
                $ecpm = 0;

                $count = 0;
                $asterics = '';
                $pending = false;

                foreach ($dataReport as $item) {
                    /*
                    if($item['impressions'] && $item['estimated']==1){
                        if($previosAllocted>0 && $previosImpressoin>0){
                            $item['impressions'] = ($previosAllocted/$previosImpressoin)*$item['impressions'];
                            $item['impressions'] = round($item['impressions']);
                        }
                    }
                    */
                    $count++;
                    $impressions = $item['impressions'];
                    $revenue = $item['revenue'];
                    $ecpm = '-';
                    $pending = false;

                    if($item['estimated']==1){
						if($item['type']==6 || $item['type']==7 || $item['type']==8){
		                	$ecpm = '-';
		                	$revenue=0;
		                	$pending=true;
		                	$asterics = '*';
	                	}else{	                    	                    
	                        $asterics='*';
	                        if($impressions>0 && $revenue>0){
	                            $ecpm = $revenue*1000/$impressions;
	                        }elseif($absEcpm>0 && $impressions>0){
	                            $ecpm = $absEcpm;
	                            $revenue = $absEcpm*$impressions/1000;                    
	                        }else{
	                            $pending = true;
	                        }                   
	                    }
                    }else{
                        $asterics='';
                        if($impressions>0 && $revenue>0)
                            $ecpm = $revenue*1000/$impressions;
                    }
                    
                    $finalDate = $item['query_date'].$asterics;
                    $finalSite = $item['SiteName'];
                    $finalImpressions = !$impressions ? '-'.$asterics : round($impressions).$asterics;
                    $finalEcpm = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($ecpm,2).$asterics;
                    $finalRevenue = $ecpm=='-' ? $pending ? 'Pending' : '-'.$asterics : round($revenue,2).$asterics;
                    
                    $Content.="$finalDate,$finalSite,$finalImpressions,$finalEcpm,$finalRevenue\n";
                }
            }
        }
        
        echo $Content;        
    }
    
    public function sizeAction()
    {
        $layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
        
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $date = $this->getRequest()->getParam('date');
        $site_id = (int)$this->getRequest()->getParam('site');
                
        $date = date('Y-m-d', strtotime($date));
         
        $tableSite = new Application_Model_DbTable_Sites();
        $reportModel = new Application_Model_DbTable_Report();
        $sizeModel = new Application_Model_DbTable_Sizes();
           
        $sql = $tableSite->select()->setIntegrityCheck(false)
                         ->from(array('s' => 'sites'), array('s.SiteID', 's.floor_pricing', 's.SiteName'))
                         ->where('PubID = ?', $auth->id)
                         ->where('SiteID = ?', $site_id);
        
        $dataSite = $tableSite->fetchRow($sql);
               
        $this->view->floor_pricing = $dataSite['floor_pricing'];
               
        $dataReport = $reportModel->getReportBySize($site_id, $auth->id, $date);        
 
        $this->view->report = $dataReport;        
        $this->view->curent = array('date' => $date, 'site' => $site_id);
        $this->view->site = $dataSite;

        if($this->view->floor_pricing==1)
            $this->render('size-floor');
        else
            $this->render('size');
                
    }
    
    public function editAction()
    {
        $params = $this->getRequest()->getParams();
        if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"])) {
            $this->_redirect('/report/');
        }else{
            $layout = Zend_Layout::getMvcInstance();
            $layout->setLayout('reports');
            $layout->nav = 'report';                

            $auth = Zend_Auth::getInstance()->getIdentity();
            $sizeModel = new Application_Model_DbTable_Sizes();
            $sitesModel = new Application_Model_DbTable_Sites();
            $reportModel = new Application_Model_DbTable_Report();


            $this->view->ad_sizes = $sizeModel->fetchAll(null, 'position');
            $this->view->sites = $sitesModel->getApprovedSitesByUserId($auth->id, false, 'sites.SiteName ASC');    	 
            $this->view->floor_pricing = null;
            $this->view->auth = $auth;

            if($this->view->sites){
                    foreach($this->view->sites as $site){
                            if($params["site"]==$site['SiteID']){
                                    $this->view->siteID = $site['SiteID'];
                                    if($site['floor_pricing']==1)
                                            $this->view->floor_pricing = 1;   						
                                    break;
                            }	
                    }

            } 
            
            
            if (!isset($params["start_date"]) || !is_string($params["start_date"])) {
                    $params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
            }

            $params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));

            if (!isset($params["end_date"]) || !is_string($params["end_date"])) {
                    $params["end_date"] = gmdate( 'm/d/Y',time());
            }

            $params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));

            if (!isset($params["ad_size"]) || strlen($params["ad_size"]) < 1) {
                    $params["ad_size"] = null;
            }

            $this->view->report_params = $params;
            
            if($this->getRequest()->getPost()){
                if($this->getRequest()->getPost('item')){
                    foreach ($this->getRequest()->getPost('item') as $item){
                        $values = explode("_", $item);
                        /*
                        if($this->getRequest()->getPost('impres_'.$item)!=$this->getRequest()->getPost('oldImpres_'.$item) || $this->getRequest()->getPost('paid_'.$item)!=$this->getRequest()->getPost('oldpaid_'.$item) || $this->getRequest()->getPost('c'.$item)!=$this->getRequest()->getPost('oldReven_'.$item)){
                            $reportModel->update(array('impressions'=>$this->getRequest()->getPost('impres_'.$item), 'paid_impressions'=>$this->getRequest()->getPost('paid_'.$item), 'revenue'=>$this->getRequest()->getPost('reven_'.$item), 'impressions_manual'=>$this->getRequest()->getPost('impres_'.$item), 'paid_impressions_manual'=>$this->getRequest()->getPost('paid_'.$item), 'revenue_manual'=>$this->getRequest()->getPost('reven_'.$item), 'updated'=>1), "SiteID='".$params["site"]."' AND query_date='".$values[0]."' AND AdSize='".$values[1]."'");
                        }
                        */
                        if($this->getRequest()->getPost('impres_'.$item)!=$this->getRequest()->getPost('oldImpres_'.$item))
                        $reportModel->update(array('impressions'=>$this->getRequest()->getPost('impres_'.$item), 'impressions_manual'=>$this->getRequest()->getPost('impres_'.$item), 'updated'=>1), "SiteID='".$params["site"]."' AND query_date='".$values[0]."' AND AdSize='".$values[1]."'");
                        
                        if($this->getRequest()->getPost('paid_'.$item)!=$this->getRequest()->getPost('oldpaid_'.$item))
                        $reportModel->update(array('paid_impressions'=>$this->getRequest()->getPost('paid_'.$item), 'paid_impressions_manual'=>$this->getRequest()->getPost('paid_'.$item), 'updated'=>1), "SiteID='".$params["site"]."' AND query_date='".$values[0]."' AND AdSize='".$values[1]."'");

                        if($this->getRequest()->getPost('reven_'.$item)!=$this->getRequest()->getPost('oldReven_'.$item))
                        $reportModel->update(array('revenue'=>$this->getRequest()->getPost('reven_'.$item), 'revenue_manual'=>$this->getRequest()->getPost('reven_'.$item), 'updated'=>1), "SiteID='".$params["site"]."' AND query_date='".$values[0]."' AND AdSize='".$values[1]."'");                        
                    }
                }
                $this->_redirect('/report/');
            }
            
            
            $finalUserReport = array();
            $finalUserReport = $reportModel->getUserReportEdit($params["site"], $params["start_date"], $params["end_date"], $params["ad_size"]);
            $this->view->report = $finalUserReport;
        }
    }
    

    public function viewallnewsAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$news_model = new Application_Model_DbTable_UserNews();
    	$auth = Zend_Auth::getInstance()->getInstance()->getIdentity();
    	$data = $news_model->getPreparedNews($auth);
    	$this->view->news = $data;
    }
    
    public function setAutoReportAction()
    {
        $this->_helper->layout()->disableLayout(); 
        
        $start_date	= $this->_helper->Xss->xss_clean($this->_getParam('start_date'));
        $end_date	= $this->_helper->Xss->xss_clean($this->_getParam('end_date'));
        $ad_size	= (int)$this->_helper->Xss->xss_clean($this->_getParam('ad_size'));
        $site		= (int)$this->_helper->Xss->xss_clean($this->_getParam('site'));
        $break_size	= (int)$this->_helper->Xss->xss_clean($this->_getParam('break_size'));
        
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
                
        $report_params = array('start_date' => $start_date, 'end_date' => $end_date, 'ad_size' => $ad_size, 'site' => $site, 'break_size'=>$break_size);
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $session = new Zend_Session_Namespace('Default');

        if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }        
        
        $tableCsv = new Application_Model_DbTable_CsvReport();
        $tableTimezone = new Application_Model_DbTable_Timezone();

        $sql = $tableCsv->select()->where('PubID = ?', $dataAuth->id);
        $dataUserCsv = $tableCsv->fetchRow($sql);
        
        $dataZones = $tableTimezone->fetchAll(null, 'name');
        
        $form = new Application_Form_ReportCsv();

        $arrUtc = array();
        $arrUtcVal = array();
        foreach($dataZones as $zone){
	        $arrUtc[$zone['id']] = $zone['name'];
	        $arrUtcVal[$zone['id']] = $zone['value'];
        }
        $form->getElement('utc')->addMultiOptions($arrUtc);
		$this->view->zones = $arrUtc;
		        
        if($this->getRequest()->isPost()){
                     
                    $formData = array();            
                    $dirtyData = $this->getRequest()->getPost();            
            foreach($dirtyData as $key => $value){ $formData[$key] = $this->_helper->Xss->xss_clean($value); }     
                    
                    $arrEmail = preg_split("[,]", $formData['email']);
                    $arrEmailvalid = array();
            foreach($arrEmail as $iterEmail){
                
                              $iterEmail = trim($iterEmail);
                if(filter_var($iterEmail, FILTER_VALIDATE_EMAIL)){ $arrEmailvalid[] = $iterEmail; }     
                
            } $formData['email'] = count($arrEmailvalid) ? implode(",", $arrEmailvalid) : "";
            
            if($form->isValid($formData) AND $this->_helper->Csrf->check_token($formData['csrf'])){
            
                $utc = $formData['utc'];
                $dataCsv = array('PubID'   => $dataAuth->id,
                                 'email'   => $formData['email'],
                                 'period'  => $formData['period'],
                                 'when'	=> $formData['when'],                                 
                                 'time'	=> $formData['time'],                                 
                                 'utc'	=> $arrUtcVal[$utc],                                                                  
                                 'utc_id'     => $utc,                                 
                                 'break_size' => $formData['break_size'],
                                 'created' => date('Y-m-d H:i:s')); 

				$time = mktime($dataCsv['time']-$dataCsv['utc'],0,0,date("m"),date("d"),date("Y"));
				
				$dataCsv['start_time_utc'] = date("G", $time);
                
				if($dataUserCsv->PubID)
					$tableCsv->update($dataCsv, "PubID=".$dataAuth->id);
				else  	$tableCsv->insert($dataCsv); 
               
				$session->message = 'Your report has been scheduled.';
                	
                $this->_redirect('/default/report/set-auto-report/start_date/'.$report_params['start_date'].'/end_date/'.$report_params['end_date'].'/ad_size/'.$report_params['ad_size'].'/site/'.$report_params['site']);                
            } else {
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $formData;       
            }
            
        } else {
        	if(!empty($dataUserCsv->PubID)){
	        	$this->view->formValues = array(
	        		'break_size'=>$dataUserCsv->break_size,
	        		'email'=>$dataUserCsv->email,
	        		'period'=>$dataUserCsv->period,
	        		'when'=>$dataUserCsv->when,
	        		'time'=>$dataUserCsv->time,
	        		'utc'=>$dataUserCsv->utc_id,
	        		'dis'=>1
	        	);
        	}else{
	            $this->view->formValues = array('break_size'=>'', 'period'=>'', 'when'=>'', 'time'=>'', 'utc'=>'', 'email'=>'');
	        }
        }                 
        
        $this->view->form = $form;
        $this->view->PubID = $dataAuth->id;
        $this->view->report_params = $report_params;
        $this->view->csrf = $this->_helper->Csrf->set_token();
    }
    
    public function delAutoReportAction()
    {
	    $this->_helper->layout()->disableLayout();
	    
	    $PubID = (int)$this->_getParam('id');
	    $dataAuth = Zend_Auth::getInstance()->getIdentity();
	    
	    if($dataAuth->id == $PubID){
	    	$tableCsv = new Application_Model_DbTable_CsvReport();
	        $sql = $tableCsv->select()
	                             ->where('PubID = ?', $PubID);
	        
	        $dataCsv = $tableCsv->fetchRow($sql);
	        $dataCsv->delete();
	    	
		    $this->view->message = 'Daily reports are disabled';
	    }else{
		    $this->view->message = 'This publisher ID doesn\'t exist';
	    }
    }

    public function betaAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'reportBeta';

        $auth = Zend_Auth::getInstance()->getIdentity();

        $sizeModel = new Application_Model_DbTable_Sizes();
        $sitesModel = new Application_Model_DbTable_Sites();
        $reportModel = new Application_Model_DbTable_UsersReport();

        $this->view->ad_sizes = $sizeModel->fetchAll(null, 'position');
        $this->view->sites = $sitesModel->getApprovedSitesByUserId($auth->id, false, 'sites.SiteName ASC');

        $this->view->rangeToday = date("M j, Y", time()).'_'.date("M j, Y", time());
        $this->view->rangeYest = date("M j, Y", time()-86400).'_'.date("M j, Y", time()-86400);
        $this->view->rangeLast7 = date("M j, Y", time()-86400*7).'_'.date("M j, Y", time());
        $this->view->rangeLast30 = date("M j, Y", time()-86400*30).'_'.date("M j, Y", time());
        $this->view->rangeThisMonth = date("M j, Y", time()-86400*(date("j")-1)).'_'.date("M j, Y", time());
        $this->view->rangePrevMonth = date("M j, Y", mktime(0,0,0,date("n")-1,1,date("Y"))).'_'.date("M j, Y", mktime(0,0,0,date("n"),0,date("Y")));
        $this->view->rangeAll = $reportModel->getFirstDate($auth->id).'_'.date("M j, Y", time());
    }

    public function betaAjaxAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $reportModel = new Application_Model_DbTable_UsersReport();
        $EstimatedModel = new Application_Model_DbTable_EstimatedReport();
        $NonfilleModel = new Application_Model_DbTable_NonfilleReport();
        $PsaModel = new Application_Model_DbTable_Psa();

        $auth = Zend_Auth::getInstance()->getIdentity();

        $ad_size = (int)$this->_getParam('ad_size');
        $site = (int)$this->_getParam('site');

        $date_from = DateTime::createFromFormat('M j, Y', $this->_getParam('dateStart'));
        $date_from->setTime(0,0,0);
        $date_to = DateTime::createFromFormat('M j, Y', $this->_getParam('dateFinish'));
        $date_to->setTime(23,59,59);

        $dateToEstim = DateTime::createFromFormat('M j, Y', $this->_getParam('dateFinish'));
        $dateToEstim->sub(new DateInterval('P5D'));

        if($auth->id && $date_from && $date_to){
            $result['status'] = 'OK';
            $dataEstimated = $EstimatedModel->getReport($auth->id, $dateToEstim->format('Y-m-d'), $date_to->format('Y-m-d'), $site, $ad_size);
            $dataNonfille = $NonfilleModel->getReport($auth->id, $dateToEstim->format('Y-m-d'), $date_to->format('Y-m-d'), $site, $ad_size);
            $dataPsa = $PsaModel->getReport($auth->id, $dateToEstim->format('Y-m-d'), $date_to->format('Y-m-d'), $site, $ad_size);
            $dataReport = $reportModel->getReport($auth->id, $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $site, $ad_size);

            $interval = new DateInterval('P1D');
            $daterange = new DatePeriod($date_from, $interval ,$date_to);

            $totalImpressions = 0;
            $totalPaidImpressions = 0;
            $totalPaidRevenue = 0.00;
            
            foreach($daterange as $date){
                if(isset($dataReport[$date->format("Y-m-d")])){
                    $totalImpressions +=$dataReport[$date->format("Y-m-d")]['impressions'];
                    $totalPaidImpressions +=$dataReport[$date->format("Y-m-d")]['paid_impressions'];
                    $totalPaidRevenue +=$dataReport[$date->format("Y-m-d")]['revenue_paid'];
                    $result['report'][$date->format("Y-m-d")] = [
                        'date'=>$date->format("Y-m-d"),
                        'impressions'=>number_format($dataReport[$date->format("Y-m-d")]['impressions'], 0, '.', ','),
                        'paid_impressions'=>number_format($dataReport[$date->format("Y-m-d")]['paid_impressions'], 0, '.', ','),
                        'cpm'=>'$'.number_format($dataReport[$date->format("Y-m-d")]['revenue_paid']*1000/$dataReport[$date->format("Y-m-d")]['paid_impressions'], 2, '.', ','),
                        'revenue_paid'=>'$'.number_format($dataReport[$date->format("Y-m-d")]['revenue_paid'], 2, '.', ',')
                    ];
                }elseif(isset($dataEstimated[$date->format("Y-m-d")])){
                    $totalImpressions +=$dataEstimated[$date->format("Y-m-d")]['impressions']-$dataPsa[$date->format("Y-m-d")]['impressions'];
                    $totalPaidImpressions +=$dataEstimated[$date->format("Y-m-d")]['impressions']-$dataNonfille[$date->format("Y-m-d")]['impressions']-$dataPsa[$date->format("Y-m-d")]['impressions'];
                    $totalPaidRevenue +=0;

                    $result['report'][$date->format("Y-m-d")] = [
                        'date'=>$date->format("Y-m-d"),
                        'impressions'=>$dataEstimated[$date->format("Y-m-d")]['impressions']-$dataPsa[$date->format("Y-m-d")]['impressions'].'*',
                        'paid_impressions'=>$dataEstimated[$date->format("Y-m-d")]['impressions']-$dataNonfille[$date->format("Y-m-d")]['impressions']-$dataPsa[$date->format("Y-m-d")]['impressions'].'*',
                        'cpm'=>'Pending',
                        'revenue_paid'=>'Pending'
                    ];
                }else{
                    $result['report'][$date->format("Y-m-d")] = [
                        'date'=>$date->format("Y-m-d"),
                        'impressions'=>'-',
                        'paid_impressions'=>'-',
                        'cpm'=>'-',
                        'revenue_paid'=>'-'
                    ];
                }
            }
            $result['report'] = array_reverse($result['report']);
            $result['total'] = [
                'totalImpressions'=>number_format($totalImpressions, 0, '.', ','),
                'totalPaidImpressions'=>number_format($totalPaidImpressions, 0, '.', ','),
                'totalCpm'=>'$'.number_format($totalPaidRevenue*1000/$totalPaidImpressions, 2, '.', ','),
                'totalPaidRevenue'=>'$'.number_format($totalPaidRevenue, 2, '.', ',')
            ];

        }else{
            $result['status'] = 'ERROR!!!';
        }


        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
}