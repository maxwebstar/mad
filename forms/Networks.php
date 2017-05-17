<?php
/**
 * Description of Networks
 *
 * @author nik
 */
class Application_Form_Networks extends Zend_Form
{
    public function init()
    {
        $isEmptyMessage = 'Fileld is required';


        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $short_name = new Zend_Form_Element_Text('short_name');
        $short_name->setLabel('Short name')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $parent_id = new Zend_Form_Element_Text('parent_id');
        $parent_id->setLabel('Parent Network')
            ->setRequired(true)
            ->addValidator('Int')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $sizes_id = new Zend_Form_Element_Text('sizes_id', array('isArray'=>true));
        $sizes_id->setLabel('Sizes')
            ->setRequired(true)
            ->addValidator('Int')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $size_request = new Zend_Form_Element_Text('size_request', array('isArray'=>true));
        $size_request->setLabel('Sizes')
            ->addValidator('Int');

        $only_primary = new Zend_Form_Element_Text('only_primary', array('isArray'=>true));
        $only_primary->setLabel('Sizes')
            ->addValidator('Int');

        $active = new Zend_Form_Element_Checkbox('active');
        $active->setLabel('Active')
            ->addFilter('Int');

        $position = new Zend_Form_Element_Text('position');
        $position->setLabel('Position')
            ->setRequired(true)
            ->addValidator('Int')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $show = new Zend_Form_Element_Checkbox('show');
        $show->setLabel('Show')
            ->addValidator('Int');

        // add elements to form
        $this->addElements(array($name, $short_name, $parent_id, $sizes_id, $size_request, $only_primary, $active, $position, $show));

    }
}