<?php
class Administrator_SubController extends Zend_Controller_Action
{  
    protected $_layout;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
                       
        $this->view->headTitle('Sub');
    }
    
    public function indexAction()
    {        
        $tableSub = new Application_Model_DbTable_Sub();
        
        $dataSub = $tableSub->getAllData();
        
        $this->view->data = $dataSub;
    }
    
    public function addAction()
    {        
        $tableSub = new Application_Model_DbTable_Sub();
        
        $dataSub = $tableSub->createRow();
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $form = new Application_Form_Sub($dataSub);
        
        if($this->getRequest()->isPost()){
               if($form->isValid($this->_getAllParams())){
                   
                   $form->appendData();
                   
                   $dataSub->created = date('Y-m-d H:i:s');
                   $dataSub->created_by = $dataAuth->email;
                   $dataSub->save();
                   
                   $this->_redirect('/administrator/sub/index');                   
               }
        }
        
        $this->view->form = $form;
    }
    
    public function editAction()
    {                
        $id = (int) $this->_getParam('id');
        
        $tableSub = new Application_Model_DbTable_Sub();
        
        $sqlSub = $tableSub->select()->where('id = ?', $id);
        
        $dataSub = $tableSub->fetchRow($sqlSub);
 
        $form = new Application_Form_Sub($dataSub); 
        $form->showOldData();
        
        if($this->getRequest()->isPost()){
               if($form->isValid($this->_getAllParams())){
                   
                   $form->appendData();

                   $dataSub->save();
                   
                   $this->_redirect('/administrator/sub/index');
               }
        }
        
        $this->view->form = $form;
    }    
    
}