<?php
class Application_Model_DbTable_Pdf_Entity extends Zend_Db_Table_Abstract
{
    protected $_name = 'pdf_entity';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_Pdf_Entity';
  
    public function check($PubID)
    {
        $select = $this->_db->select()
                       ->from($this->_name, array('exist'=>'PubID'))
                       ->where('PubID = ?', $PubID);
                
        $result = $this->getAdapter()->fetchRow($select);

        return $result['exist'];                     
    }
    
}
?>
