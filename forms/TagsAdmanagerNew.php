<?php
    
class Application_Form_TagsAdmanagerNew extends Zend_Form{
    
    public function init(){
        //name form
        $this->setName('tags');
        
        $isEmptyMessage = 'Field is required';
        
        $accountRub_id = new Zend_Form_Element_Text('accountRub_id');
        $accountRub_id->setLabel('Rubicon Account ID:')
                ->addFilter('Int');
//                ->setRequired(true)
//                ->addValidator('NotEmpty', true,
//                    array('messages' => array('isEmpty' => $isEmptyMessage))
//                );        

        $siteRub_id = new Zend_Form_Element_Text('siteRub_id');
        $siteRub_id->setLabel('Rubicon Site ID: ')
                ->addFilter('Int');
//                ->setRequired(true)
//                ->addValidator('NotEmpty', true,
//                    array('messages' => array('isEmpty' => $isEmptyMessage))
//                );        
                

        $zoneRub_id = new Zend_Form_Element_Text('zoneRub_id');
        $zoneRub_id->setLabel('Rubicon Zone ID:')
                ->addFilter(new My_Filter_Apostrophes());
//                ->setRequired(true)
//               ->addValidator('NotEmpty', true,
//                    array('messages' => array('isEmpty' => $isEmptyMessage))
//                );

                
        $site_name = new Zend_Form_Element_Text('site_name');
        $site_name->setLabel('Site Name:')
                ->addFilter('StripTags')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );        
        
        $video_ads = new Zend_Form_Element_Checkbox('video_ads');
        $video_ads->setLabel('Video Ads:');        
        
        //submit button
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setValue('Login');
        
        //add elements to form
        $this->addElements(array($accountRub_id, $siteRub_id, $zoneRub_id, $site_name, $video_ads, $submit));
        $this->setMethod('post');
    }
}