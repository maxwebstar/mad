<?php
class Application_Form_Payment_Level extends Zend_Form 
{
    public function init()
    {
        $isEmptyMessage = 'Fileld is required';
        
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name: ')
             ->setRequired(true)
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
 
        $min = new Zend_Form_Element_Text('min');
        $min->setLabel('Min: ')	
            ->setRequired(true)	
            ->addFilter('LocalizedToNormalized')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')    
            /*->addValidator('float', true, array('locale' => 'en_US'))*/    
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $max = new Zend_Form_Element_Text('max');
        $max->setLabel('Max: ')	
            ->setRequired(true)	
            ->addFilter('LocalizedToNormalized')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')    
            /*->addValidator('float', true, array('locale' => 'en_US'))*/    
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $position = new Zend_Form_Element_Text('position');
        $position->setLabel('Position: ')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addValidator('StringLength', false, array('min'=>1, 'max'=>3))        
                ->addValidator('Int');  
        
        $this->addElements(array($name, $min, $max, $position));        
        
    }
}
?>
