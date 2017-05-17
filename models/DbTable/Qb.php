<?php
/**
 * Description of QB
 *
 * @author nik
 */
class Application_Model_DbTable_Qb extends Zend_Db_Table_Abstract 
{
    protected $_name = 'quickbooks_request';
    protected $_rowClass='Application_Model_DbTable_Row_Qb';
    
}