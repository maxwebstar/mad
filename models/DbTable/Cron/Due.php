<?php
class Application_Model_DbTable_Cron_Due extends Zend_Db_Table_Abstract
{
    protected $_name = 'cron_generate_due';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_Cron_Due';
}
?>
