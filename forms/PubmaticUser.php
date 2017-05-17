<?php
/**
 * 
 * @author tim
 * @copyright 2014
 * 
 * class for users PubmaticUser
 */


class Application_Form_PubmaticUser extends Zend_Form
{
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        
        $firstName = new Zend_Form_Element_Text('firstName');
        $firstName->setLabel('First Name: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );                

        $lastName = new Zend_Form_Element_Text('lastName');
        $lastName->setLabel('Last Name: ')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );                

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator('EmailAddress', true);                
        
        $companyName = new Zend_Form_Element_Text('companyName');
        $companyName->setLabel('Company Name: ')
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

        $monthlyImpressions = new Zend_Form_Element_Text('monthlyImpressions');
        $monthlyImpressions->setLabel('Monthly Impressions')
                ->setRequired(true)
                ->addValidator('Int')
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                );        

        $currency = new Zend_Form_Element_Select('currency');
        $currency->setLabel('Currency')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions(array(
                		'AUD'=>'AUD', 
                		'CAD'=>'CAD', 
                		'CHF'=>'CHF', 
                		'EURO'=>'EURO', 
                		'GBP'=>'GBP', 
                		'SEK'=>'SEK',                 		
                		'USD'=>'USD'));        
        
        // add elements to form
        $this->addElements(array($firstName, $lastName, $email, $companyName, $siteUrl, $verticalId, $monthlyImpressions, $currency));
        
    }
}