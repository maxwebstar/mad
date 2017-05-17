<?php
class Application_Form_Cpm_Value extends Zend_Form{
    
	public $_dataValue;
                                     
	public function __construct(&$objectValue){

        $this->_dataValue = $objectValue;
        
        $this->init();
        $this->loadDefaultDecorators(); 
    }
    
    public function init(){

            $this->addElement('text', 'name', array(
                    'label' 	        =>	'Name:',
                    'required'	        =>	true,
                    'filters'           =>      array('StringTrim'),
                    
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'name'))),    
            ));
            
            $this->addElement('text', 'value', array(
                    'label' 	        =>	'Value:',
                    'required'	        =>	true,
                    'filters'           =>      array('StringTrim'),
                    
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'name'))),    
            ));
            
            $this->addElement('text', 'estim_cpm', array(
                    'label' 	        =>	'Estim eCPM:',
                    'required'	        =>	true,
                    'filters'           =>      array('StringTrim'),
                    
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'name'))),    
            ));
            
            $this->addElement('text', 'estim_rate', array(
                    'label' 	        =>	'Estim Fill Rate:',
                    'required'	        =>	true,
                    'filters'           =>      array('StringTrim'),
                    
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'name'))),    
            ));
            
             $this->addElement('text', 'position', array(
                    'label' 	        =>	'Position:',
                    'required'	        =>	true,
                    'filters'           =>      array('StringTrim'),
                    'validators'        =>	array(new Zend_Validate_Int(),
                                                      new Zend_Validate_StringLength(array('max'=>3, 'encoding' => 'UTF-8'))),
                    
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'name'))),    
            ));
            
            $this->addElement('submit','submit',array('label'=> 'Save'));
            
            $this->setDecorators(array('FormElements',
                                 array('HtmlTag', array('tag' => 'div', 'id' => 'default_admin_form')),
                                       'Form', ));
	 
     }
     
     public function appendData()
     {	    
            $this->_dataValue->setFromArray(array(
                'name'        =>  $this->name->getValue(),
                'value'       =>  $this->value->getValue(),
                'estim_cpm'   =>  $this->estim_cpm->getValue(),
                'estim_rate'  =>  $this->estim_rate->getValue(),
                'position'    =>  $this->position->getValue(),
            ));
     }
     
     public function showOldData()
     {
             $this->setDefaults(array( 
                     'name'         =>   $this->_dataValue->name, 
                     'value'        =>   $this->_dataValue->value, 
                     'estim_cpm'    =>   $this->_dataValue->estim_cpm, 
                     'estim_rate'   =>   $this->_dataValue->estim_rate, 
                     'position'     =>   $this->_dataValue->position, 
             ));
     }
     
    
}
?>
