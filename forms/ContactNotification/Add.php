<?php
class Application_Form_ContactNotification_Add extends Zend_Form{
    
	public $_data;
        public $_dataStaff;
                        
	public function __construct(&$object, &$dataStaff){

        $this->_data = $object;
        $this->_dataStaff = $dataStaff;
                                
        $this->init();
        $this->loadDefaultDecorators();
    }
    
    public function init(){
    	
        $this->setAttrib('id', 'form-contact-add');
        
        $db_adapter = Zend_Db_Table::getDefaultAdapter();
        
        $this->addElement('select', 'staff_id', array(
                    'label' 	        =>	'Staff:',
                    'required'          =>      false, 
                    'filters'           =>      array('StringTrim'),
                    'multiOptions'      =>      $this->_dataStaff,
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div'))),    
        ));  
        
        $this->addElement('text', 'name', array(
                'label' 	=>	'Name:',
                'required'	=>	true,
                'filters'       =>      array('StringTrim'),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
         ));
        
        $this->addElement('text', 'mail', array(
                'label' 	=>	'E-mail:',
                'required'	=>	true,
                'validators'	=>	array(new Zend_Validate_EmailAddress(), 
                                              new Zend_Validate_Db_NoRecordExists(array('adapter'   => $db_adapter,
                                                                                        'table'     => 'contact_notification',
                                                                                        'field'     => 'mail'))),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))),     
         ));
        
         $this->addElement('checkbox','status', array(
                           'label' => 'Active:',
                           'attribs'=>array('id'=>'status_active', 'class'=>'status_active'),
                           'value'=>1,
                           'validators'=>array('Int'),
                           'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox'))),    
         ));
         
         $this->addElement('text', 'livechat', array(
                'label' 	=>	'Livechat:',
                'required'	=>	false,
                'filters'       =>      array('StringTrim'),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                        array('HtmlTag', array('tag' => 'div',  'class' => 'item'))),
         ));
         
         $this->addElement('textarea', 'signature', array(
                'label'    => 'Signature:',
                'required' =>  false,
                'attribs'  => array('cols' => '105', 'rows' => '5'),
                'filters'  =>  array('StringTrim'),
                'decorators'  =>  array('ViewHelper',
                        'Label',
                        'Errors',
                        array('HtmlTag', array('tag' => 'div', 'class' => 'zend-textarea item'))),
        ));
        
         $this->addElement('text', 'host', array(
                'label' 	=>	'Host:',
                'required'	=>	false,
                'filters'       =>      array('StringTrim'),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
         ));
         
         $this->addElement('text', 'user_login', array(
                'label' 	=>	'Login:',
                'required'	=>	false,
                'filters'       =>      array('StringTrim'),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
         ));
            
         $this->addElement('text', 'user_pass', array(
                'label' 	=>	'Password:',
                'required'	=>	false,
                'filters'       =>      array('StringTrim'),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
         ));
         
         $this->addElement('text', 'port', array(
                'label' 	=>	'Port:',
                'required'	=>	false,
                'filters'       =>      array('StringTrim'),
                'validators'        =>	array(new Zend_Validate_Int()),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
         ));
         
         $this->addElement('text', 'ssl', array(
                'label' 	=>	'Protocol:',
                'required'	=>	false,
                'filters'       =>      array('StringTrim'),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
         ));
         
                
        $this->addElement('submit','submit',array('label'=> 'Save'));
        
        $this->setDecorators(array('FormElements',
                             array('HtmlTag', array('tag' => 'div', 'id' => 'default_admin_form')),
                                   'Form', ));
                
    }
       
     
    public function appendData()
    {	            
        $staffID = $this->staff_id->getValue();
        $staffID = $staffID ? $staffID : NULL;
        
          $this->_data->setFromArray(array(
                  'staff_id'  =>      $staffID,
                  'mail'      =>      $this->mail->getValue(),
                  'name'      =>      $this->name->getValue(),
                  'created'   =>      date('Y-m-d'),
                  'status'    =>  $this->status->getValue(),
                  'livechat'  =>     $this->livechat->getValue(),
                  'signature'  =>  $this->signature->getValue(),
                  'host'       =>  $this->host->getValue() ? $this->host->getValue() : NULL,
                  'user_login' =>  $this->user_login->getValue() ? $this->user_login->getValue() : NULL,
                  'user_pass'  =>  $this->user_pass->getValue() ? $this->user_pass->getValue() : NULL,
                  'port'  =>      $this->port->getValue() ? $this->port->getValue() : NULL,
                  'ssl'   =>      $this->ssl->getValue() ? $this->ssl->getValue() : NULL,
          ));                 
    }
    
    public function showDefault()
    {
           $this->setDefaults(array(
                  'host'         =>	    'mail.madadsmedia.com',
                  'port'         =>	    993,
                  'ssl'          =>	    'SSL',                
           ));
    }      
    
}
?>
