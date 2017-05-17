<?php
class Application_Model_DbTable_CoSiteApprovals extends Zend_Db_Table_Abstract 
{
    protected $_primary = 'SiteID';
    protected $_name = 'co_site_approvals';
    protected $_rowClass='Application_Model_DbTable_Row_CoSiteApprovals';
}