<?php
class Application_Form_ReportCsv extends Zend_Form{
    
    public function init(){
       
        $this->setName('report-csv');
        $this->setMethod('post');
        
        $isEmptyMessage = 'Field is required';
               
        $break_size = new Zend_Form_Element_Checkbox('break_size');
        $break_size->setLabel('Break out report by ad size')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim');               
               
        $email = new Zend_Form_Element_Text('email', array('isArray'=>true));        
        $email->setLabel('Email Address:')
              ->setRequired(true)
	      ->addFilter('StripTags')
	      ->addFilter('StringTrim')              
              ->addValidator('NotEmpty', true, array('messages'=>array('isEmpty'=>$isEmptyMessage)));
//			  ->addValidator('EmailAddress', true, array('messages'=>array(
//        Zend_Validate_EmailAddress::INVALID_FORMAT => "Is not a valid email address. Example: you@yourdomain.com",
//        Zend_Validate_EmailAddress::INVALID_HOSTNAME => "Is not a valid hostname for email address"
//            		)));
                       
        $when = new Zend_Form_Element_Select('when');
        $when->setLabel('Send This Report:')
                 ->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addMultiOptions(array('once_day' => 'Once a day',
                                         'once_week' => 'Once a week (every Monday)',
                                         'once_month' => 'Once a month (1st of every month)')); 

        $period = new Zend_Form_Element_Select('period');
        $period->setLabel('Date Range:')
                 ->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addMultiOptions(array('yesterday' => 'Yesterday',
                                         'last7days' => 'Last 7 Days',
                                         'last30days' => 'Last 30 Days',
                                         'thisMonth' => 'This Month')); 

		$timeArr = array();
		for($i=0; $i<=23; $i++){
			$timeArr[$i] = date("H", mktime($i,0,0,01,01,date("Y"))).":00";
		}
		
        $time = new Zend_Form_Element_Select('time');
        $time->setLabel('Time:')
                 ->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addMultiOptions($timeArr); 
		
        $utc = new Zend_Form_Element_Select('utc');
        $utc->setLabel('Time:')
                 ->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim'); 

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setValue('Submit');
        
        $this->addElements(array($email, $when, $period, $time, $utc, $break_size, $submit));        
    }
}