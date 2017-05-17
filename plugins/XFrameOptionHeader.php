<?php
 
class Application_Plugin_XFrameOptionHeader extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
		$this->_response->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
    }
}