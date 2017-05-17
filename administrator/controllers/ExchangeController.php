<?php


class Administrator_ExchangeController extends Zend_Controller_Action
{
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'exchange';      
        
    	$sizeModel = new Application_Model_DbTable_Sizes();
    	$sitesModel = new Application_Model_DbTable_Sites();
        $exchangeModel = new Application_Model_DbTable_Exchange();
    	$params = $this->getRequest()->getParams();
    	
    	$this->view->ad_sizes = $sizeModel->fetchAll(null, 'position');
    	$this->view->sites = $sitesModel->fetchAll("rub_io IS NOT NULL", "SiteName");    	 
    	
    	$sitesArray = array(0);
    	
    	if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"])) {
    		if($this->view->sites){
    			foreach($this->view->sites as $site){
    				$sitesArray[] = $site['SiteID'];
    			}
    			
    		}
    	}else{
    		if($this->view->sites){
    			foreach($this->view->sites as $site){
    				if($params["site"]==$site['SiteID']){
    					$sitesArray[] = $site['SiteID'];
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
        $finalUserReport = $exchangeModel->getExchangeReport($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"]);
    	$this->view->report = $finalUserReport;                
    }

    public function viewAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'exchange';      
        
        $date = $this->getRequest()->getParam('date');
        $ad_size = $this->getRequest()->getParam('ad_size');
                
        $date = date('Y-m-d', strtotime($date));
         
        $tableSite = new Application_Model_DbTable_Sites();
        $exchangeModel = new Application_Model_DbTable_Exchange();
        $sizeModel = new Application_Model_DbTable_Sizes();
           
        $sql = $tableSite->select()->setIntegrityCheck(false)
                         ->from(array('s' => 'sites'), array('s.SiteID', 's.floor_pricing'))
                         ->where('s.rub_io IS NOT NULL');
        
        $dataSite = $tableSite->fetchAll($sql, 's.SiteName');
               
        $siteIDs = array();
         
        foreach($dataSite as $iter){            
            $siteIDs[]= $iter['SiteID'];            
        }   
                
        $dataReport = $exchangeModel->getReportByDate($siteIDs, $date, $ad_size);        
                
        $this->view->report = $dataReport;        
        $this->view->curent = array('date' => $date, 'ad_size' => $ad_size);                
        $this->view->ad_sizes = $sizeModel->fetchAll(null, 'position');                
    }
    
    public function sizeAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'exchange';      
                
        $date = $this->getRequest()->getParam('date');
        $site_id = (int)$this->getRequest()->getParam('site');
                
        $date = date('Y-m-d', strtotime($date));
         
        $tableSite = new Application_Model_DbTable_Sites();
        $exchangeModel = new Application_Model_DbTable_Exchange();
        $sizeModel = new Application_Model_DbTable_Sizes();
           
        $sql = $tableSite->select()->setIntegrityCheck(false)
                         ->from(array('s' => 'sites'), array('s.SiteID', 's.floor_pricing', 's.SiteName'))
                         ->where('rub_io IS NOT NULL')
                         ->where('SiteID = ?', $site_id);
        
        $dataSite = $tableSite->fetchRow($sql);
                              
        $dataReport = $exchangeModel->getReportBySize($site_id, $date);        
 
        $this->view->report = $dataReport;        
        $this->view->curent = array('date' => $date, 'site' => $site_id);
        $this->view->site = $dataSite;        
    }
}