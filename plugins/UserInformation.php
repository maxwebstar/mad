<?php

class Application_Plugin_UserInformation extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = new Zend_Layout();
        
	$layout->pdfError = 0;
		
        $auth = Zend_Auth::getInstance()->getIdentity();        
        
        if(!empty($auth->id)){ 
            $usersModel = new Application_Model_DbTable_Users();
            $userSInfo = $usersModel->getUserInfo($auth->id); 
      
            if(!$userSInfo['name'] 
                || !$userSInfo['payType'] 
                || (!$userSInfo['street1'] && !$userSInfo['street2'])
                || !$userSInfo['country'] 
                || !$userSInfo['state']
                || !$userSInfo['city']
                || !$userSInfo['zip']
                || !$userSInfo['paymentAmout']
                || !$userSInfo['paymentBy']){
                $layout->PaymentError = 1; 
            }/*elseif($userSInfo['payType']==1 && !$userSInfo['ssn']){
                $this->view->PaymentError = 1;
            }elseif($userSInfo['payType']==2 && !$userSInfo['ein']){
                $this->view->PaymentError = 1;
            }*/elseif($userSInfo['paymentBy']==2 && !$userSInfo['paypalmail']){
                $layout->PaymentError = 1; 
            }elseif($userSInfo['paymentBy']==3 && (!$userSInfo['bank'] || !$userSInfo['bankName'] || !$userSInfo['accType'] || !$userSInfo['accNumber'] || !$userSInfo['confirmAccNumber'] || !$userSInfo['routNumber'] || !$userSInfo['confirmRoutNumber'])){
                $layout->PaymentError = 1;
            }elseif($userSInfo['paymentBy']==4 && (!$userSInfo['bankName2'] || !$userSInfo['bankAdress'] || !$userSInfo['accName2'] || !$userSInfo['accNumber2'] || !$userSInfo['swift'] || !$userSInfo['iban'])){
                $layout->PaymentError = 1; 
            }
			
            $tablePdf = new Application_Model_DbTable_Pdf_Entity();
            $existPdf = $tablePdf->check($auth->id);
            if((!$existPdf && $userSInfo['country'] == 237) || (!$existPdf && $userSInfo['country'] == 238)) $layout->pdfError = 1;
            
            $layout->invite = array('url' => $userSInfo['inviteURL'], 'request' => $userSInfo['inviteRequest'], 'inviteAdx' => $userSInfo['inviteAdx']); 
		
            $reportTable = new Application_Model_DbTable_Report();
            $sql = $reportTable->select()->from('users_reports_final', array('impress'=>'SUM(impressions_denied)'))->where("PubID='".$auth->id."'");
            $dataDeniImpress = $reportTable->fetchRow($sql);
            $layout->deniImpress = $dataDeniImpress->impress;            
            
        }else{
            $layout->PaymentError = 0;
            $layout->invite = array('url' => 0, 'request' => 0, 'inviteAdx' => 0);
        }
                if(isset($userSInfo['reg_AdExchage']) && $userSInfo['reg_AdExchage']==1){
        	$layout->CPMs = 1;
        }
        $layout->testvar = 'test';        
    }
}