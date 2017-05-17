<?php
/**
 * Description of Alerts
 *
 * @author nik
 */
class Application_Model_DbTable_Burst extends Zend_Db_Table_Abstract 
{    
    protected $_name = 'burst_media_tags';
    protected $_rowClass='Application_Model_DbTable_Row_Burst';    
}