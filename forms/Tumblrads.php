<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users registration
 */


class Application_Form_Tumblrads extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        $usersModel = new Application_Model_DbTable_Users();
        
        //create password field
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password: ')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );                

        $passwordConfirm = new Zend_Form_Element_Password('passwordConfirm');
        $passwordConfirm->setLabel('Verify Password: ')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator('identical', false,
                array('token'=>'password'));                                

        $company = new Zend_Form_Element_Text('company');
        $company->setLabel('Company Name')
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
            ->addValidator('EmailAddress', true)
            ->addValidator('Db_NoRecordExists', true, array(
                                                        'table'=>'users',
                                                        'field'=>'email'
                                                    ));                
        
        $phone = new Zend_Form_Element_Text('phone');
        $phone->setLabel('Phone Number')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        
        
        $list = $usersModel->querySelect('timezone');
        $zone = new Zend_Form_Element_Select('zone');
        $zone->setLabel('Time Zone')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions($list);        

        $list = $usersModel->querySelect('country');
        $country = new Zend_Form_Element_Select('country');
        $country->setLabel('Country')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions($list);        

        $state = new Zend_Form_Element_Text('state');
        $state->setLabel('State')
                ->addFilter('StripTags');        
        
        $ssn = new Zend_Form_Element_Text('ssn');
        $ssn->setLabel('SSN or EIN')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');                
        
        $url = new Zend_Form_Element_Text('url');
        $url->setLabel('URL')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');                

        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Title')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');                

        $description = new Zend_Form_Element_Text('description');
        $description->setLabel('Description')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');                

        $keywords = new Zend_Form_Element_Text('keywords');
        $keywords->setLabel('Keywords')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');                
        
        $list = $usersModel->querySelect('category');
        $category = new Zend_Form_Element_Select('category');
        $category->setLabel('Category')
                ->addFilter('Int')
                ->addMultiOptions($list);        
                
        $followers = new Zend_Form_Element_Text('followers');
        $followers->setLabel('Followers')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );                        

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($password, $passwordConfirm, $company, $name, $email, $phone, $zone, $country, $state, $ssn, $url, $title, $description, $keywords, $category, $followers, $submit));
        
    }
}