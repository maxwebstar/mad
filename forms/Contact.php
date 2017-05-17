<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users registration
 */


class Application_Form_Contact extends Zend_Form
{
    public $_PubID;
    
    public function __construct($PubID = false){

        $this->_PubID = $PubID;
                        
        $this->init();
        $this->loadDefaultDecorators();
    }
    
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        
        $company = new Zend_Form_Element_Text('company');
        $company->setLabel('Company Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        

        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Contact Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        

        //create email field
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator('EmailAddress', true, array('messages'=>array(
        Zend_Validate_EmailAddress::INVALID_FORMAT => "Is not a valid email address. Example: you@yourdomain.com",
        Zend_Validate_EmailAddress::INVALID_HOSTNAME => "Is not a valid hostname for email address"
            		)))
            ->addValidator('Db_NoRecordExists', true, array(
                                                    'table'=>'users',
                                                    'field'=>'email',
                                                    'messages'=>array(
                                                    	Zend_Validate_Db_RecordExists::ERROR_RECORD_FOUND=>'Email already exists'
                                                    )
                                                ));
                                                    
        
        if($this->_PubID) $email->addValidator(new My_Form_Validate_SecondNoExist($this->_PubID));
        
        $phone = new Zend_Form_Element_Text('phone');
        $phone->setLabel('Phone Number')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');     
        
        $notification = new Zend_Form_Element_Checkbox('notification_control_user');
        $notification->setLabel('Notification')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($company, $name, $email, $phone, $notification));
        
    }
}