<?php
class Application_Model_DbTable_Cpm_File extends Zend_Db_Table_Abstract
{
    protected $_primary = 'id';
    protected $_name = 'minimum_cpm_file';
    protected $_rowClass='Application_Model_DbTable_Row_Cpm_File';
    
//    protected $_referenceMap = array( 
//            
//            'cpm' => array('columns' => array('minimum_cpm_id'),
//                           'refTableClass' => 'Application_Model_DbTable_Cpm_Minimum',
//                           'refColumns'    => array('id'),
//                           'onDelete'      => self::CASCADE)
//    );
    
    
    public function getData($MinimumCpmID)
    {
        $sql = $this->_db->select()
                    ->from(array('f' => 'minimum_cpm_file'), 
                           array('f.id', 'f.size_id', 'passback', 'data'))
                    ->where('f.minimum_cpm_id = ?', $MinimumCpmID)
                    ->join(array('s' => 'display_size'),('s.id = f.size_id'),
                           array('CONCAT(s.width,"x",s.height) AS size'));
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    
}