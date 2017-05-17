<?php

class Application_Form_Tags_Google extends Zend_Form
{
    public $SiteName = null;
    public $PubID = null;
    public $placement = 20688632;
    public $sizes = null;
    public $minCpmVal = null;
    public $minCpmValSelect = null;

    public function __construct($SiteName, $PubID, $sizes, $minCpmVal) 
    {
        $this->SiteName = $SiteName;
        $this->PubID = $PubID;
        $this->sizes = $sizes;
        if($minCpmVal){
            foreach ($minCpmVal as $cpm){
                $this->minCpmVal[$cpm->estim_cpm.':'.$cpm->estim_rate.':'.$cpm->value] = $cpm->value;
                $this->minCpmValSelect[$cpm->value] = $cpm->value;
            }
        }
        $this->init();
        $this->loadDefaultDecorators();         
    } 
    
    public function init() 
    {
        
        $this->setDecorators(array('FormElements',
                             array('HtmlTag', array('tag' => 'div', 'class' => 'default_admin_form')),
                                   'Form', ));
        $this->setAttrib('class', 'minimum-cpm');

         $this->addElement('hidden', 'type', array(
            'required' => true,
             'validators'=>array('Int'),
             'value'=>4
         ));         
        
         if($this->sizes){
             foreach ($this->sizes as $size){
                $this->addElement('text','dfp_'.$size->description, array(
                           'label'   => $size->description.' Dfp Placement:',
                           'required' => true,
                           'filters'   =>  array('StringTrim'),
                           'decorators'  =>  array('ViewHelper',
                                                   'Label',
                                                   'Errors',
                                                    array('HtmlTag', array('tag' => 'div', 'class' => 'zend-input'))),   
                ));        
             }
         }        
        
         $this->addElement('text', 'site_name', array(
            'label' => 'Site Name:',
            'required' => true,
            'filters'   =>  array('StringTrim'),
            'decorators'  =>  array(
                                            'ViewHelper',
                                            'Errors',
                                            'label',
                                            array('HtmlTag', array('tag' => 'div', 'class' => 'zend-input')))
         ));     
         
         if($this->sizes){
             foreach ($this->sizes as $size){
                $this->addElement('text','creative_adexchange_'.$size->description, array(
                           'label'   => 'Creatives '.$size->description.':',
                           'filters'   =>  array('StringTrim'),
                           'decorators'  =>  array('ViewHelper',
                                                   'Label',
                                                   'Errors',
                                                    array('HtmlTag', array('tag' => 'div', 'class' => 'zend-input'))),   
                ));        
             }
         }                 

         $this->addElement('checkbox','multiple', array(
                           'label' => 'This tag will serve on multiple domains',
                           'value'=>0,
                           'validators'=>array('Int'),
                           'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox'))),    
         ));             
         
         $this->addElement('checkbox','create_dfp_passbacks', array(
                           'label' => 'Create DFP Passbacks',
                           'attribs'=>array('id'=>'create_dfp_passbacks_google', 'class'=>'create_dfp_passbacks_google'),
                           'value'=>0,
                           'validators'=>array('Int'),
                           'decorators'  =>  array('ViewHelper',
                                            'Label',
                                            'Errors',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox'))),    
         ));    
         
         $this->addElement('select', 'cpm', array(
             'label' => 'Minimum CPM:',
            'required' => true,
             'attribs'=>array('id'=>'cpm_google', 'class'=>'cpm_google', 'dataValues'=>$this->minCpmVal),
            'multioptions' => $this->minCpmValSelect,
            'decorators'  =>  array(array(
                                   'ViewScript', array(
                                   'viewScript' => 'cpm_select_element.phtml',
                                    'class'      => 'zend-select passbacks_google',
                                    'style'=>'display:none'))),   
         ));                  
         
         $this->addElement('hidden', 'estimated_cpm', array(
            'attribs'=>array('id'=>'estimated_cpm_google', 'class'=>'estimated_cpm_google'),
            'decorators'  =>  array(
                                            'ViewHelper',
                                            'Errors',
                                            'label',
                                            array('HtmlTag', array('tag' => 'div', 'class' => 'zend-input passbacks_google', 'style'=>'display:none')))
         ));     

         $this->addElement('hidden', 'estimated_rate', array(
            'attribs'=>array('id'=>'estimated_rate_google', 'class'=>'estimated_rate_google'),
            'decorators'  =>  array(
                                            'ViewHelper',
                                            'Errors',
                                            'label',
                                            array('HtmlTag', array('tag' => 'div', 'class' => 'zend-input passbacks_google', 'style'=>'display:none')))
         ));              
         if($this->sizes){
             foreach ($this->sizes as $size){
                $this->addElement('textarea','passback_'.$size->description, array(
                           'label'   => 'Passback '.$size->description.': ',
                           'attribs' => array('cols' => '90', 'rows' => '7', 'onkeyup'=>'modefiCheckBox(\''.$size->description.'\', \'google\')', 'id'=>'', 'class'=>'passback_'.$size->description.'_google'),
                           'filters'   =>  array('StringTrim'),
                           'decorators'  =>  array('ViewHelper',
                                                   'Label',
                                                   'Errors',
                                                    array('HtmlTag', array('tag' => 'div', 'class' => 'zend-textarea passbacks_google', 'style'=>'display:none'))),   
                ));        
                $this->addElement('checkbox','passback_check_'.$size->description, array(
                                  'label' => 'No passback - keep this ad size on maximum fill.',
                                  'value'=>1,
                                  'attribs' => array('onchange'=>'modefiTextarea(\''.$size->description.'\', \'google\')', 'id'=>'passback_check_'.$size->description.'_google', 'class'=>'passback_check_'.$size->description.'_google'),
                                  'decorators'  =>  array('ViewHelper',
                                                   'Label',
                                                   'Errors',
                                                    array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox passbacks_google', 'style'=>'display:none'))),   
                ));                 
             }
         }
         
         $this->addElement('submit','submit',array('label'=> 'Save'));
    }
    
    public function setDefaultsValues() 
    {
        $this->setDefault('site_name', $this->SiteName);
        $this->setDefault('estimated_cpm', 'Revshare');
        $this->setDefault('estimated_rate', '100%');
        if($this->sizes){
            foreach ($this->sizes as $size){
                $this->setDefault('dfp_'.$size->description, '/'.$this->placement.'/'.$this->SiteName.'-(ID:'.$this->PubID.')-ROS-'.$size->description);
            }
        }
    }    
}