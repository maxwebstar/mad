<?php

class Application_Form_SendMail extends Zend_Form
{
	public function init()
	{	
		$isEmptyMessage = 'Fileld is required';
		
		$email = new Zend_Form_Element_Text('email', array("style"=>'width:400px'));
		$email->setLabel('Email')
		->setRequired(true)
		->addFilter('StripTags')
		->addFilter('StringTrim')
		->addValidator('NotEmpty', true,
				array('messages' => array('isEmpty' => $isEmptyMessage))
		)
		->addValidator('EmailAddress', true);
		
		$subject = new Zend_Form_Element_Text('subject', array("style"=>'width:400px'));
		$subject->setLabel('Subject')
		->setRequired(true)
		->addValidator('NotEmpty', true,
				array('messages' => array('isEmpty' => $isEmptyMessage))
		)
		->addFilter('StripTags')
		->addFilter('StringTrim');
		
		$message = new Zend_Form_Element_Textarea('message', array('rows'=>7, 'cols'=>50));
		$message->setLabel('Message')
		->setRequired(true)
		->addValidator('NotEmpty', true,
				array('messages' => array('isEmpty' => $isEmptyMessage))
		);
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue('Submit');
		
		$this->addElements(array($email, $subject, $message, $submit));
		
	}
}