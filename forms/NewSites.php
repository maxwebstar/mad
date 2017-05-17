<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users payments
 */


class Application_Form_NewSites extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('sites');
        
        $isEmptyMessage = 'Fileld is required';
        $usersModel = new Application_Model_DbTable_Users();
        
        $list = $usersModel->querySelect('users');
        $user = new Zend_Form_Element_Select('user');
        $user->setLabel('User')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions($list);     

        $type = new Zend_Form_Element_Select('type');
        $type->setLabel('Site Type')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addMultiOptions(array(1=>'Website', 2=>'Tumblr', 3=>'Application'));     

        $rubicon_type = new Zend_Form_Element_Select('rubicon_type');
        $rubicon_type->setLabel('Rubicon')
        ->addFilter('Int')
        ->setRequired(true)
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
        ->addMultiOptions(array(1=>'MAM-Rubicon (Profile Customization)', 2=>'MAM-Rubicon (Comics)'));        
        
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Domain name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addFilter('StripTags')
                ->addFilter('StringTrim');      
        
        $url = new Zend_Form_Element_Text('SiteURL');
        $url->setLabel('SiteURL')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addValidator('regex', false, array('pattern' => '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',
                                                     'messages'=>array('regexNotMatch' => 'Please enter a valid Site URL')))
   
                ->addFilter('StripTags')
                ->addFilter('StringTrim'); 

        $floor_pricing = new Zend_Form_Element_Checkbox('floor_pricing');
        $floor_pricing->setLabel('Floor Pricing?')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');       
        
        $report_csv = new Zend_Form_Element_Checkbox('auto_report_file');
        $report_csv->setLabel('Generate CSV?')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $notes = new Zend_Form_Element_Textarea('notes');
        $notes->setLabel('Notes');       
                
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($user, $type, $rubicon_type, $name, $url, $floor_pricing, $report_csv, $notes, $submit));
                 
                           
    }
    
}