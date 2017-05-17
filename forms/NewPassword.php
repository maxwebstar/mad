<?php

class Application_Form_NewPassword extends Zend_Form
{
    public function init()
    {        
        // form name
        $this->setName('newpass');

        $isEmptyMessage = 'Fileld is required';
        
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password: ')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );                

        $passwordConfirm = new Zend_Form_Element_Password('passwordConfirm');
        $passwordConfirm->setLabel('Verify Password:')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator('identical', false,
                array('token'=>'password'));                                            

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton')
                ->setValue('Save');                 

        // Добавляем все созданные элементы к форме.
        $this->addElements(array($password, $passwordConfirm, $submit));
    }
}
