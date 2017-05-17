<?php
class Application_Form_Sub extends Zend_Form{
    
	public $_data;
                        
	public function __construct(&$object){

        $this->_data = $object;
                                
        $this->init();
        $this->loadDefaultDecorators();
    }
    
    public function init(){
    	
        
        $this->addPrefixPath('My_Form_Decorator', 
                             'My/Form/Decorator',
                             'decorator');
    	
        $this->setAttrib('id', 'form-sub');
        
        $this->addElement('text', 'name', array(
                'label' 	=>	'Name:',
                'required'	=>	true,
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
        $this->_data->setFromArray(array(
                  'name'      =>     $this->name->getValue(),            
        ));                 
    }
      
    public function showOldData()
    {
        $this->setDefaults(array(
                'name'       =>	$this->_data->name,
        ));
    }      
      
}