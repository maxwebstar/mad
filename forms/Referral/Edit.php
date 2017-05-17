<?php
class Application_Form_Referral_Edit extends Zend_Form{

    public $_dataReferral;
    public $_dataParent;

    public function __construct(&$objectReferral, &$objectParent){

        $this->_dataReferral = $objectReferral;
        $this->_dataParent = $objectParent;

        $this->init();
        $this->loadDefaultDecorators();
    }

    public function init(){

        $dataBaseAdapter = Zend_Db_Table::getDefaultAdapter();

        $contact_notifications_table = new Application_Model_DbTable_ContactNotification();
        $adminData = $contact_notifications_table->getAllData();

        $adminValues[''] = 'Select admin email';
        foreach ($adminData as $user){ $adminValues[$user['mail']] = $user['mail']; }

        $this->addElement('text', 'name', array(
            'label' 	        =>	'Name*:',
            'required'	        =>	true,
            'filters'           =>      array('StringTrim'),
            'validators'	=>	array(new Zend_Validate_Db_NoRecordExists(array('adapter'   => $dataBaseAdapter,
                'table'     => 'referral',
                'field'     => 'name',
                'exclude'   => array('field' => 'id', 'value' => $this->_dataReferral->id)))),
            'decorators'  =>  array('ViewHelper',
                'Label',
                'Errors',
                array('HtmlTag', array('tag' => 'div', 'class' => 'name'))),
        ));

        $this->addElement('select', 'parentID', array(
            'label' 	        =>	'Assign to Existing ID?:',
            'filters'           =>      array('StringTrim'),
            'multiOptions'      =>      $this->_dataParent,
            'decorators'  =>  array('ViewHelper',
                'Label',
                'Errors',
                array('HtmlTag', array('tag' => 'div', 'class' => 'parent'))),
        ));

        $this->addElement('select', 'email', array(
            'label' 	        =>	'Admin email:',
            'filters'           =>      array('StringTrim'),
            'multiOptions'      =>      $adminValues,
            'decorators'  =>  array('ViewHelper',
                'Label',
                'Errors',
                array('HtmlTag', array('tag' => 'div', 'class' => 'email'))),
        ));

        $this->addElement('submit','submit',array('label'=> 'Save'));

        $this->setDecorators(array('FormElements',
            array('HtmlTag', array('tag' => 'div', 'id' => 'default_admin_form')),
            'Form', ));

    }

    public function appendData()
    {
        $adminEmail = $this->email->getValue();
        $adminEmail = $adminEmail ? $adminEmail : NULL;

        $this->_dataReferral->setFromArray(array(
            'name'   =>  $this->name->getValue(),
            'email'  =>  $adminEmail,
            'parentID' =>  $this->parentID->getValue(),
        ));
    }

    public function showOldData()
    {
        $this->setDefaults(array(
            'name'   =>   $this->_dataReferral->name,
            'email'  =>   $this->_dataReferral->email,
            'parentID' =>   $this->_dataReferral->parentID,
        ));

    }

}