<?php
/**
 *
 * @author Nick
 *        
 *        
 */

class Application_Form_TagsFloor extends Zend_Form
{
	public function init(){
	
		//name form
		$this->setName('tags');
	
		$isEmptyMessage = 'Field is required';
	
		$accountRub_id = new Zend_Form_Element_Text('accountRub_id');	
		$accountRub_id->setLabel('Rubicon Account ID:')
		->addFilter('Int')
		->setRequired(true)
		->addValidator('NotEmpty', true,
				array('messages' => array('isEmpty' => $isEmptyMessage))
		);
	
	
		$siteRub_id = new Zend_Form_Element_Text('siteRub_id');
		$siteRub_id->setLabel('Rubicon Site ID: ')	
		->addFilter('Int')	
		->setRequired(true)	
		->addValidator('NotEmpty', true,	
				array('messages' => array('isEmpty' => $isEmptyMessage))	
		);
	
	
		$zoneRub_id = new Zend_Form_Element_Text('zoneRub_id');	
		$zoneRub_id->setLabel('Rubicon Zone ID:')	
		->setRequired(true)	
		->addFilter(new My_Filter_Apostrophes())	
		->addValidator('NotEmpty', true,	
				array('messages' => array('isEmpty' => $isEmptyMessage))	
		);
	
		//submit button	
		$submit = new Zend_Form_Element_Submit('login');	
		$submit->setValue('Login');
	
		//add elements to form	
		$this->addElements(array($accountRub_id, $siteRub_id, $zoneRub_id, $submit));	
		$this->setMethod('post');
	
	}
	
}

?>