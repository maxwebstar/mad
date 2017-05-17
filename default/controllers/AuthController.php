<?php


class AuthController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_redirect('/login/');
    }
    
    public function loginAction()
    {
        //if the user login redirect to index
        if(Zend_Auth::getInstance()->hasIdentity())
        {    
            $dataAuth = Zend_Auth::getInstance()->getIdentity();            
            if($dataAuth->role != 'user')
            {
            	$usersModel = new Application_Model_DbTable_Users();
            	//$usersModel->emulateOsTicketAuth($dataAuth->email);
                if($dataAuth->role=='support'){
                    $this->_redirect('http://'.$_SERVER['HTTP_HOST'].'/administrator/');
                }else{
                    $this->_redirect('http://'.$_SERVER['HTTP_HOST'].'/administrator/dashboard');
                }
            }
            else 
            	$this->_redirect('https://madadsmedia.com/report');
        }

        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'login';                
        
        $session = new Zend_Session_Namespace('Default');

        //if isset message view
        if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }
        
        $form = new Application_Form_Auth();
        $this->view->form = $form;
        
        $usersModel = new Application_Model_DbTable_Users();
        
        if($this->getRequest()->isPost()){
            $formData = $this->getRequest()->getPost();
            $formData['email'] = $this->_helper->Xss->xss_clean($formData['email']);
            if($form->isValid($formData)){
                $userData = $usersModel->getUserByEmail($this->getRequest()->getPost('email'));
                
                if($userData['active']==1 && $userData['reject']==0){
                    $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
                    $authAdapter->setTableName('users')
                                ->setIdentityColumn('email')
                                ->setCredentialColumn('password');
                                
                    //get form data
                    $email = $this->_helper->Xss->xss_clean($this->getRequest()->getPost('email'));
                    $password = md5(md5($this->getRequest()->getPost('password')).md5($userData['salt']));
                    
                    //substitute the obtained data from the form
                    $authAdapter->setIdentity($email)
                                ->setCredential($password);
                                
                    $auth = Zend_Auth::getInstance();
                            
                    $result = $auth->authenticate($authAdapter);
                    
                    if($result->isValid()){
                        if($userData['role'] != 'user')
                        {
                            $namespace = new Zend_Session_Namespace('Zend_Auth');
                            $namespace->setExpirationSeconds(14400);
                        }
                        //get data about user
                        $identity = $authAdapter->getResultRowObject();
                        
                        //set user premisions
                        $identity->role = $userData['role'];   
                        $identity->admin_id = $userData['id'];
    
						$identity->update_pass = $userData['update_pass'];
    
                        $authStorage = $auth->getStorage();
                        $authStorage->write($identity);
                        $ipaddress = $_SERVER["REMOTE_ADDR"];
                        $loginActivity = new Zend_Db_Table('madads_login_activity');
                        $data = array(
                            'ip_address' => $ipaddress,
                            'activity_type' => 'login',
                            'activity_timestamp' => date('Y-m-d H:i:s'),
                            'user_id' => $userData['id']
                        );
                        $loginActivity->insert($data);                        
                     
                    }else{
                        $session->message = 'You have entered an incorrect username or password.';
                    }
                    
                }elseif($userData['active']==0 && $userData['reject']==1){
                    $session->message = 'Unfortunately, your application was rejected. Please check your email for more information.';
                }else{
                    $session->message = 'Your application is still pending.  You will receive an email from us within 1-2 days.';
                }
                
                $this->_redirect('/login/');
            }else{
            	$this->view->message = 'You have entered an incorrect username or password.';
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();                
            }
        }
                
        if(Zend_Registry::get('isMobile')===true){
            $layout->setLayoutPath(APPLICATION_PATH.'/layouts/scripts/mobile');
            $layout->setLayout('index-mobile-content');
            $this->render('login-mobile');
        }
        
    }
    
    public function logoutAction()
    {
    	$auth = Zend_Auth::getInstance()->getIdentity();
    	$usersModel = new Application_Model_DbTable_Users();
    	$usersModel->logoutOsTicketAuth($auth->email);
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/');     
    }
    
}