<?php

class Application_Plugin_CountAssign extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = new Zend_Layout();
        $modelSites = new Application_Model_DbTable_Sites();
        $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
        $tableUser = new Application_Model_DbTable_Users();
        $tableDeniedUrl = new Application_Model_DbTable_Madads_Blocked();
        $tableNotifi = new Application_Model_DbTable_Sites_NotifiMessage();
        $tableTask = new Application_Model_DbTable_CheckTask();        
                
        $layout->countNewSites = count($modelSites->getNewSites());    
        $layout->countNewFloor = $tableCpm->getNum();
        $layout->countAdxRegistration = $tableUser->getNumNewInviteReguest();
        $layout->countDeniedUrl = $tableDeniedUrl->getNum();
        $layout->countPending = $tableNotifi->getNum();
        $layout->siteCorrection = $tableNotifi->getSiteNeedNotifi();
        $layout->dataTask = $tableTask->getData();        
    }
}

?>