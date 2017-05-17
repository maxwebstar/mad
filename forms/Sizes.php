<?php
/**
 * Description of Sizes
 *
 * @author nik
 */
class Application_Form_Sizes extends Zend_Form 
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
        
        $width = new Zend_Form_Element_Text('width');
        $width->setLabel('Width: ')	
        ->addFilter('Int')	
        ->setRequired(true)	
        ->addValidator('NotEmpty', true,	
                        array('messages' => array('isEmpty' => $isEmptyMessage))	
        );
        
        $height = new Zend_Form_Element_Text('height');
        $height->setLabel('Height: ')	
        ->addFilter('Int')	
        ->setRequired(true)	
        ->addValidator('NotEmpty', true,	
                        array('messages' => array('isEmpty' => $isEmptyMessage))	
        );
        
        $rp_zonesize = new Zend_Form_Element_Text('rp_zonesize');
        $rp_zonesize->setLabel('rp_zonesize')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMessage))
        );
        
        $rp_account_max_fill = new Zend_Form_Element_Text('rp_account_max_fill');
        $rp_account_max_fill->setLabel('rp_account(Max Fill)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $rp_account_floor_tag = new Zend_Form_Element_Text('rp_account_floor_tag');
        $rp_account_floor_tag->setLabel('rp_account(Floor Tag)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $rp_account_limited = new Zend_Form_Element_Text('rp_account_limited');
        $rp_account_limited->setLabel('rp_account(Limited)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $rp_site_max_fill = new Zend_Form_Element_Text('rp_site_max_fill');
        $rp_site_max_fill->setLabel('rp_site(Max Fill)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $rp_site_floor_tag = new Zend_Form_Element_Text('rp_site_floor_tag');
        $rp_site_floor_tag->setLabel('rp_site(Floor Tag)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $rp_site_limited = new Zend_Form_Element_Text('rp_site_limited');
        $rp_site_limited->setLabel('rp_site(Limited)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $rp_zonesize_max_fill = new Zend_Form_Element_Text('rp_zonesize_max_fill');
        $rp_zonesize_max_fill->setLabel('rp_zonesize(Max Fill)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $rp_zonesize_floor_tag = new Zend_Form_Element_Text('rp_zonesize_floor_tag');
        $rp_zonesize_floor_tag->setLabel('rp_zonesize(Floor Tag)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $rp_zonesize_limited = new Zend_Form_Element_Text('rp_zonesize_limited');
        $rp_zonesize_limited->setLabel('rp_zonesize(Limited)')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        $file_name = new Zend_Form_Element_Text('file_name');
        $file_name->setLabel('File name')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMessage))
        );
        
        $position = new Zend_Form_Element_Text('position');
        $position->setLabel('Position')
        ->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
        ->addValidator('StringLength', false, array('min'=>1, 'max'=>3))        
        ->addValidator('Int');     
                
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description');
        
        $video = new Zend_Form_Element_Checkbox('video');
        $video->setLabel('Video');        

        $hide_rubicon = new Zend_Form_Element_Checkbox('hide_rubicon');
        $hide_rubicon->setLabel('Hide For Rubicon Sites');        
        
        $hide_google = new Zend_Form_Element_Checkbox('hide_google');
        $hide_google->setLabel('Hide For Google Sites'); 
        
        $active = new Zend_Form_Element_Checkbox('active');
        $active->setLabel('Active'); 

        $individual = new Zend_Form_Element_Checkbox('individual');
        $individual->setLabel('Manually Enable for Websites')
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim');
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Submit');

        // add elements to form
        $this->addElements(array($name, $width, $height, $rp_zonesize, $rp_account_max_fill, $rp_account_floor_tag, $rp_account_limited, $rp_site_max_fill, $rp_site_floor_tag, $rp_site_limited, $rp_zonesize_max_fill, $rp_zonesize_floor_tag, $rp_zonesize_limited, $file_name, $position, $description, $video, $hide_google, $hide_rubicon, $active, $individual, $submit));
        
    }
}

?>
