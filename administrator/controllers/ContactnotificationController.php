<?php
class Administrator_ContactnotificationController extends Zend_Controller_Action
{  
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->nav = 'contact_notification';
                       
        $this->view->headTitle('Contact Notification');
    }
    
    public function indexAction()
    {
        $this->_layout->setLayout('admin');
        
        $tableContactNotification = new Application_Model_DbTable_ContactNotification();
        
        $this->view->data = $tableContactNotification->getAllData();
    }
    
    public function addAction()
    {
        $this->_layout->setLayout('admin');
        
        $tableStaff = new Application_Model_DbTable_Staff();
        $tableContactNotification = new Application_Model_DbTable_ContactNotification();
        
        $dataStaff = $tableStaff->getDataForSelect();
        $dataContact = $tableContactNotification->createRow();
                        
        $form = new Application_Form_ContactNotification_Add($dataContact, $dataStaff);
        $form->showDefault();
 
        if($this->getRequest()->isPost()){
               if($form->isValid($this->_getAllParams())){
                   
                   $form->appendData();
                   
                   $dataContact->save();
                   
                   $this->_redirect('/administrator/contactnotification/index');
                   
               }
        }
        
        $this->view->form = $form;
    }
    
    public function editAction()
    {
        $this->_layout->setLayout('admin');
        
        $this->view->id = (int)$this->_getParam('id');
        
        $tableStaff = new Application_Model_DbTable_Staff();
        $tableContactNotification = new Application_Model_DbTable_ContactNotification();
        
        $sql = $tableContactNotification->select()->where('id = ?', (int)$this->_getParam('id'));
        
        $dataStaff = $tableStaff->getDataForSelect();
        $dataContact = $tableContactNotification->fetchRow($sql);
     
        $form = new Application_Form_ContactNotification_Edit($dataContact, $dataStaff);
        $form->showOldData();
        
        if($this->getRequest()->isPost()){
               if($form->isValid($this->_getAllParams())){
                   
                   $form->appendData();
                   $dataContact->save();
                   
                   $this->_redirect('/administrator/contactnotification/index');
                   
               }
        }
        
        $this->view->form = $form;
    }
    
    public function deleteAction()
    {
        $this->_helper->layout()->disableLayout();
        
        $tableContactNotification = new Application_Model_DbTable_ContactNotification();
        
        $sql = $tableContactNotification->select()->where('id = ?', (int)$this->_getParam('id'));
        
        $dataContact = $tableContactNotification->fetchRow($sql);
        $dataContact->delete();
        
        $this->_redirect('/administrator/contactnotification/index');
    }
    
    public function assignAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $userModel = new Application_Model_DbTable_Users();
        $contactModel = new Application_Model_DbTable_ContactNotification();
        
        $id = (int)  $this->_getParam('id');
        
        if($id){
            $sql = $contactModel->select()
                        ->from('contact_notification', array(
                            'name'=>'contact_notification.name',
                            'mail'=>'contact_notification.mail',
                            'new'=>'contact_notification.new'
                        ))
                        ->where("id='".$id."'");
            $dataManager = $contactModel->fetchRow($sql);
            
            $sql = $userModel->select()
                        ->from('users', array(
                            'count'=>'COUNT(id)'
                        ))
                        ->where("role='user'")
                        ->where("lock_am IS NULL");
            $countAllUsers = $userModel->fetchRow($sql);
            
            $sql = $userModel->select()
                        ->from('users', array(
                            'count'=>'COUNT(id)'
                        ))
                        ->where("account_manager_id='".$id."'")
                        ->where("lock_am IS NULL");
            $countManagerUsers = $userModel->fetchRow($sql);
            
            $sql = $contactModel->select()
                        ->from('contact_notification', array(
                            'count'=>'COUNT(id)'
                        ))
                        ->where("status=1")
                        ->where("lock_am IS NULL");
            $countAllManagers = $contactModel->fetchRow($sql);
            
            $countUsersForManager = round($countAllUsers->count/$countAllManagers->count)-$countManagerUsers->count;
            
            $date = date("Y-m-d", mktime(0, 0, 0, date("n")-1, date("d"), date("Y")));
            
            $sql = $userModel->select()->setIntegrityCheck(false)
                        ->from('users', array(
                            'id'=>'users.id',
                            'email'=>'users.email',
                            'name'=>'users.name',
                            'company'=>'users.company',
                            'account_manager_id'=>'users.account_manager_id',
                            'active'=>'users.active'
                        ))
                        ->joinLeft('contact_notification', 'contact_notification.id=users.account_manager_id', array(
                            'manager_name'=>'contact_notification.name'
                        ))
                        ->where("users.account_manager_id IN (SELECT id FROM contact_notification WHERE status=1 AND new is NULL AND created<='".$date."' AND id!='".$id."')")
                        ->where("role='user'")
                        ->where("users.lock_am IS NULL")
                        ->limit($countUsersForManager)
                        ->order("RAND()");
            
            $dataAssign = $userModel->fetchAll($sql);
            
            foreach ($dataAssign as $user){
                if($user->active==1 && $user->account_manager_id!=$id){
                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";    
                    $headers .= "From: ".$dataManager->name." <".$dataManager->mail.">\r\n";                                    
                    $to = $user->email;
                    $title = 'New MadAds Media Account Manager ('.$user->company.')';
                    $message = "<p>Hello ".$user->name.",</p>";
                    $message.="<p>I would like to introduce myself as your new account manager here at MadAds Media.  I will be taking over for ".$user->manager_name.", going forward.</p>";
                    $message.='<p>If at any point you have an issue with your account, or need a question answered, please don\'t hesitate to reach out!</p>';
                    $message.='<p>I look forward to working with you.</p>';
                    $message.='<p>Regards,<br />'.$dataManager->name.'</p>';
                    $message.= '<table width="450" cellspacing="3" cellpadding="0" border="0">
                                  <tbody><tr>
                                    <td width="225" valign="bottom"><font style="color:rgb(204, 0, 0); font-family:arial,helvetica,sans-serif; font-weight:bold;">'.$dataManager->name.'</font></td>
                                    <td align="right"><b><span style="font-size:8.0pt; font-family:Arial,sans-serif; color:#7f7f7f">tel: 856-874-8928</span></b></td>
                                  </tr>
                                  <tr>
                                    <td valign="top"><font style="font-size:12px; font-family:arial,helvetica,sans-serif;">Publisher Relations Associate</font> </td>
                                    <td align="right"><a style="color:rgb(204, 0, 0); font-size:11px; font-family:arial,helvetica,sans-serif;" target="_blank" href="mailto:'.$dataManager->mail.'">'.$dataManager->mail.'</a></td>
                                  </tr>
                                  <tr>
                                    <td><img width="170" height="54" src="http://www.madadsmedia.com/images/signature/mam-signature.gif"></td>
                                    <td valign="middle" align="right"><span style="font-size:8.0pt; font-family:arial,helvetica,sans-serif; color:#777777">717 Fellowship Rd. Suite D<br>
                                          <span style="font-size:8.0pt; font-family:arial,helvetica,sans-serif; color:#777777">Mount Laurel, NJ 08054</span> </span></td>
                                  </tr>
                                </tbody></table>';                    
                    if(APPLICATION_ENV!='development')
                        mail($to, $title, $message, $headers);
                }
                $userModel->update(array('account_manager_id'=>$id), "id='".$user->id."'");
            }
            
            $contactModel->update(array('new'=>NULL), "id='".$id."'");
        }
        
        $this->_redirect('/administrator/contactnotification/index');
    }
    
    public function manageReferalsAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$referral_model = new Application_Model_DbTable_Referral();
    	$params = $this->_getAllParams();
    	$result = $referral_model->getReferalsByEmail($params);
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function assignReferalsAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$params = $this->_getAllParams();
    	$referal_model = new Application_Model_DbTable_Referral();
    	$result =  $referal_model->assignReferal($params);
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function assignPrimaryReferalsAction()
    {
        	$this->_helper->layout()->disableLayout();
        	$this->_helper->viewRenderer->setNoRender();
        	$params = $this->_getAllParams();
        	$referal_model = new Application_Model_DbTable_Referral();
        	$result =  $referal_model->assignPrimaryReferal($params);
        	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    
    public function removeAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        $id = (int)$this->_getParam('id');
        
        $tableContact = new Application_Model_DbTable_ContactNotification();
        $tableUsers = new Application_Model_DbTable_Users();

        $sql = $tableContact->select()
                    ->from('contact_notification', array(
                        'name'=>'contact_notification.name',
                        'mail'=>'contact_notification.mail',
                        'new'=>'contact_notification.new'
                    ))
                    ->where("id='".$id."'");
        $dataManagerOld = $tableContact->fetchRow($sql);
                
        $tableUsers->update(array('account_manager_id'=>-1), "account_manager_id='$id'");
        
        $sql = $tableUsers->select()
                    ->from('users', array(
                        'id'=>'users.id',
                        'email'=>'users.email',
                        'name'=>'users.name',
                        'company'=>'users.company',
                        'active'=>'users.active'
                    ))
                    ->where("account_manager_id='-1'");
        $dataUsers = $tableUsers->fetchAll($sql);
        
        foreach ($dataUsers as $user){
            
            $sql = $tableContact->select()
                        ->from('contact_notification', array(
                            'id'=>'contact_notification.id',
                            'name'=>'contact_notification.name',
                            'mail'=>'contact_notification.mail'
                        ))
                        ->where("id!='".$id."'")
                        ->where("status=1")
                        ->order("RAND()")
                        ->limit(1);
            $dataManagerNew = $tableContact->fetchRow($sql);
                        
            $tableUsers->update(array('account_manager_id'=>$dataManagerNew->id), "id='".$user->id."'");
            
            if($user->active==1){
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";    
                $headers .= "From: ".$dataManagerNew->name." <".$dataManagerNew->mail.">\r\n";                                    
                $to = $user->email;
                $title = 'New MadAds Media Account Manager ('.$user->company.')';
                $message = "<p>Hello ".$user->name.",</p>";
                $message.="<p>I would like to introduce myself as your new account manager here at MadAds Media.  I will be taking over for ".$dataManagerOld->name.", going forward.</p>";
                $message.='<p>If at any point you have an issue with your account, or need a question answered, please don\'t hesitate to reach out!</p>';
                $message.='<p>I look forward to working with you.</p>';
                $message.='<p>Regards,<br />'.$dataManagerNew->name.'</p>';
                $message.= '<table width="450" cellspacing="3" cellpadding="0" border="0">
                              <tbody><tr>
                                <td width="225" valign="bottom"><font style="color:rgb(204, 0, 0); font-family:arial,helvetica,sans-serif; font-weight:bold;">'.$dataManagerNew->name.'</font></td>
                                <td align="right"><b><span style="font-size:8.0pt; font-family:Arial,sans-serif; color:#7f7f7f">tel: 856-874-8928</span></b></td>
                              </tr>
                              <tr>
                                <td valign="top"><font style="font-size:12px; font-family:arial,helvetica,sans-serif;">Publisher Relations Associate</font> </td>
                                <td align="right"><a style="color:rgb(204, 0, 0); font-size:11px; font-family:arial,helvetica,sans-serif;" target="_blank" href="mailto:'.$dataManagerNew->mail.'">'.$dataManagerNew->mail.'</a></td>
                              </tr>
                              <tr>
                                <td><img width="170" height="54" src="http://www.madadsmedia.com/images/signature/mam-signature.gif"></td>
                                <td valign="middle" align="right"><span style="font-size:8.0pt; font-family:arial,helvetica,sans-serif; color:#777777">717 Fellowship Rd. Suite D<br>
                                      <span style="font-size:8.0pt; font-family:arial,helvetica,sans-serif; color:#777777">Mount Laurel, NJ 08054</span> </span></td>
                              </tr>
                            </tbody></table>';                    
                if(APPLICATION_ENV!='development')
                    mail($to, $title, $message, $headers); 
            }
        }
        
        $this->_redirect('/administrator/contactnotification/index');
    }
}
?>
