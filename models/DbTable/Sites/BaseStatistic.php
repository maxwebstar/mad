<?php
class Application_Model_DbTable_Sites_BaseStatistic extends Zend_Db_Table_Abstract
{
    protected $_name = 'site_base_statistic';
    protected $_primary = 'id';
    
    public function getFirstDateByRefID($RefID)
    {
        $sql = $this->_db->select()
                    ->from(array('sb' => $this->_name), array('sb.query_date'))
                    ->join(array('u' => 'users'), 'u.id = sb.PubID', array(''))
                    ->where('referral_id = ?', $RefID)
                    ->order('sb.query_date ASC')
                    ->limit(1);
        
        $data = $this->_db->query($sql)->fetch();
        
        return $data['query_date'];                
    }
    
    public function getFirstDate()
    {
        $sql = $this->_db->select()
                    ->from(array('sb' => $this->_name), array('sb.query_date'))
                    ->order('sb.query_date ASC')
                    ->limit(1);
        
        $data = $this->_db->query($sql)->fetch();
        
        return $data['query_date'];                
    }    

}
