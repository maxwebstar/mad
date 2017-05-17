<?php
class Application_Model_DbTable_User_NewPaymentInfoChanges extends Zend_Db_Table_Abstract
{
    protected $_name = 'user_new_payment_info_changes';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_User_NewPaymentInfoChanges';

    public function getData($PubID)
    {
        $select = $this->_db->select()
                            ->from($this->_name)
                            ->where('PubID = ?', $PubID);
        return $this->_db->fetchAll($select);
    }

    public function getLastData($PubID)
    {
        $select = $this->_db->select()
                            ->from($this->_name,array('changed'))
                            ->where('PubID = ?', $PubID)
                            ->order('changed DESC')
                            ->limit(1);
        return $this->_db->fetchRow($select);
	}
}
?>
