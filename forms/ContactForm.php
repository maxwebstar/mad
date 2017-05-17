<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users registration
 */


class Application_Form_ContactForm extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        $usersModel = new Application_Model_DbTable_Users();
        
        $FullName = new Zend_Form_Element_Text('FullName');
        $FullName->setLabel('Full Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags');        

      /*$LastName = new Zend_Form_Element_Text('LastName');
        $LastName->setLabel('Last Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags');*/        

        $Title = new Zend_Form_Element_Text('Title');
        $Title->setLabel('Title')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags');   
                     
        $Company = new Zend_Form_Element_Text('Company');
        $Company->setLabel('Company')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags');        

        //create email field
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator('EmailAddress', true);                
        
        $phone = new Zend_Form_Element_Text('phone');
        $phone->setLabel('Phone')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags');   
                                     
        $inquiry = new Zend_Form_Element_Select('inquiry');
        $inquiry->setLabel('Type of Inquiry')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions(array('Advertising'=>'Advertising',
                                        'Publisher'=>'Publisher',
                                        'General'=>'General',
                                        'Billing'=>'Billing',  
                )); 
                
        $site = new Zend_Form_Element_Text('site');
        $site->setLabel('Affected Site')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags');                          

        $question = new Zend_Form_Element_Textarea('question');
        $question->setLabel('Question')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags');   
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Submit');                 
        
        // add elements to form
        $this->addElements(array($FullName, $Title, $Company, $email, $phone, $state, $inquiry, $site, $how, $question, $submit));
        
    }
}