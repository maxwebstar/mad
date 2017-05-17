<?php

require_once (LIB_PATH.'/google-api-php-client/src/Google_Client.php');
require_once (LIB_PATH."/google-api-php-client/src/contrib/Google_AdExchangeSellerService.php");

class Administrator_GoogleApiController extends Zend_Controller_Action
{
    public function init()
    {
        
    }
    
    public function indexAction()
    {
         $layout = Zend_Layout::getMvcInstance();
         $layout->setLayout('admin');
         
         $tableSize = new Application_Model_DbTable_Sizes();
         
         $this->view->size = $tableSize->getAllSize();
    }
    
    
    public function ajaxCheckAccessAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(); 
        
        $client = new Google_Client();
        $client->setApplicationName('AdExchangeSellerService API PHP');

        $client->setScopes(array('https://www.googleapis.com/auth/adexchange.seller.readonly', 'https://www.googleapis.com/auth/adexchange.seller'));

        $client->setClientId('182999324078-kussdikg9poiemm67mp8a86q4qlvm658.apps.googleusercontent.com'); /*'insert_your_oauth2_client_id'*/
        $client->setClientSecret('pDhdFAOeP_hU3Na85qcA4mVS'); /*'insert_your_oauth2_client_secret'*/
        $client->setRedirectUri('http://madadsmedia.com/administrator/google-api/set-access'); /*'insert_your_oauth2_redirect_uri'*/
        
                 $session = new Zend_Session_Namespace('Google_Api');        
        if(isset($session->access_token) && $session->access_token) $client->setAccessToken($session->access_token);
        
           $result = array(); 
           $authTocken = $client->getAccessToken();
        
        if($authTocken){
            
            $result['status'] = 'token';
            $result['value'] = $authTocken;
            
        }else{
            
           $authUrl = $client->createAuthUrl();
           
           $result['status'] = 'login';
           $result['value'] = $authUrl;
        }      
        
        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function setAccessAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(); 
        
        $session = new Zend_Session_Namespace('Google_Api');
        
        if(isset($_GET['code'])){          
            
            $client = new Google_Client();
            
            $client->setScopes(array('https://www.googleapis.com/auth/adexchange.seller.readonly', 'https://www.googleapis.com/auth/adexchange.seller'));

            $client->setClientId('182999324078-kussdikg9poiemm67mp8a86q4qlvm658.apps.googleusercontent.com'); /*'insert_your_oauth2_client_id'*/
            $client->setClientSecret('pDhdFAOeP_hU3Na85qcA4mVS'); /*'insert_your_oauth2_client_secret'*/
            $client->setRedirectUri('http://madadsmedia.com/administrator/google-api/set-access'); /*'insert_your_oauth2_redirect_uri'*/
            
            $client->authenticate();
            $session->access_token = $client->getAccessToken();        
            
        } else $session->access_token = NULL; 
        
        $this->_redirect('/administrator/google-api/index');
    }
    
    public function getDataAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(); 
        
        $dateStart = $this->_getParam('start');
        $dateEnd = $this->_getParam('end');
        $sizeAd = $this->_getParam('size');
        $searchUnit = $this->_getParam('search');
        
        $client = new Google_Client();
        $client->setApplicationName('AdExchangeSellerService API PHP');

        $client->setScopes(array('https://www.googleapis.com/auth/adexchange.seller.readonly', 'https://www.googleapis.com/auth/adexchange.seller'));

        $client->setClientId('182999324078-kussdikg9poiemm67mp8a86q4qlvm658.apps.googleusercontent.com'); /*'insert_your_oauth2_client_id'*/
        $client->setClientSecret('pDhdFAOeP_hU3Na85qcA4mVS'); /*'insert_your_oauth2_client_secret'*/
        $client->setRedirectUri('http://madadsmedia.com/administrator/google-api/set-access'); /*'insert_your_oauth2_redirect_uri'*/
        
                 $session = new Zend_Session_Namespace('Google_Api');        
        if(isset($session->access_token) && $session->access_token) $client->setAccessToken($session->access_token);
        
        $tableSite = new Application_Model_DbTable_Sites();
        $tableSize = new Application_Model_DbTable_Sizes();
        $tableGoogle = new Application_Model_DbTable_DataFromGoogleApi();
        $serviceGoogle = new Google_AdExchangeSellerService($client);
        
        $classReport = $serviceGoogle->reports;
        
        $apiFilter = array();
        
        if($sizeAd != 'all') $apiFilter[] = 'AD_UNIT_SIZE_NAME=='.$sizeAd;
        if($searchUnit) $apiFilter[] = 'AD_UNIT_NAME=@'.$searchUnit;
        
        if(count($apiFilter)){ $dataReport = $classReport->generate($dateStart, $dateEnd, array('dimension' => array('AD_UNIT_NAME','AD_UNIT_SIZE_NAME','DATE'), 'metric' => array('INDIVIDUAL_AD_IMPRESSIONS','CLICKS','COST_PER_CLICK'), 'filter' => $apiFilter)); }
                         else{ $dataReport = $classReport->generate($dateStart, $dateEnd, array('dimension' => array('AD_UNIT_NAME','AD_UNIT_SIZE_NAME','DATE'), 'metric' => array('INDIVIDUAL_AD_IMPRESSIONS','CLICKS','COST_PER_CLICK'))); } /*'maxResults' => '10000'*/

        $result['rows'] = array();                
                        
        if(isset($dataReport['rows']) && is_array($dataReport['rows'])){
        
            $tmpSize = $tableSize->getAllSize();
            $dataSize = array();
            foreach($tmpSize as $key => $iter){ $dataSize[$iter['description']] = $iter; }
            
            foreach($dataReport['rows'] as $key => $iter){

                    if($searchUnit == 'Custom') $SiteName = str_replace('-Custom', '', $iter[0]); 
                                           else $SiteName = str_replace('-'.$iter[1], '', $iter[0]);                                    
   
                    $iter[1] = ($iter[1] == 'Custom') ? '234x60' : $iter[1];                      
                                           
                    $dataSite = $tableSite->getIDbyDoname($SiteName);                 
                 if($dataSite && isset($dataSize[$iter[1]])){    
                     
                     $result['rows'][$iter[2]][$dataSite['SiteID']] = array('PubID'  => $dataSite['PubID'],
                                                                            'SiteID' => $dataSite['SiteID'],
                                                                            'SiteName' => $dataSite['SiteName'], 
                                                                            'SizeAd' => $iter[1],
                                                                            'SizeID' => $dataSize[$iter[1]]['id'],
                                                                            'date' => $iter[2],
                                                                            'date_start' => $dateStart,
                                                                            'date_end' => $dateEnd,
                                                                            'impressions' => $iter[3],
                                                                            'revenue' => round($iter[4] * $iter[5], 2),
                                                                            'status' => 1);
                     
                     $tableGoogle->inserData($result['rows'][$iter[2]][$dataSite['SiteID']]);
                 
                 } else {
                     
                     $result['rows'][$iter[2]][$dataSite['SiteID']] = array('PubID'  => isset($dataSite['PubID']) ? $dataSite['PubID'] : 0,
                                                                            'SiteID' => isset($dataSite['SiteID']) ? $dataSite['SiteID'] : 0,
                                                                            'SiteName' => $SiteName, 
                                                                            'SizeAd' => $iter[1],
                                                                            'SizeID' => isset($dataSize[$iter[1]]) ? $dataSize[$iter[1]]['id'] : 0,
                                                                            'date' => $iter[2],
                                                                            'date_start' => $dateStart,
                                                                            'date_end' => $dateEnd,
                                                                            'impressions' => $iter[3],
                                                                            'revenue' => round($iter[4] * $iter[5], 2),
                                                                            'status' => 0);
                     
                 }
            }
        
        }
        
        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
        
    }   
    
}
    
?>
