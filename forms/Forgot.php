<?php
    
class Application_Form_Forgot extends Zend_Form{
    
    public function init(){
        //name form
        $this->setName('forgot');
        
        $isEmptyMessage = 'Field is required';
        
        //email field
        $email = new Zend_Form_Element_Text('email');
        
        $email->setLabel('Email:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true, 
                    array('messages'=>array('isEmpty'=>$isEmptyMessage))
            )
            ->addValidator('EmailAddress', true, array('messages'=>array(
        Zend_Validate_EmailAddress::INVALID_FORMAT => "Is not a valid email address. Example: you@yourdomain.com",
        Zend_Validate_EmailAddress::INVALID_HOSTNAME => "Is not a valid hostname for email address"
            		)))
             ->addValidator('Db_RecordExists', false, array(
                                                    'table'=>'users',
                                                    'field'=>'email',
                                                    'messages'=>array(
                                                    	Zend_Validate_Db_RecordExists::ERROR_NO_RECORD_FOUND=>'Email addresses not found'
                                                    )
                                                ));
                    
        //submit button
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Login')
                ->setValue('Submit');
        
        //add elements to form
        $this->addElements(array($email, $submit));
        $this->setMethod('post');
    }
}