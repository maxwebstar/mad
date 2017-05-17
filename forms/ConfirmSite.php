<?php
class Application_Form_ConfirmSite extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('site');
        
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
        
        
        $enable_wire = new Zend_Form_Element_Checkbox('enable_wire_transfer');
        $enable_wire->setLabel('Enable Wire Transfer as Payment Option')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $notification = new Zend_Form_Element_Checkbox('notification_control_admin');
        $notification->setLabel('Disable Notification')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $action = new Zend_Form_Element_Radio('action');
        $action->setLabel('Action')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions(array(1=>'Delete Site', 2=>'Confirm Site', 3=>'Send message only', 4 => 'Reject Site'));        
        
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

        $changed_url = new Zend_Form_Element_Text('changed_url');
        $changed_url->setLabel('URL')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addFilter(new My_Filter_StrReplaceUrl())
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                   'table'=>'sites',
                   'field'=>'SiteName',
                   'messages'=>array(Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND=>'URL already exists')
               ))
            );

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($title, $message, $enable_wire, $notification, $action, $represent_domain, $authorize_domain, $desired_types, $changed_url, $submit));
        
    }
}