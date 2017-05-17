<?php
/**
 * Description of Sizes
 *
 * @author nik
 */
class Application_Form_Alerts extends Zend_Form 
{
    public function init()
    {
        $isEmptyMessage = 'Fileld is required';

        
        $adminID = new Zend_Form_Element_Text('adminID', array('isArray'=>true));
        $adminID->setLabel('Admin')
        ->setRequired(true)
        ->addFilter('Int')
        ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMessage))
        );        
        
        $date_alert = new Zend_Form_Element_Text('date_alert');
        $date_alert->setLabel('Date')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMessage))
        );
        
                
        $message = new Zend_Form_Element_Textarea('message');
        $message->setLabel('Message')
        ->setRequired(true)
        ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMessage))
        );

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Submit');

        // add elements to form
        $this->addElements(array($adminID, $date_alert, $message, $submit));
        
    }
}