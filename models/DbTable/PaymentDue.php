<?php
class Application_Model_DbTable_PaymentDue extends Zend_Db_Table_Abstract
{
    protected $_name = 'payments_due';
    protected $_primary = 'PubID';
    protected $_rowClass='Application_Model_DbTable_Row_PaymentDue';
    
    public function getLevelByUserID($userID, $limit = 12)
    {
        $sql = $this->_db->select()
                    ->from(array('p' => $this->_name), array('p.revenue AS revenue', 'p.date AS date'))
                    ->join(array('l' => 'payments_level'), ('l.min <= p.revenue AND l.max >= p.revenue'), 
                           array('l.id AS level_id', 'l.name AS level_name', 'l.min AS level_min', 'l.max AS level_max'))
                    ->where('p.PubID = ?', $userID)
                    ->where('DATE_FORMAT(p.date, "%Y-%m") < ?', date('Y-m')) 
                    ->group('p.date')
                    ->order('p.date DESC')
                    ->limit($limit);
        
        return $this->_db->query($sql)->fetchAll();
    }
   
}
?>
