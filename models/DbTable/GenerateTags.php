<?php

class Application_Model_DbTable_GenerateTags extends Zend_Db_Table_Abstract 
{    
    protected $_name = 'generate_tags';
    protected $_primary = 'id';    
    protected $_rowClass='Application_Model_DbTable_Row_GenerateTags';    
}