<?php

class ContactsController extends Zend_Controller_Action
{
    public function indexAction()
    {  	
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'contacts';                
        
        $form = new Application_Form_ContactForm();
        $users = new Application_Model_DbTable_Users();
        $session = new Zend_Session_Namespace('Default');
        
        $auth = Zend_Auth::getInstance()->getIdentity(); 

        if($auth){
        	$sitesModel = new Application_Model_DbTable_Sites();
        	$sites = $sitesModel->getArraySites($auth->id);	        
        }
        
        if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }
        
        $this->view->state = $users->querySelect('state');  
        if($this->getRequest()->isPost()){  
        	$token = new Zend_Session_Namespace('token');
        	$stored_token = $token->hash;                          
            $formData = $this->getRequest()->getPost();

            if(count($sites)==0){
            	$form->removeElement('site');	            
            }	
            if($form->isValid($formData) AND $this->_helper->Csrf->check_token($formData['csrf'])){
	                $session->message = 'Your message has been sent.';
					
					if($auth){
						$contactModel = new Application_Model_DbTable_ContactNotification();
						$dataManager = $contactModel->getPublisherContact($auth->id);
					}
					
	                $headers  = 'MIME-Version: 1.0' . "\r\n";
	                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";    
	                $headers .= "From: ".$formData['FullName']." <$formData[email]>\r\n";
	              /*$headers .= "Cc: $formData[email]\r\n";*/
	              /*$headers .= "Bcc: $formData[email]\r\n";*/
	                $to = 'support@madadsmedia.com';
	                if($formData['inquiry'] == 'Billing') $to .= ', billing@madadsmedia.com'; 
	                $title = 'Contact Us - '.$formData['Company'];
	                
	                $message = '<strong>Publisher ID:</strong> '.$formData['publisherID'].'<br />';
	                $message.= '<strong>Full Name:</strong> '.$formData['FullName'].'<br />';
	              /*$message.='<strong>Last Name:</strong> '.$formData['LastName'].'<br />';*/
	
	                if($dataManager)
	                	$message.= '<strong>Account Manager:</strong> '.$dataManager['name'].'<br />';
	                	
	                $message.='<strong>Title:</strong> '.$formData['Title'].'<br />';
	                $message.='<strong>Company:</strong> '.$formData['Company'].'<br />';
	                $message.='<strong>Email:</strong> '.$formData['email'].'<br />';
	                $message.='<strong>Phone:</strong> '.$formData['phone'].'<br />';
	                $message.='<strong>Type of Inquiry:</strong> '.$formData['inquiry'].'<br /><br />';
	                if($sites)
	                	$message.='<strong>Affected Site:</strong> '.$formData['site'].'<br /><br />';
	                $message.='<strong>Question:</strong> '.$formData['question'].'<br /><br />';
	                mail($to, $title, $message, $headers);
	                
	                $this->_redirect('/contact-us/');
            }else{
                    $this->view->formErrors = $form->getMessages();
                    $this->view->formValues = $form->getValues();
                    $this->view->formValues['site'] = $sites; 
                    $this->view->csrf = $this->_helper->Csrf->set_token();                                   
            }
        }else{
        	$this->view->csrf = $this->_helper->Csrf->set_token();
            
            if($auth){
            	
                $this->view->formValues = array('publisherID'=>$auth->id, 
                                                'FullName'=>$auth->name, 
                                                'Company'=>$auth->company, 
                                                'email'=>$auth->email, 
                                                'phone'=>$auth->phone,
                                                'site'=>$sites);        
            }else{
                
                $this->view->formValues = array('publisherID'=>null, 
                                                'FullName'=>null, 
                                                'Company'=>null, 
                                                'email'=>null, 
                                                'phone'=>null,
                                                'Title'=>null,
                                                'question'=>null,
                                                'inquiry'=>null,);  
                
            }
        }        
    }

}