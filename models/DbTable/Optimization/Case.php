<?php
class Application_Model_DbTable_Optimization_Case extends Zend_Db_Table_Abstract
{
    protected $_name = 'optimization_case';
    protected $_primary = 'id';
    
    public function getAllData()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('id ASC');
        
        $data = $this->_db->query($sql)->fetchAll();
        
        $result = array();
        
        foreach($data as $iter){ $result[$iter['id']] = array('name' => $iter['name'], 'color' => $iter['color']); }
        
        return $result;
    }
}
?>
