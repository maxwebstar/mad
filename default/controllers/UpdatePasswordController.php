<?php

class UpdatePasswordController extends Zend_Controller_Action
{
    public function preDispatch()
    {
		Zend_Auth::getInstance()->clearIdentity();
    }

	public function indexAction()
	{
		
        $session = new Zend_Session_Namespace('Default');
        $user = new Application_Model_DbTable_Users();
		$helpersFuncs = new My_Helpers();

        if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }
		
        $form = new Application_Form_Forgot();
        
        if($this->getRequest()->isPost()){
            $formData = $this->getRequest()->getPost();
            if($form->isValid($formData)){

                $confirm = md5($helpersFuncs->genereteSalt());

                $user->updatePasswordConfirm($formData['email'], $confirm);

                $link = "http://".$_SERVER['HTTP_HOST']."/update-password/update/".$confirm;

                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers .= "From: Publisher Support <support@madadsmedia.com>\r\n";
                $to = $formData['email'];
                $title = 'Reset Your MadAdsMedia.com Password';
                $message = 'Dear Publisher,<br /><br />';
                $message .= 'Reset your password by following the steps below:<br /><br />';
                $message .= '1. Click this link or copy and paste it into your web browser: '.$link.'<br /><br />';
                $message .= '2. Follow the on-screen instructions to reset your password. <br /><br />';
                $message .= 'Regards,<br />MadAdsMedia.com Staff';
                mail($to, $title, $message, $headers);

                $session->message = 'You should receive an email containing a password reset link shortly.';
                $this->_redirect('/update-password/');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }        
	}
	
	public function updateAction()
	{
        $session = new Zend_Session_Namespace('Default');
        $user = new Application_Model_DbTable_Users();
		$helpersFuncs = new My_Helpers();
	
	    if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }

        if($this->_getParam('code')){
            $userData = $user->getUserByPassUpdate($this->_getParam('code'));

            if(!$userData){
                $this->view->message = 'Not Found';
            }else{
                $this->view->messageTitle = "Change Password";
            }

            $form = new Application_Form_NewPassword();

            if($this->getRequest()->isPost()){
                $formData = $this->getRequest()->getPost();
                if($form->isValid($formData)){
                    $salt = $helpersFuncs->genereteSalt();
                    $password = md5(md5($formData['password']).md5($salt));
                    $user->changePassword($userData['id'], $password, $salt);
                    $session->message = 'Your password has been changed.<br /><br /> You may now sign in using your new password.';
                    $this->_redirect('/update-password/update/');
                }else{
                    $this->view->formErrors = $form->getMessages();
                    $this->view->formValues = $form->getValues();
                }
            }

        }
	}	
}