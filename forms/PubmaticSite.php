<?php
/**
 * 
 * @author tim
 * @copyright 2014
 * 
 * class for users PubmaticSite
 */


class Application_Form_PubmaticSite extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        
        $publisherId = new Zend_Form_Element_Text('publisherId');
        $publisherId->setLabel('publisherId')
                ->setRequired(true)
                ->addValidator('Int')                
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );        
        
        $domainName = new Zend_Form_Element_Text('domainName');
        $domainName->setLabel('Domain Name: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );                

        $siteUrl = new Zend_Form_Element_Text('siteUrl');
        $siteUrl->setLabel('Site Url: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );                
        
        $verticalId = new Zend_Form_Element_Text('verticalId');
        $verticalId->setLabel('Vertical')
                ->setRequired(true)
                ->addValidator('Int')                
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );        

        $microVerticalId = new Zend_Form_Element_Text('microVerticalId');
        $microVerticalId->setLabel('Micro Vertical')
                ->setRequired(true)
                ->addValidator('Int')                
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );        

        $monthlyImpressions = new Zend_Form_Element_Text('monthlyImpressions');
        $monthlyImpressions->setLabel('Monthly Impressions')
                ->setRequired(true)
                ->addValidator('Int')
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );        

        $platform = new Zend_Form_Element_Select('platform');
        $platform->setLabel('Platform')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions(array(
                		'2'=>'MOBILE_WEB',
                		'4'=>'MOBLIE_APP_IOS',
                		'5'=>'MOBLIE_APP_ANDROID',
                		'1'=>'WEB'));
        
        $privacyPolicyUrl = new Zend_Form_Element_Text('privacyPolicyUrl');
        $privacyPolicyUrl->setLabel('Privacy Policy Url: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');                
        
        $isDefault = new Zend_Form_Element_Text('isDefault');
        $isDefault->setLabel('is Default')
                ->addFilter('Int');        

        $isCoppaCompliant = new Zend_Form_Element_Text('isCoppaCompliant');
        $isCoppaCompliant->setLabel('is Coppa Compliant')
                ->addFilter('Int');        
        
        // add elements to form
        $this->addElements(array($publisherId, $domainName, $siteUrl, $verticalId, $microVerticalId, $monthlyImpressions, $platform, $privacyPolicyUrl, $isDefault, $isCoppaCompliant));
        
    }
}