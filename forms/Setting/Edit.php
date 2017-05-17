<?php
class Application_Form_Setting_Edit extends Zend_Form{
    
    
	public function __construct(){
                
        $this->init();
        $this->loadDefaultDecorators();
        
        }
        
        public function init(){ /* form element mast identical name with db filed */
                
	        	$token = new Zend_Form_Element_Hash('csrf_token');
	        	$token->setSalt('dsfvr7rs8vvr7rfvr7fr6');
	        	$this->addElement($token);
        	
        	
                $this->addElement('text', 'email_report_csv', array(
                    'label' 	=>	'Email Admeld:',
                    'required'	=>	true,
                    'filters'	=>	array('StringTrim'),
                    'validators'=>	array(new Zend_Validate_EmailAddress()),
                     
                 ));
                
                
                 $this->addElement('text', 'admin_email', array(
                    'label' 	=>	'Admin Email:',
                    'required'	=>	true,
                    'filters'	=>	array('StringTrim'),
                    'validators'=>	array(new Zend_Validate_EmailAddress()),
                 ));
                 
                 
                 $this->addElement('text', 'admin_name', array(
                    'label' 	=>	'Admin Name:',
                    'required'	=>	true,
                    'filters'	=>	array('StringTrim'),
                 ));
                   

                 $this->addElement('text', 'dfp_orders', array(
                    'label' 	=>	'DFP orders:',
                    'required'	=>	true,
                    'filters'	=>	array('StringTrim'),
                 ));

                 $this->addElement('text', 'checker_days', array(
                    'label' 	=>	'Content Checker days:',
                    'required'	=>	true,
                    'filters'	=>	array('StringTrim'),
                 ));
                 
                $this->addElement('submit','submit',array('label'=> 'Save'));
                
                $this->setDecorators(array('FormElements',
                                     array('HtmlTag', array('tag' => 'div', 'id' => 'default_admin_form')),
                                           'Form', ));
            
        }
        
        
        public function saveData()
         {	    
            	$table = new Application_Model_DbTable_Setting();
	    	$ConfirRowset = $table->fetchAll();
	    	foreach($this->getElements() AS $Element){
	    		$name = $Element->getName();
	    		foreach($ConfirRowset AS $ConfirRow){
	    			if($ConfirRow->name == $name){
	    				$ConfirRow->value = $this->getValue($name);
	    				$ConfirRow->save();
	    				break;
	    			}
	    		}
	    	}
    		return true;
                
         }
         
         
          public function showOldData()
          { 
             	$table = new Application_Model_DbTable_Setting();
                $ConfirRowset = $table->fetchAll();
                
                foreach($this->getElements() AS $Element){
                        $name = $Element->getName();
                        foreach($ConfirRowset AS $ConfirRow){
                                if($ConfirRow->name == $name){
                                        $this->setDefault($name, $ConfirRow->value);
                                        break;
                                }
                        }
                }

          }
          
}
?>
