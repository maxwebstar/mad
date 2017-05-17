<?php
class Application_Model_DbTable_Sites_WantIframe extends Zend_Db_Table_Abstract
{
    protected $_name = 'request_iframe';
    protected $_primary = 'SiteID';
    protected $_rowClass='Application_Model_DbTable_Row_Sites_WantIframe';         
}
?>
