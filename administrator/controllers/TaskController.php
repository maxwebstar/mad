<?php

class Administrator_TaskController extends Zend_Controller_Action
{
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');      
        $layout->nav = 'task';
    }
    
    public function indexAction()
    {        
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity(); 
        
        $tableTask = new Application_Model_DbTable_CheckTask();  
        
        $this->view->dataTask = $tableTask->getAllData($dataAuth);

    }
    
    public function addAction()
    {
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity(); 
        
        $tableTask = new Application_Model_DbTable_CheckTask();
        $tableUser = new Application_Model_DbTable_Users();
        
        $dataAdmin = $tableUser->getAllAdmin();
        $dataTask = $tableTask->createRow();
        
        $selectAdmin = array(); 
        foreach($dataAdmin as $iter){ $selectAdmin[$iter['email']] = $iter['email']; }
        
        $form = new Application_Form_Task_Add($dataAuth, $dataTask, $selectAdmin);        
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->_getAllParams())){
                
                $form->appendData();  
                $dataTask->save();
                
                $this->_redirect('/administrator/task/index');
            }
        }
        
        $this->view->form = $form;
        
    }
    
    public function editAction()
    {
        $taskID = (int) $this->_getParam('id');
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity(); 
        
        $tableTask = new Application_Model_DbTable_CheckTask();
        $tableUser = new Application_Model_DbTable_Users();
        
        $sqlTask = $tableTask->select()->where('id = ?', $taskID);
        $dataTask = $tableTask->fetchRow($sqlTask);
        
        if($dataAuth->role == 'super' || $dataAuth->email == $dataTask->for){
        
            $dataAdmin = $tableUser->getAllAdmin();       

            $selectAdmin = array(); 
            foreach($dataAdmin as $iter){ $selectAdmin[$iter['email']] = $iter['email']; }

            $form = new Application_Form_Task_Edit($dataAuth, $dataTask, $selectAdmin);   
            $form->showOldData();

            if($this->getRequest()->isPost()){
                if($form->isValid($this->_getAllParams())){

                    $form->appendData();  
                    $dataTask->save();

                    $this->_redirect('/administrator/task/index');
                }
            }

            $this->view->form = $form;
        
        } else { $this->view->message = '<div style="color: red; font-size: 16px;">You do not have permissions for edit this task!</div>'; }
    }
    
    public function deleteAction()
    {
        $taskID = (int) $this->_getParam('id');
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity(); 
        
        $tableTask = new Application_Model_DbTable_CheckTask();
        $tableTaskUser = new Application_Model_DbTable_CheckTaskUser();
        
        $sqlTask = $tableTask->select()->where('id = ?', $taskID);
        $dataTask = $tableTask->fetchRow($sqlTask);
        
        if($dataAuth->role == 'super' || $dataAuth->email == $dataTask->for){
        
            $tableTaskUser->delete('task_id = '.$dataTask->id);
            $dataTask->delete();

            $this->_redirect('/administrator/task/index');
   
        
        } else { $this->view->message = '<div style="color: red; font-size: 16px;">You do not have permissions for remove this task!</div>'; }
    }    
    
    
    
}