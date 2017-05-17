<?php
/**
 *
 * @author Nick
 *        
 *        
 */

class Application_Form_DueNotes extends Zend_Form
{
	public function init()
	{
		// form name
		$this->setName('users');
	
		$isEmptyMessage = 'Fileld is required';
	
		$comment = new Zend_Form_Element_Textarea('comment', array('rows'=>7, 'cols'=>50));
		$comment->setLabel('Comment')
		->setRequired(true)
		->addValidator('NotEmpty', true,
				array('messages' => array('isEmpty' => $isEmptyMessage))
		);
	
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue('Submit');
	
		// add elements to form
		$this->addElements(array($comment, $submit));
	
	}	
}

?>