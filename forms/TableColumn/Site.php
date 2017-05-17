<?php
class Application_Form_TableColumn_Site extends Zend_Form
{
    
        public $_table;
        public $_dataAuth;
        protected $_arrColumn;
            
        public function __construct(&$arrColumn, &$dataAuth, &$tableColumn){

            $this->_table = $tableColumn;
            $this->_dataAuth = $dataAuth;
            $this->_arrColumn = $arrColumn;
            
            $this->init();
            $this->loadDefaultDecorators();

        }
    
        public function init(){                     
             
             foreach($this->_arrColumn as $column)
             {
                    $this->addElement('select', $column['name'] , array(
                        'label' => $column['label'],
                        'required' => true,
                        'multioptions' => array(0 => 'show', 1 => 'hide'),
                        'decorators' => array('ViewHelper', 'Label',
                                              array('HtmlTag', array('tag' => 'div', 'class' => 'itemColumn')))
                    )); 
             }
        
             $this->addElement('submit','submit',array('label'=> 'Save'));
             
             $this->setDecorators(array('FormElements',
                                  array('HtmlTag', array('tag' => 'div', 'id' => 'formColumn')), 'Form', ));
                
       }
       
       public function saveData()
       {                      
           foreach($this->_arrColumn as $column){
               
               $value = $this->getElement($column['name'])->getValue();
               
               $this->_table->saveColumnStatus($column['id'], $this->_dataAuth->email, $value);
           }          
           
       }
       
       public function showOldData()
       {           
           $value = array();
           
           foreach($this->_arrColumn as $iter){ $value[$iter['name']] = $iter['status'] ? 0 : 1; }
           
           $this->setDefaults($value);
       }
       
       
       
       
}
