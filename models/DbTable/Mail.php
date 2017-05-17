<?php
class Application_Model_DbTable_Mail extends Zend_Db_Table_Abstract
{
    protected $_name = 'mail';
    protected $_primary = 'id';
    
    /////////// type ///////////
    // 1 - reachout           //
    // 2 - reject user        //
    // 3 - reject site        //
    // 4 - approve site       //
    // 5 - invite reject site //
    // 6 - reject adx         //
    ////////////////////////////
    
    public function getDataByUser($PubID)
    {
        $sql = $this->_db->select()
                    ->from(array('r' => $this->_name), array('*'))
                    //->joinLeft(array('c' => 'contact_notification'), 'c.id = r.account_manager', array('c.name AS manager_name'))
                    ->joinLeft(array('u' => 'users'),'u.email = r.author', array('u.name AS admin_name'))
                    ->where('r.PubID = ?', $PubID)
                    ->order('r.created DESC');
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getDataByUserWithManager($PubID)
    {
    	$sql = $this->_db->select()
    	->from(array('r' => $this->_name), array('*'))
    	->joinLeft(array('c' => 'contact_notification'), 'c.id = r.account_manager', array('c.name AS manager_name'))
    	->where('r.PubID = ?', $PubID)
    	->order('r.created DESC');
    
    	return $this->_db->query($sql)->fetchAll();
    }
}
?>
