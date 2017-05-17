<?php
/**
 * Description of TagsRubiconNew
 *
 * @author stasdre
 */
class Application_Form_TagsRubiconNew extends Zend_Form 
{
    public function init()
    {
        $this->setName('tags');
        
        $isEmptyMessage = 'Field is required';
        
        $site_name = new Zend_Form_Element_Text('site_name');
        $site_name->setLabel('Site Name:')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );        
        /*        
        $rubiconType = new Zend_Form_Element_Text('rubiconType');
        $rubiconType->setLabel('')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );                
        */
        //submit button
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setValue('Login');
        
        //add elements to form
        $this->addElements(array($site_name, /*$rubiconType,*/ $submit));
        $this->setMethod('post');
        
    }
}

?>
