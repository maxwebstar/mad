<?php

class Application_Form_Blacklists extends Zend_Form
{
	public function init()
	{	
		$isEmptyMessage = 'Fileld is required';
				
		$list = new Zend_Form_Element_Textarea('list', array('rows'=>7, 'cols'=>50));
		$list->setLabel('List')
		->setRequired(true)
		->addValidator('NotEmpty', true,
				array('messages' => array('isEmpty' => $isEmptyMessage))
		);
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue('Submit');
		
		$this->addElements(array($list, $submit));
		
	}
}