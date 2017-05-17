<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users registration
 */


class Application_Form_Website extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        $usersModel = new Application_Model_DbTable_Users();
        /*
        $tag_name = new Zend_Form_Element_Text('tag_name');
        $tag_name->setLabel('Tag Name')
		        ->addFilter('StripTags')
		        ->addFilter('StringTrim');
        
        $list = $usersModel->querySelect('category');
        $category = new Zend_Form_Element_Select('category');
        $category->setLabel('Category')
                ->addFilter('Int')
                ->addMultiOption(array('0'=>'Please Select...'))                
        		->addMultiOptions($list);        
        
        $list = $usersModel->querySelect('cat_rubicon');
        $cat_rubicon = new Zend_Form_Element_Select('cat_rubicon');
        $cat_rubicon->setLabel('Category (Rubicon)')
                ->addFilter('Int')
                ->addMultiOption(array('0'=>'Please Select...'))
                ->addMultiOptions($list);        
        */
                
        $floor_pricing = new Zend_Form_Element_Checkbox('floor_pricing');
        $floor_pricing->setLabel('Floor Pricing?')
        ->addFilter('StripTags')
        ->addFilter('StringTrim');

        $factor_revshare = new Zend_Form_Element_Checkbox('factor_revshare');
        $factor_revshare->setLabel('Factor Revshare Into Floor Price')
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
        /*
        $report_csv = new Zend_Form_Element_Checkbox('auto_report_file');
        $report_csv->setLabel('Generate CSV?')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        */
        $notification = new Zend_Form_Element_Checkbox('email_notlive_3day');
        $notification->setLabel('Disable Notifications ?')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        /*
        $limited = new Zend_Form_Element_Checkbox('limited_demand_tag');
        $limited->setLabel('Limited Demand Tags ?')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
		*/		
        $iframe_tags = new Zend_Form_Element_Checkbox('iframe_tags');
        $iframe_tags->setLabel('Iframe Tags')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $lock_tags = new Zend_Form_Element_Checkbox('lock_tags');
        $lock_tags->setLabel('Lock Ad Tags')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $hide_question = new Zend_Form_Element_Checkbox('hide_question');
        $hide_question->setLabel('Hide ? On Ads')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $update_tags = new Zend_Form_Element_Checkbox('update_tags');
        $update_tags->setLabel('Iframe Tags');
        
        $floor_price = new Zend_Form_Element_Text('floor_price');
        $floor_price->setLabel('Floor price')
        ->addFilter('LocalizedToNormalized');
        
        /*
        $list = $usersModel->querySelect('partners');
        $partners = new Zend_Form_Element_MultiCheckbox('partners');
        $partners->setLabel('Ad Partners')
                ->addFilter('Int')
                ->addMultiOption(array('0'=>'Please Select...'))
                ->addMultiOptions($list);    
        */
        $siteURL = new Zend_Form_Element_Textarea('SiteURL');
        $siteURL->setLabel('Site URL')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
	        ->setAttribs(array('cols' => '60', 'rows' => '10' ));
        
        $servingURL = new Zend_Form_Element_Textarea('ServingURL');
        $servingURL->setLabel('Serving URL')
                ->setRequired(false)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
	        ->setAttribs(array('cols' => '60', 'rows' => '10' ));

        $BlockedURL = new Zend_Form_Element_Textarea('BlockedURL');
        $BlockedURL->setLabel('Blocked URL')
                ->setRequired(false)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
	        ->setAttribs(array('cols' => '60', 'rows' => '10' ));
        
        $define_url = new Zend_Form_Element_Text('define_url');
        $define_url->setLabel('Define Page_URL')
		        ->addFilter('StripTags')
		        ->addFilter('StringTrim')
                        //->addValidator('Hostname')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));        
        
        $define_url_check = new Zend_Form_Element_Checkbox('define_url_check');
        $define_url_check->setLabel('Define Page_URL');
        
        $video_ads = new Zend_Form_Element_Checkbox('video_ads');
        $video_ads->setLabel('Video Ads:');
        
        $disable_rubicon_revenue = new Zend_Form_Element_Checkbox('disable_rubicon_revenue');
        $disable_rubicon_revenue->setLabel('Rubicon');
        /*
        $disable_google_revenue = new Zend_Form_Element_Checkbox('disable_google_revenue');
        $disable_google_revenue->setLabel('Google');
        
        $date_disable_google = new Zend_Form_Element_Text('date_disable_google');
        $date_disable_google->setLabel('Date')
		        ->addFilter('StripTags')
		        ->addFilter('StringTrim');
		*/		
        $date_disable_rubicon = new Zend_Form_Element_Text('date_disable_rubicon');
        $date_disable_rubicon->setLabel('Date')
		        ->addFilter('StripTags')
		        ->addFilter('StringTrim');     
        
        $baner_320 = new Zend_Form_Element_Checkbox('baner_320');
        $baner_320->setLabel('320x50 mobile ad size:'); 
        
        $blank_ref_serve = new Zend_Form_Element_Checkbox('blank_ref_serve');
        $blank_ref_serve->setLabel('Enable New Ad Tags:');

        $header_bidding = new Zend_Form_Element_Checkbox('header_bidding');
        $header_bidding->setLabel('Enable Header Bidding:');

        $burst_tag = new Zend_Form_Element_Checkbox('burst_tag');
        $burst_tag->setLabel('Using BurstMedia Tags:');

        $desired_types = new Zend_Form_Element_Select('desired_types');
        $desired_types->setLabel('Desired Ad Types')
            ->addValidator('Int')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addMultiOptions([
                1=>'Banner Ads Only',
                2=>'Video Ads Only',
                3=>'Banner & Video Ads'
            ]);

        $this->addElements(array($siteURL, $servingURL, $BlockedURL, /*$tag_name, $category, $cat_rubicon,*/ $floor_pricing, $factor_revshare, /*$report_csv,*/ $notification, $iframe_tags, $update_tags, $floor_price, /*$partners, $limited,*/ $lock_tags, $hide_question, $define_url_check, $define_url, $video_ads, /*$disable_google_revenue,*/ $disable_rubicon_revenue, /*$date_disable_google,*/ $date_disable_rubicon, $baner_320, $blank_ref_serve, $header_bidding, $burst_tag, $desired_types));
                
    }
}