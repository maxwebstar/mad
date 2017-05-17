<?php
/**
 * Description of Sizes
 *
 * @author nik
 */
class Application_Model_DbTable_Payments extends Zend_Db_Table_Abstract 
{
    protected $_name = 'payments_due';
    protected $_rowClass='Application_Model_DbTable_Row_Payments';        
}