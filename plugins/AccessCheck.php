<?php

    class Application_Plugin_AccessCheck extends Zend_Controller_Plugin_Abstract{
        
        private $_acl = null;
        private $_auth = null;
        
        public function __construct(Zend_Acl $acl, Zend_Auth $auth){
            
                $this->_acl = $acl;
                $this->_auth = $auth;
        }
        
        public function preDispatch(Zend_Controller_Request_Abstract $request){
             
            //get controller and action name            
            $resource = $request->getModuleName().'_'.$request->getControllerName();
            $action = $request->getActionName();
            $action = ($action == 'ref' && $resource == 'default_index') ? 'index' : $action;
            
            $identity = $this->_auth->getStorage()->read();
            
            //if user not login his role is guest
            $role = !empty($identity->role) ? $identity->role : 'guest';
            /*
            if(APPLICATION_ENV=='development' && isset($identity) && $identity->update_pass!=1 && $request->getControllerName()!='update-password'){
	            if($identity->update_pass!=1){
	                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
	                $redirector->gotoUrl('/update-password/');		
	            }
            }
            */
            //if user not access controller and action, redirect login page
            if(!$this->_acl->isAllowed($role, $resource, $action)){     
//                $request->setModuleName('default')->setControllerName('auth')->setActionName('login');
//                $this->_redirect('/login/');                
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $redirector->gotoUrl('/login/');
//                $redirector->gotoUrl('https://madadsmedia.com/login/');
            }
             
        }
    }