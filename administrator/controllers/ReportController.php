<?php
class Administrator_ReportController extends Zend_Controller_Action
{
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
        
        $this->view->headTitle('Referral');
    }
    
    public function indexAction()
    {
        $sizeModel = new Application_Model_DbTable_Sizes();
        $reportModel = new Application_Model_DbTable_ReportExecute();
        
        $this->view->sizes = $sizeModel->fetchAll(null, "name");
        
        $reports = $reportModel->fetchAll();
        
        if(count($reports)>0){
            $this->view->message = 'Your query will be processed in the nearest time';
        }
                                
        if($this->getRequest()->isPost()){
            
            if($this->getRequest()->getPost('sites')){
                $sitesStr = trim($this->getRequest()->getPost('sites'));
                $sitesStr = rtrim($sitesStr, ",");
                $sitesArray = explode(", ", $sitesStr);
            }else{
                $sitesArray = array();
            }

            $dataReport = $reportModel->createRow();
            $dataReport->setFromArray(array(
                'site' => $this->getRequest()->getPost('all_sites')==1 ? 'all' : serialize($sitesArray),
                'size' => $this->getRequest()->getPost('size') ? serialize($this->getRequest()->getPost('size')) : null,
                'type' => $this->getRequest()->getPost('type') ? serialize($this->getRequest()->getPost('type')) : null,
                'floor_pricing' => $this->getRequest()->getPost('floor_pricing'),
                'start_date' => $this->getRequest()->getPost('start_date'),
                'end_date' => $this->getRequest()->getPost('end_date'),
                'date_add' => date("Y-m-d H:i:s"),
                'updat' => $this->getRequest()->getPost('updat')==1 ? '' : 1
            ));

            $dataReport->save();                        
            $this->_redirect('/administrator/report/');
        }
    }
    
    public function getAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering     
        
        $output = array();
        $sitesModel = new Application_Model_DbTable_Sites();
        
        $search = $this->_getParam('term');

        $sql = $sitesModel->select()->setIntegrityCheck(false)
                            ->from(array('s' => 'sites'),array(
                                's.SiteID AS id',
                                's.SiteName AS label',
                                's.SiteID AS value'))
                            ->where("s.SiteName LIKE ?", '%'.$search.'%')
                            ->orWhere("s.SiteID LIKE ?", '%'.$search.'%')
                            ->orWhere("s.PubID LIKE ?", '%'.$search.'%');                          
        
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
}