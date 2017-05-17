<?php
class Application_Form_Staff extends Zend_Form{
    
	public $_data;
                        
	public function __construct(&$object){

        $this->_data = $object;
                                
        $this->init();
        $this->loadDefaultDecorators();
    }
    
    public function init(){
    	
        
        $this->addPrefixPath('My_Form_Decorator', 
					 'My/Form/Decorator',
					 'decorator'
                             );
    	
        $this->setAttrib('id', 'form-staff');

        
        $db_adapter = Zend_Db_Table::getDefaultAdapter();
        
        $this->addElement('text', 'name', array(
                'label' 	=>	'Name:',
                'required'	=>	true,
                'filters'       =>      array('StringTrim'),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
         ));
        
        $this->addElement('text', 'email', array(
                'label' 	=>	'E-mail:',
                'required'	=>	true,
                'validators'	=>	array(new Zend_Validate_EmailAddress(), 
                                              new Zend_Validate_Db_NoRecordExists(array('adapter'   => $db_adapter,
                                                                                        'table'     => 'staff',
                                                                                        'field'     => 'email',
                                                                                        'exclude'   => array('field' => 'id', 'value' => (isset($this->_data->id) ? $this->_data->id : 0) )))),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
        ));
        
        $this->addElement('text', 'livechat', array(
                'label' 	=>	'Livechat:',
                'required'	=>	true,
                'filters'       =>      array('StringTrim'),
                'decorators'    =>      array('ViewHelper', 'Label', 'Errors',
                                              array('HtmlTag', array('tag' => 'div'))), 
         ));
        
        $this->addElement('textarea', 'signature', array(
                    'label'    => 'Signature:',
                    'required' =>  true,
                    'attribs'  => array('cols' => '105', 'rows' => '5'),
                    'filters'  =>  array('StringTrim'),
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-textarea'))), 
         ));
       
        $this->addElement('checkbox','hidden', array(
                           'label' => 'Hidden:',
                           'attribs'=>array('id'=>'staff_hidden', 'class'=>'staff_hidden'),
                           'validators'=>array('Int'),
                           'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox'))),    
        ));                       
                
        $this->addElement('submit','submit',array('label'=> 'Save'));
        
        $this->setDecorators(array('FormElements',
                             array('HtmlTag', array('tag' => 'div', 'id' => 'default_admin_form')),
                                   'Form', ));
                
    }
       
     
    public function appendData()
    {       
        $this->_data->setFromArray(array(
                  'name'      =>     $this->name->getValue(),
                  'email'     =>     $this->email->getValue(),                  
                  'livechat'  =>     $this->livechat->getValue(),
                  'signature' =>     $this->signature->getValue(), 
                  'hidden'    =>     $this->hidden->getValue(),                
        ));                 
    }
      
    public function showOldData()
    {
           $this->setDefaults(array(
                  'name'       =>	$this->_data->name,
                  'email'      =>       $this->_data->email,
                  'livechat'   =>	$this->_data->livechat,
                  'signature'  =>	$this->_data->signature,
                  'hidden'     =>	$this->_data->hidden,
           ));
    }      
      
}