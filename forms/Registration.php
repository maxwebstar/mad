<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users registration
 */


class Application_Form_Registration extends Zend_Form
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

        $desired_types = new Zend_Form_Element_Select('desired_types');
        $desired_types->setLabel('Desired Ad Types')
            ->addValidator('Int')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addMultiOptions([
                1=>'Banner Ads Only',
                2=>'Video Ads Only',
                3=>'Banner & Video Ads'
            ]);

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
                //->addValidator('Hostname')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('Db_NoRecordExists', true, array(
                    'table'=>'sites',
                    'field'=>'SiteName'
                ));


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
        
        $privacy = new Zend_Form_Element_Radio('privacy');
        $privacy->setLabel('Privacy Policy')
                ->addFilter('Int')
                ->addMultiOptions(array(0=>'No', 1=>'Yes'));        
        
        $type = new Zend_Form_Element_Radio('type');
        $type->setLabel('Site Type')
                ->addFilter('Int')
                ->addMultiOptions(array(1=>'Web Site', 2=>'Application', 3=>'Tumblr Account'));        
        
        $daily = new Zend_Form_Element_Text('daily');
        $daily->setLabel('Daily Visits')
                ->setValue('100')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('Int');                        

        $followers = new Zend_Form_Element_Text('followers');
        $followers->setLabel('Followers')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );    
        
        $term = new Zend_Form_Element_Checkbox('term');
        $term->setLabel("I agree to the Terms Of Service/Publisher Guidelines");

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($email, $url, $password, $passwordConfirm, $desired_types));
        
    }
}