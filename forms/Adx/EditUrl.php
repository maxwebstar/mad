<?php
class Application_Form_Adx_EditUrl extends Zend_Form{
    
    public function init()
    {
        // form name
        $this->setName('adx-edit');
        
        $isEmptyMessage = 'Fileld is required';       

        $inviteUrl = new Zend_Form_Element_Text('inviteURL');
        $inviteUrl->setLabel('Invite Url:')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage))); 
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($inviteUrl, $submit));
          
    }
}
?>
