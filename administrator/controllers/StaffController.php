<?php
class Administrator_StaffController extends Zend_Controller_Action
{  
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->nav = 'staff';
                       
        $this->view->headTitle('Staff');
    }
    
    public function indexAction()
    {
        $this->_layout->setLayout('admin');
        
        $tableStaff = new Application_Model_DbTable_Staff();
        
        $dataStaff = $tableStaff->getAllData();
        
        $this->view->data = $dataStaff;
    }
    
    public function addAction()
    {
        $this->_layout->setLayout('admin');
        
        $tableStaff = new Application_Model_DbTable_Staff();
        
        $dataStaff = $tableStaff->createRow();
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $form = new Application_Form_Staff($dataStaff);
        
        if($this->getRequest()->isPost()){
               if($form->isValid($this->_getAllParams())){
                   
                   $form->appendData();
                   
                   $dataStaff->date_created = date('Y-m-d H:i:s');
                   $dataStaff->created_by = $dataAuth->email;
                   $dataStaff->save();
                   
                   $this->_redirect('/administrator/staff/index');                   
               }
        }
        
        $this->view->form = $form;
    }
    
    public function editAction()
    {
        $this->_layout->setLayout('admin');
        
        $id = (int) $this->_getParam('id');
        
        $tableStaff = new Application_Model_DbTable_Staff();
        
        $sqlStaff = $tableStaff->select()->where('id = ?', $id);
        
        $dataStaff = $tableStaff->fetchRow($sqlStaff);
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
 
        $form = new Application_Form_Staff($dataStaff); 
        $form->showOldData();
        
        if($this->getRequest()->isPost()){
               if($form->isValid($this->_getAllParams())){
                   
                   $form->appendData();
                   
                   $dataStaff->date_updated = date('Y-m-d H:i:s');
                   $dataStaff->updated_by = $dataAuth->email;
                   $dataStaff->save();
                   
                   $this->_redirect('/administrator/staff/index');
               }
        }
        
        $this->view->form = $form;
    }    
    
}