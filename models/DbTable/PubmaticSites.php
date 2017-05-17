<?php
/**
 * Description of PubmaticSites
 *
 * @author nik
 */
class Application_Model_DbTable_PubmaticSites extends Zend_Db_Table_Abstract 
{
    protected $_name = 'pubmatic_sites';
    protected $_rowClass='Application_Model_DbTable_Row_Pubmatic';
    
}