<?php
class Application_Form_Task_Add extends Zend_Form{
    
        public $_dataTask;
        public $_dataAuth;
        public $_selectAdmin;
                    
        public function __construct(&$dataAuth, &$dataTask, &$selectAdmin){

            $this->_dataTask = $dataTask;
            $this->_dataAuth = $dataAuth;
            $this->_selectAdmin = $selectAdmin;
            
            $this->init();
            $this->loadDefaultDecorators();

     }
    
     public function init(){

            $isEmptyMessage = 'Field is required';
         
            $name = new Zend_Form_Element_Text('name');
            $name->setLabel('Name')
                 ->setRequired(true)   
                 ->addFilter('StripTags')
		 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
            
            $link = new Zend_Form_Element_Text('link');
            $link->setLabel('Link')
                 ->setRequired(true)   
                 ->addFilter('StripTags')
		 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
           
            
            $for = new Zend_Form_Element_Select('for');
            $for->setLabel('For')
                    ->setRequired(true);
            
            if($this->_dataAuth->role == 'super'){ $for->addMultiOptions(array('all' => 'all'))
                                                       ->addMultiOptions($this->_selectAdmin); 
            
                                    } else { $for->addMultiOptions(array($this->_dataAuth->email => $this->_dataAuth->email)); }                
               
            
            $position = new Zend_Form_Element_Text('position');
            $position->setLabel('Position')
                     ->setRequired(true)
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                     ->addValidator('StringLength', false, array('min'=>1, 'max'=>3))        
                     ->addValidator('Int');
            
            $submit = new Zend_Form_Element_Submit('submit');
            $submit->setValue('Save');
            
            $this->addElements(array($name, $link, $for, $position, $submit));	 
     }
     
     public function appendData()
     {        
            $this->_dataTask->setFromArray(array(
                    'name'     => $this->name->getValue(), 
                    'link'     => $this->link->getValue(),                            
                    'for'      => $this->for->getValue(),
                    'position' => $this->position->getValue(),
                    'status'   => 1, /* not Required this is old params */                    
            ));
     }
    
}
?>
