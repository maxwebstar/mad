<?php
/**
 * Description of Pubmatic
 *
 * @author nik
 */
class Application_Model_DbTable_Pubmatic extends Zend_Db_Table_Abstract 
{
    protected $_name = 'pubmatic_users';
    protected $_rowClass='Application_Model_DbTable_Row_Pubmatic';
    
}