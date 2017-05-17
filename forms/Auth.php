<?php
    
class Application_Form_Auth extends Zend_Form{
    
    public function init(){
        //name form
        $this->setName('auth');
        
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
            ->addValidator('EmailAddress', true)
             ->addValidator('Db_RecordExists', false, array(
                                                    'table'=>'users',
                                                    'field'=>'email'
                                                ));
        
        //password field
        $password = new Zend_Form_Element_Password('password');
        
        $password->setLabel('Password: ')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages'=>array('isEmpty'=>$isEmptyMessage))
            );
        
        //submit button
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setValue('Login');
        
        //add elements to form
        $this->addElements(array($email, $password, $submit));
        $this->setMethod('post');
    }
}