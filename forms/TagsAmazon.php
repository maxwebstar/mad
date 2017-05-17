<?php

class Application_Form_TagsAmazon extends Zend_Form{

    public function init(){
        //name form
        $this->setName('tags');

        $isEmptyMessage = 'Field is required';

        $slot_uuid = new Zend_Form_Element_Text('slot_uuid', array('isArray'=>true));
        $slot_uuid->setLabel('slot_uuid=')
            ->addValidator('Int');


        //submit button
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setValue('Login');

        //add elements to form
        $this->addElements(array($slot_uuid, $submit));
        $this->setMethod('post');
    }
}