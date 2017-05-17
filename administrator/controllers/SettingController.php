<?php
class Administrator_SettingController extends Zend_Controller_Action
{
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
        
        $this->view->headTitle('Referral');
    }
    
    public function indexAction()
    {
        
    }
    
    public function editAction()
    {
        $form = new Application_Form_Setting_Edit();
        $form->showOldData();
        
        if ($this->getRequest()->isPost()) {
           if ($form->isValid($this->_getAllParams())) {
            
               $form->saveData();
               
           }
        }
        $this->view->form = $form;
    }
    
    
}
?>
