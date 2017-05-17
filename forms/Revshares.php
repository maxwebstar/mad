<?php

class Application_Form_Revshares extends Zend_Form{

    public function init(){
        //name form
        $this->setName('revshares');

        $isEmptyMessage = 'Field is required';
        $isEmptyMessageNetwork = 'The field must be filled in at least for one AdSize';

        $revshare = new Zend_Form_Element_Text('revshare', ['isArray'=>true]);
        $revshare->setLabel('revshare:')
            ->setRequired(true)
            ->addValidator('NotEmpty', false,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator('Int');


        //add elements to form
        $this->addElements(array($revshare));
        $this->setMethod('post');
    }
}