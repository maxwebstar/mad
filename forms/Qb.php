<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * */


class Application_Form_Qb extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('qb');                
                
        $action = new Zend_Form_Element_Select('action');
        $action->setLabel('Action: ')
	            ->setRequired(true)   
				->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))	                 
                ->addMultiOptions(array(
                	'add_vend'=>'Add vendors',
                	'get_vend'=>'Get vendors',
                	'add_bill'=>'Add bills',
                	'get_bill'=>'Get bills'));        
                
        $new_vend = new Zend_Form_Element_Checkbox('new_vend');
        $new_vend->setLabel('Only new vendors: ')
        			->addFilter('StripTags')
					->addFilter('StringTrim');
               
        $by_id = new Zend_Form_Element_Text('by_id');
        $by_id->setLabel('By id: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('Db_NoRecordExists', true, array(
                                                        'table'=>'users',
                                                        'field'=>'id'
                                                    ));                

        $by_email = new Zend_Form_Element_Text('by_email');
        $by_email->setLabel('By email: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('EmailAddress', true)
            ->addValidator('Db_NoRecordExists', true, array(
                                                        'table'=>'users',
                                                        'field'=>'email'
                                                    ));                
                
        $by_name = new Zend_Form_Element_Text('by_name');
        $by_name->setLabel('By name: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('Db_NoRecordExists', true, array(
                                                        'table'=>'users',
                                                        'field'=>'name'
                                                    ));                
                
        $year = new Zend_Form_Element_Text('year');
        $year->setLabel('Year: ')
                ->addFilter('Int');        

        $month = new Zend_Form_Element_Text('month');
        $month->setLabel('Month: ')
                ->addFilter('Int');        
        
        // add elements to form
        $this->addElements(array($action, $new_vend, $by_id, $by_email, $by_name, $year, $month));
        
    }
}