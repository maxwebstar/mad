<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users registration
 */


class Application_Form_ConfirmUser extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        
        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Message title')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        

        $message = new Zend_Form_Element_Textarea('message');
        $message->setLabel('Message')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );        
        
        $notes = new Zend_Form_Element_Textarea('notes');
        $notes->setLabel('Notes')
                ->addFilter('StripTags');                
        
        $enable_wire = new Zend_Form_Element_Checkbox('enable_wire_transfer');
        $enable_wire->setLabel('Enable Wire Transfer as Payment Option')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $notification = new Zend_Form_Element_Checkbox('notification_control_admin');
        $notification->setLabel('Disable Notification')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $alexa = new Zend_Form_Element_Checkbox('alexa');
        $alexa->setLabel('Alexa Clickstream')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addValidator('GreaterThan', false, array(0));

        $nude = new Zend_Form_Element_Checkbox('nude');
        $nude->setLabel('Google Search - Nude')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addValidator('GreaterThan', false, array(0));

        $porn = new Zend_Form_Element_Checkbox('porn');
        $porn->setLabel('Google Search - Porn')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addValidator('GreaterThan', false, array(0));

        $sex = new Zend_Form_Element_Checkbox('sex');
        $sex->setLabel('Google Search - Sex')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addValidator('GreaterThan', false, array(0));

        $nswf = new Zend_Form_Element_Checkbox('nswf');
        $nswf->setLabel('Google Search - NSFW')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addValidator('GreaterThan', false, array(0));

        $fuck = new Zend_Form_Element_Checkbox('fuck');
        $fuck->setLabel('Google Search - Fuck')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addValidator('GreaterThan', false, array(0));
        
        $represent_domain = new Zend_Form_Element_Checkbox('represent_domain');
        $represent_domain->setLabel('Added to AdX Represented Domains')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addValidator('GreaterThan', false, array(0));
        
        $authorize_domain = new Zend_Form_Element_Checkbox('authorize_domain');
        $authorize_domain->setLabel('Added to Adsense Authorize Domains')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addValidator('GreaterThan', false, array(0));        
        
        $action = new Zend_Form_Element_Radio('action');
        $action->setLabel('Action')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions(array(1=>'Delete User', 2=>'Confirm User', 3=>'Send message only'));    
        
                $notinvite = new Zend_Form_Element_Checkbox('inviteAdx');
                
        $notinvite->setLabel("Don't Invite To AdX")
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim');
        
        $invite = new Zend_Form_Element_Text('inviteURL');
        $invite->setLabel('Google AdX Invite URL:')
                //->setRequired(true)
                //>addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
//                ->addValidator('regex', false, array('pattern' => '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',
//                                                     'messages'=>array('regexNotMatch' => 'Please enter a valid URL')))
//   
//                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $auto_min_cpm = new Zend_Form_Element_Checkbox('auto_min_cpm');
        $auto_min_cpm->setLabel('Auto-Approve Min. CPM')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $revShare_date = new Zend_Form_Element_Text('revShare_date', array('isArray'=>true));
        $revShare_date->setLabel('RevShare date')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        
        
        $revShare_price = new Zend_Form_Element_Text('revShare_price', array('isArray'=>true));
        $revShare_price->setLabel('RevShare price')
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
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($title, $message, $enable_wire, $notification, $action, $notinvite, $invite, $alexa, $nude, $porn, $sex, $nswf, $fuck, $represent_domain, $authorize_domain, $notes, $auto_min_cpm, $revShare_date, $revShare_price, $desired_types, $submit));
        
    }
}