<?php
class Application_Plugin_HttpsRedirect extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $redirect =false;
        
        if($request->getModuleName()=='default')
            $redirect = true;
        
        if($request->getModuleName()=='default' && $request->getControllerName()=='index')
            $redirect = false;
        
        if($redirect && APPLICATION_ENV!='development'){
            if($_SERVER['HTTPS']!='on'){
                header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
        }elseif(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'){
            header('Location: http://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }       
    }
}