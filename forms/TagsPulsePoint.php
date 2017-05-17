<?php

class Application_Form_TagsPulsePoint extends Zend_Form{

    public function init(){
        //name form
        $this->setName('tags');

        $isEmptyMessage = 'Field is required';

        $PulsePubID = new Zend_Form_Element_Text('PulsePubID', array('isArray'=>true));
        $PulsePubID->setLabel('PulsePubID')
            ->addValidator('Int');

        $PulseTagID = new Zend_Form_Element_Text('PulseTagID', array('isArray'=>true));
        $PulseTagID->setLabel('PulseTagID')
            ->addFilter('Int');

        //submit button
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setValue('Login');

        //add elements to form
        $this->addElements(array($PulsePubID, $PulseTagID, $submit));
        $this->setMethod('post');
    }
}