<?php
class Application_Model_DbTable_Networks extends Zend_Db_Table_Abstract
{
    protected $_name = 'networks';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_Networks';

    public function getActiveNetworks()
    {
        $sql = $this->_db->select()
            ->from($this->_name, array(
                'id',
                'name',
                'short_name'
            ))
            ->where($this->_name.'.active = 1')
            ->where($this->_name.'.show = 1')
            ->order($this->_name.'.position ASC');

        return $this->_db->query($sql)->fetchAll();
    }

    public function getNetworkById($id)
    {
        $sql = $this->_db->select()
            ->from($this->_name)
            ->where("id = ?", $id);

        return $this->_db->fetchRow($sql);
    }
}