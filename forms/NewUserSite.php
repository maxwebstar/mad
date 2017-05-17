<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users registration
 */


class Application_Form_NewUserSite extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        $usersModel = new Application_Model_DbTable_Users();
                
        $url = new Zend_Form_Element_Text('url');
        $url->setLabel('URL')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')                                
                ->addFilter(new My_Filter_StrReplaceUrl())                                                
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );
                /*->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                           'table'=>'sites',
                           'field'=>'SiteName',
                           'messages'=>array(Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND=>'URL already exists')
                       ))
                );*/
        
        $hiddenUrl = new Zend_Form_Element_Hidden('hidden_url');
        $hiddenUrl->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                           'table'=>'sites',
                           'field'=>'SiteName',
                           'messages'=>array(Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND=>'URL already exists')
                       ))
                  );

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

        $rub_io = new Zend_Form_Element_Text('rub_io');
        $rub_io->setLabel('IO #')
                ->addFilter('Int');                        
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($url, $hiddenUrl, $title, $description, $keywords, $desired_types, $category, $privacy, $type, $daily, $followers, $rub_io, $submit));
        
    }
}