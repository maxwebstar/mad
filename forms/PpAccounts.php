<?php

class Application_Form_PpAccounts extends Zend_Form{

    public function init(){

        $isEmptyMessage = 'Field is required';

        $user_name = new Zend_Form_Element_Text('user_name');
        $user_name->setLabel('User name:')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages'=>array('isEmpty'=>$isEmptyMessage))
            )
            ->addValidator('stringLength', false, array(0, 40))
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        $acc_num = new Zend_Form_Element_Text('acc_num');
        $acc_num->setLabel('Acc Num:')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages'=>array('isEmpty'=>$isEmptyMessage))
            )
            ->addValidator('Int');

        $token = new Zend_Form_Element_Text('token');
        $token->setLabel('Token:')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages'=>array('isEmpty'=>$isEmptyMessage))
            )
            ->addValidator('stringLength', false, array(32, 32))
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        //submit button
        $submit = new Zend_Form_Element_Submit('Save');
        $submit->setValue('Save');

        //add elements to form
        $this->addElements(array($user_name, $acc_num, $token, $submit));
        $this->setMethod('post');
    }
}