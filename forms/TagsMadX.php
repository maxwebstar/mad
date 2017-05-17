<?php
/**
 * Description of TagsMadX
 *
 * @author stasdre
 */
class Application_Form_TagsMadX extends Zend_Form 
{
    public function init()
    {
        $this->setName('tags');
        
        $isEmptyMessage = 'Field is required';
                
        $kPubID = new Zend_Form_Element_Text('kPubID', array('isArray'=>true));
        $kPubID->setLabel('pubID=')
                ->addValidator('Int');

        $kSiteID = new Zend_Form_Element_Text('kSiteID', array('isArray'=>true));
        $kSiteID->setLabel('siteId=')
                ->addFilter('Int');

        $kadID = new Zend_Form_Element_Text('kadID', array('isArray'=>true));
        $kadID->setLabel('kadId=')
                ->addFilter('Int');

        $kadWidth = new Zend_Form_Element_Text('kadWidth', array('isArray'=>true));
        $kadWidth->setLabel('kadWidth=')
                ->addValidator('Int');

        $kadHeight = new Zend_Form_Element_Text('kadHeight', array('isArray'=>true));
        $kadHeight->setLabel('kadHeight=')
                ->addValidator('Int');

        $kadType = new Zend_Form_Element_Text('kadType', array('isArray'=>true));
        $kadType->setLabel('kadtype=')
                ->addValidator('Int');

        $kadPageUrl = new Zend_Form_Element_Text('kadPageUrl', array('isArray'=>true));
        $kadPageUrl->setLabel('kadpageurl=')
            	->addFilter('StripTags')
				->addFilter('StringTrim');                
        
        //submit button
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setValue('Login');
        
        //add elements to form
        $this->addElements(array($kPubID, $kSiteID, $kadID, $kadWidth, $kadHeight, $kadType, $kadPageUrl, $submit));
        $this->setMethod('post');
    }
}