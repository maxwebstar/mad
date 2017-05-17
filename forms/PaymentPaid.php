<?php
class Application_Form_PaymentPaid extends Zend_Form{
    public function init(){
        $isEmptyMessage = 'Fileld is required';

        $year = new Zend_Form_Element_Hidden('year');
        $year->setLabel('year')
            ->addFilter('Int')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $month = new Zend_Form_Element_Hidden('month');
        $month->setLabel('month')
            ->addFilter('Int')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $pub_id = new Zend_Form_Element_Hidden('pub_id');
        $pub_id->setLabel('pub_id')
            ->addFilter('Int')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $comment = new Zend_Form_Element_Textarea('comment', array('rows'=>7, 'cols'=>50));
        $comment->setLabel('Comment')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Submit');

        // add elements to form
        $this->addElements(array($year, $month, $pub_id, $comment, $submit));

    }
}