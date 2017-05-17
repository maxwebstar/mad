<?php
class Administrator_ContactController extends Zend_Controller_Action
{
    public $_layout;
    
    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $this->_layout->nav = 'payDue';  
    }
    
    public function viewAction()
    {
        $this->_layout->nav = 'payDue'; 
        
        $tableUser = new Application_Model_DbTable_Users();
        $tableContact = new Application_Model_DbTable_User_NewContact();
        
        $sql = $tableContact->select()->where('PubID = ?', (int) $this->_getParam('id'));
        
        $dataContact = $tableContact->fetchRow($sql);
        $dataUser = $tableUser->getUserById($dataContact->PubID);
        
        $this->view->wait = $dataContact;
        $this->view->user = $dataUser;       
    }
    
    public function approveAction()
    {
        $this->_layout->nav = 'payDue'; 
        
        $tableContact = new Application_Model_DbTable_User_NewContact();
        $tableUser = new Application_Model_DbTable_Users();
        
        $sql = $tableContact->select()->where('PubID = ?', (int)$this->_getParam('id'));
        $str = $sql->__toString();
        $dataContact = $tableContact->fetchRow($sql);
    
        if($dataContact){
            
            $dataUser = array('company' => $dataContact->company,
                              'name'    => $dataContact->name,
                              'email'   => $dataContact->email,
                              'phone'   => $dataContact->phone,
            				  'date_contact_approve' => date("Y-m-d H:i:s"),
            );
            
            $tableUser->update($dataUser, 'id = '.$dataContact->PubID);
            
            $dataContact->delete();            
        }
        
        $this->_redirect('/administrator/contact/index');               
    }
    
    public function deleteAction()
    {
        $this->_layout->nav = 'payDue'; 
        
        $tableContact = new Application_Model_DbTable_User_NewContact(); 
        
        $sql = $tableContact->select()->where('PubID = ?', (int) $this->_getParam('id'));
        
        $dataContact = $tableContact->fetchRow($sql);
        $dataContact->delete();
        
        $this->_redirect('/administrator/contact/index');
        
    }
      
    
    public function pendingAjaxAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $pending_model = new Application_Model_DbTable_User_NewContact();
        $params = $this->_getAllParams();
        switch($params['filter'])
        {
        	case 'approved':
        		$output = $pending_model->getApprovedContacts($params);
        		break;
        	case 'pending':
        		$output = $pending_model->getPendingContacts($params);
        		break;
        	default:
        		$output = $pending_model->getPendingContacts($params);
        		break;
        }
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    
}
?>
