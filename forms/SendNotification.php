<?php
class Application_Form_SendNotification extends Zend_Form{
    	           
	public function __construct(){

            $this->init();
            $this->loadDefaultDecorators(); 
        }
    
     public function init(){
         
             $this->addElement('select', 'filter', array(
                    'label' => 'To Publishers:',
                    'required' => true,
                    'multioptions' => array('all' => 'All Publishers',
                                            'live' => 'Publishers With Live Sites',
                                            'no_longer' => 'Publishers No Longer Live',
                                            'never_live' => 'Publishers Never Live',
                                            'adx' => 'Adx pubs only'),
                 
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'name'))), 
             )); 
             
            $this->addElement('text', 'admin_email', array(
                                'label' 	=>	'From Email:',
                                'required'	=>	true,
                                'filters'	=>	array('StringTrim'),
                
                                'decorators'  =>  array('ViewHelper',
                                                        'Label',
                                                        'Errors',
                                                         array('HtmlTag', array('tag' => 'div', 'class' => 'name'))), 
             ));
            
            $this->addElement('text', 'admin_name', array(
                                'label' 	=>	'From Name:',
                                'required'	=>	true,
                                'filters'	=>	array('StringTrim'),
                
                                'decorators'  =>  array('ViewHelper',
                                                        'Label',
                                                        'Errors',
                                                         array('HtmlTag', array('tag' => 'div', 'class' => 'name'))), 
             ));
         
            $this->addElement('text', 'subject', array(
                                'label' 	=>	'Subject:',
                                'required'	=>	true,
                                'filters'	=>	array('StringTrim'),
                
                                'decorators'  =>  array('ViewHelper',
                                                        'Label',
                                                        'Errors',
                                                         array('HtmlTag', array('tag' => 'div', 'class' => 'name'))), 
             ));
                 
         
            $this->addElement('textarea','message', array('required' => true));
            
            $this->addElement('submit','submit',array('label'=> 'Send'));
            
            $this->message->addDecorator(new My_Form_Decorator_CKEditor()); 
            
            $this->setDecorators(array('FormElements',
                                 array('HtmlTag', array('tag' => 'div', 'id' => 'default_admin_form')),
                                       'Form', ));
	 
     }
     
    
    
}
?>
