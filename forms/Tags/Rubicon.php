<?php

class Application_Form_Tags_Rubicon extends Zend_Form
{
    public $SiteName = null;
    public $sizes = null;
    public $minCpmVal = null;
    public $minCpmValSelect = null;

    public function __construct($SiteName, $sizes, $minCpmVal) 
    {
        $this->SiteName = $SiteName;
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
             'value'=>5
         ));         
        
        
         $this->addElement('select', 'rubiconType', array(
             'label' => '.',
            'required' => true,
            'multioptions' => array('1'=>'Rubicon (All Demand)', '2'=>'Rubicon (Limited Demand)'),
             'validators'=>array('Int'),
            'decorators'  =>  array('ViewHelper',
                                            'Errors',
                                            'label',
                                             array('HtmlTag', array('tag' => 'div', 'class' => 'zend-select'))),   
         ));         
        
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
         
         $this->addElement('checkbox','create_dfp_passbacks', array(
                           'label' => 'Create DFP Passbacks',
                           'attribs'=>array('id'=>'create_dfp_passbacks_rubicon', 'class'=>'create_dfp_passbacks_rubicon'),
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
             'attribs'=>array('id'=>'cpm_rubicon', 'class'=>'cpm_rubicon', 'dataValues'=>$this->minCpmVal),
            'multioptions' => $this->minCpmValSelect,
            'decorators'  =>  array(array(
                                   'ViewScript', array(
                                   'viewScript' => 'cpm_select_element.phtml',
                                    'class'      => 'zend-select passbacks_rubicon',
                                    'style'=>'display:none'))),   
         ));                  
         
         $this->addElement('hidden', 'estimated_cpm', array(
            'attribs'=>array('id'=>'estimated_cpm_rubicon', 'class'=>'estimated_cpm_rubicon'),
            'decorators'  =>  array(
                                            'ViewHelper',
                                            'Errors',
                                            'label',
                                            array('HtmlTag', array('tag' => 'div', 'class' => 'zend-input passbacks_rubicon', 'style'=>'display:none')))
         ));     

         $this->addElement('hidden', 'estimated_rate', array(
            'attribs'=>array('id'=>'estimated_rate_rubicon', 'class'=>'estimated_rate_rubicon'),
            'decorators'  =>  array(
                                            'ViewHelper',
                                            'Errors',
                                            'label',
                                            array('HtmlTag', array('tag' => 'div', 'class' => 'zend-input passbacks_rubicon', 'style'=>'display:none')))
         ));              
         if($this->sizes){
             foreach ($this->sizes as $size){
                $this->addElement('textarea','passback_'.$size->description, array(
                           'label'   => 'Passback '.$size->description.': ',
                           'attribs' => array('cols' => '90', 'rows' => '7', 'onkeyup'=>'modefiCheckBox(\''.$size->description.'\', \'rubicon\')', 'id'=>'', 'class'=>'passback_'.$size->description.'_rubicon'),
                           'filters'   =>  array('StringTrim'),
                           'decorators'  =>  array('ViewHelper',
                                                   'Label',
                                                   'Errors',
                                                    array('HtmlTag', array('tag' => 'div', 'class' => 'zend-textarea passbacks_rubicon', 'style'=>'display:none'))),   
                ));        
                $this->addElement('checkbox','passback_check_'.$size->description, array(
                                  'label' => 'No passback - keep this ad size on maximum fill.',
                                  'value'=>1,
                                  'attribs' => array('onchange'=>'modefiTextarea(\''.$size->description.'\', \'rubicon\')', 'id'=>'passback_check_'.$size->description.'_rubicon', 'class'=>'passback_check_'.$size->description.'_rubicon'),
                                  'decorators'  =>  array('ViewHelper',
                                                   'Label',
                                                   'Errors',
                                                    array('HtmlTag', array('tag' => 'div', 'class' => 'zend-passback-checkbox passbacks_rubicon', 'style'=>'display:none'))),   
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
    }
}