<?php
/**
 * Description of SetBurst
 *
 * @author nik
 */
class Application_Form_SetBurst extends Zend_Form 
{
    public function init()
    {
        $isEmptyMessage = 'Fileld is required';

        $cpm = new Zend_Form_Element_Text('cpm');
        $cpm->setLabel('Increase/Decrease CPM: ')
        ->setRequired(true)
        ->addValidator('Float', true, array('locale'=>'en'))        
        ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMessage))
        );
        
        $paid = new Zend_Form_Element_Text('paid');
        $paid->setLabel('Increase/Decrease Paid Impressions: ')
        ->setRequired(true)
        ->addValidator('Float', true, array('locale'=>'en'))        
        ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMessage))
        );
                
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Submit');

        // add elements to form
        $this->addElements(array($cpm, $paid, $submit));
        
    }
}

?>
