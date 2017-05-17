<?php
class Application_Form_Cpm_Minimum extends Zend_Form{
    
	public $_dataCpm;
                                     
	public function __construct(&$objectCpm){

        $this->_dataCpm = $objectCpm;
        
        $this->init();
        $this->loadDefaultDecorators(); 
    }
    
     public function init(){
         
         
         $this->addElement('select', 'SiteID', array(
            'label' => 'Update Minimum CPM For:',
            'required' => true,
            'multioptions' => $this->_dataCpm->getArraySite(),
            'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-select'))),   
         )); 
         
         
         $this->addElement('select', 'cpm', array(
            'label' => 'Minimum CPM:',
            'required' => true,
            'multioptions' => $this->_dataCpm->getArrayCpm(),
            'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-select'))),   
         )); 
         
         $this->cpm->setRegisterInArrayValidator(false);
         
         $this->addElement('textarea','728x90', array(
                    'label'   => '728x90 Leaderboard Passback Tag:',
                    'attribs' => array('cols' => '90', 'rows' => '7'),
                    'filters'   =>  array('StringTrim'),
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-textarea'))),    
         ));
         $this->addElement('checkbox','passback728x90', array(
                           'label' => 'No passback - keep this ad size on maximum fill.',
                           'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox'))),    
         )); 
                  
         
         $this->addElement('textarea','300x250', array(
                    'label'   => '300x250 Leaderboard Passback Tag:',
                    'attribs' => array('cols' => '90', 'rows' => '7'),
                    'filters'   =>  array('StringTrim'),
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-textarea'))),   
         ));
         $this->addElement('checkbox','passback300x250', array(
                           'label' => 'No passback - keep this ad size on maximum fill.',
                           'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox'))),   
         )); 
         
         
         $this->addElement('textarea','160x600', array(
                    'label'   => '160x600 Leaderboard Passback Tag:',
                    'attribs' => array('cols' => '90', 'rows' => '7'),
                    'filters'   =>  array('StringTrim'),
                    'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-textarea'))),   
         ));
         $this->addElement('checkbox','passback160x600', array(
                           'label' => 'No passback - keep this ad size on maximum fill.',
                           'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox'))),    
         )); 
      
         
         $this->addElement('submit','submit',array('label'=> 'Submit Changes'));
	 
     }
     
     public function appendData()
     {	    
            $this->_dataCpm->setFromArray(array(

                    'SiteID'  =>  $this->SiteID->getValue(),
                    'cpm'     =>  $this->cpm->getValue(),
                
                    '728x90'          =>  $this->getValue('728x90'),
                    'passback728x90'  =>  $this->passback728x90->getValue(),
                
                    '300x250'          =>  $this->getValue('300x250'),
                    'passback300x250'  =>  $this->passback300x250->getValue(),
                
                    '160x600'          =>  $this->getValue('160x600'),
                    'passback160x600'  =>  $this->passback160x600->getValue(),               

            ));
     }
     
     public function showStartData()
     {          
         $this->setDefaults(array(
             '728x90'  => '<!--Partner: ValueClick Media   Size: 728x90 --> <script language="javascript" type="text/javascript">  var dkcb = Math.random();   document.write(\'<script type="text/javascript" language="javascript" src="http://optimized-by.rubiconproject.com/a/dk.js?defaulting_ad=x32a6fa.js&account_id=8223&site_id=14911&size_id=15&size=300x250&cb=\' + dkcb +\'"><\/script>\'); </script>',
             '300x250' => '<!--Partner: ValueClick Media   Size: 300x250 --> <script language="javascript" type="text/javascript">  var dkcb = Math.random();  document.write(\'<script type="text/javascript" language="javascript" src="http://optimized-by.rubiconproject.com/a/dk.js?defaulting_ad=x32a6fa.js&account_id=8223&site_id=14911&size_id=15&size=300x250&cb=\' + dkcb +\'"><\/script>\'); </script>',
             '160x600' => '<!--Partner: ValueClick Media   Size: 160x600 --> <script language="javascript" type="text/javascript">   var dkcb = Math.random();  document.write(\'<script type="text/javascript" language="javascript" src="http://optimized-by.rubiconproject.com/a/dk.js?defaulting_ad=x32a6fa.js&account_id=8223&site_id=14911&size_id=15&size=300x250&cb=\' + dkcb +\'"><\/script>\'); </script>'
         ));
     }
    
}
?>
