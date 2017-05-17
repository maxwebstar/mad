<?php

class Helper_Csrf extends Zend_Controller_Action_Helper_Abstract
{
	
	public function set_token()
	{
		//CSRF TOKEN
		$token = new Zend_Form_Element_Hash('csrf_token');
		$token->setSalt('stasdre@gmail.com'.time());
		$hash = $token->getHash();
		$session_token = new Zend_Session_Namespace('token');
		$session_token->hash = $hash;
		//
		return $hash;
	}

	public function check_token($hash)
	{
		$session_token = new Zend_Session_Namespace('token');
		if($session_token->hash === $hash)
			return true;
		else 
			return false;
	}
}