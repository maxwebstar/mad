<?php
/**
 * Description of Alerts
 *
 * @author nik
 */
class Application_Model_DbTable_Alerts extends Zend_Db_Table_Abstract 
{
    //////// status ///////
    // 1 - open (new)    //
    // 2 - closed        //
    ///////////////////////
    
    protected $_name = 'alerts';
    protected $_rowClass='Application_Model_DbTable_Row_Alerts';    
}