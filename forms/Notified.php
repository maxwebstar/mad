<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users registration
 */


class Application_Form_Notified extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('notified');
        
        $isEmptyMessage = 'Fileld is required';
        
        $subject = new Zend_Form_Element_Text('subject', array('style'=>"width:500px"));
        $subject->setLabel('Subject')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags');   
                     
        $message = new Zend_Form_Element_Textarea('message', array('rows'=>10, 'cols'=>60));
        $message->setLabel('Message')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );   
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Send');                 
        
        // add elements to form
        $this->addElements(array($subject, $message, $submit));
        
    }
}