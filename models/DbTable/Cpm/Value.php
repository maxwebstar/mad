<?php
class Application_Model_DbTable_Cpm_Value extends Zend_Db_Table_Abstract
{
    protected $_name = 'minimum_cpm_value';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_Cpm_Value';

    function getValues($order='position ASC'){
        $sql = $this->_db->select()
            ->from($this->_name)
            ->order($order);

        return $this->_db->query($sql)->fetchAll();
    }
}
