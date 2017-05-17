<?php

/**
 * Description of Images
 *
 * @author Stan
 */
class Application_Form_Csv extends Zend_Form
{

    public function init()
    {
       

        $type = new Zend_Form_Element_Select('type');
        $type->setLabel('Type')
             ->addFilter('Int')
             ->setRequired(true)
             ->addMultiOptions(array(1 => 'Rubicon Report', 2 => 'Optimization Report'));       
        
        $csv = new Zend_Form_Element_File('csv');
        $csv->setLabel('Csv file')
           ->setDestination($_SERVER['DOCUMENT_ROOT'] . '/rubicon_csv')
           ->addValidator('Extension', false, 'csv')
           ->setValueDisabled(true)
           ->setRequired(true); 
        
        // add elements to form
        $this->addElements(array($type, $csv));
        
    }
}

?>
