<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users payments
 */


class Application_Form_Placment extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('placment');
        
        $isEmptyMessage = 'Fileld is required';
        $usersModel = new Application_Model_DbTable_Users();

        $zone = new Zend_Form_Element_Text('zone');
        $zone->setLabel('Zone Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');       

        $site = new Zend_Form_Element_Text('site');
        $site->setLabel('Site')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');       

        $UName = new Zend_Form_Element_Text('UName');
        $UName->setLabel('UName')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');       
        
        $list = $usersModel->querySelect('users');
        $containing = new Zend_Form_Element_Select('containing');
        $containing->setLabel('Containing')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions($list);     
                                
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($zone, $site, $UName, $containing, $submit));
                 
                           
    }
    
}