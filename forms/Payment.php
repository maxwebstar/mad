<?php
/**
 * 
 * @author tim
 * @copyright 2011
 * 
 * class for users payments
 */


class Application_Form_Payment extends Zend_Form
{    
    public function init()
    {
        // form name
        $this->setName('users');
        
        $isEmptyMessage = 'Fileld is required';
        $usersModel = new Application_Model_DbTable_Users();
        
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Payee Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        
        
        $payType = new Zend_Form_Element_Radio('payType');
        $payType->setLabel('Payee Type')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions(array(1=>'Individual/Sole Proprietor', 2=>'Business'));        

        $street1 = new Zend_Form_Element_Text('street1');
        $street1->setLabel('Street')
		        ->setRequired(true)
		        ->addValidator('NotEmpty', true,
		        		array('messages' => array('isEmpty' => $isEmptyMessage))
		        )        
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        
        
        $street2 = new Zend_Form_Element_Text('street2');
        $street2->setLabel('Street')
		        ->setRequired(true)
		        ->addValidator('NotEmpty', true,
		        		array('messages' => array('isEmpty' => $isEmptyMessage))
		        )        
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        
        
        $city = new Zend_Form_Element_Text('city');
        $city->setLabel('City')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        
        
        $state = new Zend_Form_Element_Text('state');
        $state->setLabel('State or Province')
                ->addFilter('StripTags');        

        $zip = new Zend_Form_Element_Text('zip');
        $zip->setLabel('Zip / Postal Code')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        

        $list = $usersModel->querySelect('country');
        $country = new Zend_Form_Element_Select('country');
        $country->setLabel('Country')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions($list);        

        $list = $usersModel->querySelect('payment');
        $paymentAmout = new Zend_Form_Element_Select('paymentAmout');
        $paymentAmout->setLabel('Minimum Payment Amount')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions($list);        
        
        $paymentBy = new Zend_Form_Element_Select('paymentBy');
        $paymentBy->setLabel('Payment Method')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage)));
        
        //Payment by PayPal

        $paypalmail = new Zend_Form_Element_Text('paypalmail');
        $paypalmail->setLabel('PayPal Email')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator('EmailAddress', true, array('messages'=>array(
        Zend_Validate_EmailAddress::INVALID_FORMAT => "Is not a valid email address. Example: you@yourdomain.com",
        Zend_Validate_EmailAddress::INVALID_HOSTNAME => "Is not a valid hostname for email address"
            		)));

        //Payment by Direct Deposit 
        
        $bank = new Zend_Form_Element_Radio('bank');
        $bank->setLabel('Bank Country')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions(array(1=>'Canadian Bank', 2=>'US Bank'));        

        $accName = new Zend_Form_Element_Password('accName');
        $accName->setLabel('Account Name: ')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            );

        $accNameReal = new Zend_Form_Element_Text('accNameReal');
        $accNameReal->setLabel('Account Name: ')
        ->setRequired(true)
        ->addValidator('NotEmpty', true,
        		array('messages' => array('isEmpty' => $isEmptyMessage))
        );

        $bankName = new Zend_Form_Element_Text('bankName');
        $bankName->setLabel('Bank Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');        

        $accType = new Zend_Form_Element_Select('accType');
        $accType->setLabel('Account Type')
                ->addFilter('Int')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addMultiOptions(array(1=>'Checking', 2=>'Savings'));        

        $accNumber = new Zend_Form_Element_Text('accNumber');
        $accNumber->setLabel('Account Number')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        $accNumberReal = new Zend_Form_Element_Text('accNumberReal');
        $accNumberReal->setLabel('Account Number')
        ->setRequired(true)
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
                     

        $confirmAccNumber = new Zend_Form_Element_Text('confirmAccNumber');
        $confirmAccNumber->setLabel('Confirm Account Number')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        $confirmAccNumberReal = new Zend_Form_Element_Text('confirmAccNumberReal');
        $confirmAccNumberReal->setLabel('Confirm Account Number')
        ->setRequired(true)
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
        ->addValidator('identical', false, array('token'=>'accNumberReal'))
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
                                              

        $routNumber = new Zend_Form_Element_Text('routNumber');
        $routNumber->setLabel('Routing Number')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addFilter('StripTags')
            ->addFilter('StringTrim'); 

        $routNumberReal = new Zend_Form_Element_Text('routNumberReal');
        $routNumberReal->setLabel('Routing Number')
        ->setRequired(true)
        ->addValidator('NotEmpty', true,
        		array('messages' => array('isEmpty' => $isEmptyMessage))
        )
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
                     

        $confirmRoutNumber = new Zend_Form_Element_Text('confirmRoutNumber');
        $confirmRoutNumber->setLabel('Confirm Routing Number')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
        
        $confirmRoutNumberReal = new Zend_Form_Element_Text('confirmRoutNumberReal');
        $confirmRoutNumberReal->setLabel('Confirm Routing Number')
        ->setRequired(true)
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
        ->addValidator('identical', false, array('token'=>'routNumberReal'))
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
        
        $bankName2 = new Zend_Form_Element_Text('bankName2');
        $bankName2->setLabel('Bank Name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addFilter('StripTags')
                ->addFilter('StringTrim'); 
        
        $bankAdress = new Zend_Form_Element_Text('bankAdress');
        $bankAdress->setLabel('Bank Adress')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMessage))
                )
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $accName2 = new Zend_Form_Element_Password('accName2');
        $accName2->setLabel('Account Name: ')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)));   
        
        $accNumber2 = new Zend_Form_Element_Text('accNumber2');
        $accNumber2->setLabel('Account Number')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
            ->addFilter('StripTags')
            ->addFilter('StringTrim');
        
        $accNumber2Real = new Zend_Form_Element_Text('accNumber2Real');
        $accNumber2Real->setLabel('Account Number')
        ->setRequired(true)
        ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
        
        
        
        $swift = new Zend_Form_Element_Text('swift');
        $swift->setLabel('Swift')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addFilter('StripTags')
                ->addFilter('StringTrim');
        
        $iban = new Zend_Form_Element_Text('iban');
        $iban->setLabel('IBAN')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => $isEmptyMessage)))
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Save');                 
        
        // add elements to form
        $this->addElements(array($name, $payType, $street1, $street2, $city, $state, $zip, $country, $paymentAmout, $paymentBy, $paypalmail, $bank, $accName, $accNameReal, $bankName, $accType, $accNumber, $accNumberReal, $confirmAccNumber, $confirmAccNumberReal, $routNumber, $routNumberReal, $confirmRoutNumber, $confirmRoutNumberReal,$bankName2, $bankAdress, $accName2, $accNumber2, $accNumber2Real, $swift, $iban));
        
    }
}