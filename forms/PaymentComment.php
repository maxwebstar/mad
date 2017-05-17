<?php
/**
 *
 * @author Nick
 *        
 *        
 */

class Application_Form_PaymentComment extends Zend_Form
{
	public function init()
	{
		// form name
		$this->setName('users');
	
		$isEmptyMessage = 'Fileld is required';
		$usersModel = new Application_Model_DbTable_Users();
		
                $date = new Zend_Form_Element_Hidden('date');
                $date->setLabel('Date')
                        ->setRequired(true)
                        ->addValidator('NotEmpty', true,
                            array('messages' => array('isEmpty' => $isEmptyMessage))
                        )
                        ->addFilter('StripTags')
                        ->addFilter('StringTrim');       
                
                $id = new Zend_Form_Element_Hidden('id');
                $id->setLabel('id')	
                ->addFilter('Int')	
                ->setRequired(true)	
                ->addValidator('NotEmpty', true,	
                                array('messages' => array('isEmpty' => $isEmptyMessage))	
                );
                
                
		$comment = new Zend_Form_Element_Textarea('comment', array('rows'=>7, 'cols'=>50));
		$comment->setLabel('Comment')
		->setRequired(true)
		->addValidator('NotEmpty', true,
				array('messages' => array('isEmpty' => $isEmptyMessage))
		);
	
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setValue('Submit');
	
		// add elements to form
		$this->addElements(array($date, $id, $comment, $submit));
	
	}	
}

?>