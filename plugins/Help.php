<?php

class Application_Plugin_Help extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    { 
        //$this->view->test = 'test';
        
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $layout = new Zend_Layout();
        $tags_model = new Application_Model_DbTable_Tags();
        $userModelH = new Application_Model_DbTable_Users();        
        
        if($auth->role == 'admin' || $auth->role == 'super' || $auth->role == 'support'){
        	$websiteLogsModel = new Application_Model_DbTable_WebsiteLogs();
        	$layout->logs = $websiteLogsModel->getLogs(null,$auth->id);

            $layout->test = $tags_model->countNoTags();

            $layout->countNewUsers = $userModelH->countNewUsersNoWaiting();
            $layout->countNewUsersWaiting = $userModelH->countNewUsersWaiting();
            $layout->countNewUsers2 = $userModelH->countNewUsers2();
            
            $alerts = new Application_Model_DbTable_Alerts();
            $sql = $alerts->select()->from('alerts', array('count_alerts'=>'COUNT(id)'))->where("status=1 AND date_alert<='".date("Y-m-d")."' AND adminID='".$auth->admin_id."'");
            $dataAlerts = $alerts->fetchRow($sql);
            $layout->countAlerts = $dataAlerts->count_alerts;
            
            $sizesRequstModel = new Application_Model_DbTable_SizesRequest();
            $sql = $sizesRequstModel->select()->from('sizes_request', array('count'=>'COUNT(id)'))->where("status=1");
            $wanted_banners = $sizesRequstModel->fetchRow($sql);
            $layout->countWantBaner = $wanted_banners['count'];

			$burst_model = new Application_Model_DbTable_WantBurst();
            $sql = $burst_model->select()->from('sites_want_burst', array('count_burst'=>'COUNT(id)'));
            $dataBurst = $burst_model->fetchRow($sql);
            $layout->countBurstRequest = $dataBurst->count_burst;

            $wanted_iframes = $tags_model->getRequestedIframes();
            $layout->countWantIframe = $wanted_iframes['count'];            
            
            $CoSitesApprov = new Application_Model_DbTable_CoSiteApprovals;
            $sql = $CoSitesApprov->select()->from('co_site_approvals', array('count'=>'COUNT(SiteID)'));
            $dataCoApprov = $CoSitesApprov->fetchRow($sql);
            $layout->countCoSiteApprov = $dataCoApprov->count;                        
        }
       
        $userModel = new Application_Model_DbTable_Users();
        if(!empty($auth))
        $paymentInfo = $userModel->getUserPaymentDue($auth->id);
        //print_r($paymentInfo);
        if(!empty($paymentInfo['total'])){
        	$layout->nextPaymentSum = '<a href="/payments">$'.number_format($paymentInfo['total'], 2).'</a>';
        	if(!empty($paymentInfo['date'])){
	        	$year = date("Y", strtotime($paymentInfo['date'])); 
	        	$month = date("m", strtotime($paymentInfo['date']));
	        	$countDates = date("t", strtotime($paymentInfo['date']));
	        	$layout->nextPaymentDate = date("F 15\\t\h, Y", mktime(0,0,0,$month+2,15,$year));
        	}else{
        		$layout->nextPaymentDate = '';
        	}
        }else{
        	$layout->nextPaymentSum = '<a href="/payment">Minimum Not Met</a>';
            if(!empty($paymentInfo['date'])){
	        	$year = date("Y", strtotime($paymentInfo['date']));
	        	$month = date("m", strtotime($paymentInfo['date']));
	        	$countDates = date("t", strtotime($paymentInfo['date']));
	        	$layout->nextPaymentDate = date("F 15\\t\h, Y", mktime(0,0,0,$month+2,15,$year));
        	}else{
        		$layout->nextPaymentDate = '';
        	}
       }
//       if(isset($auth->id)) 
//$layout->countPsaUsers = $userModelH->countPsaUsers($auth->id);
       $layout->countPsaUsers = 0;       
       $news_model = new Application_Model_DbTable_UserNews();
       $data = $news_model->getPreparedNews($auth,2,1);
       $layout->news = $data;
    }

    
}