<?php
class Application_Model_DbTable_Country extends Zend_Db_Table_Abstract
{
    protected $_primary = 'id';
    protected $_name = 'country';

    public function getCountry($id)
    {
        if(!intval($id))
            return false;
        $sql = $this->_db->select()
                   ->from($this->_name, array('name'))
                   ->where('id =? ',$id)
                   ->limit(1);

        return $sql->query()->fetchColumn();
    }
}
?>
