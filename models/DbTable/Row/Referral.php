<?php
class Application_Model_DbTable_Row_Referral extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_Referral';
        
      public function getArrayUser()
      {
          $table = new Application_Model_DbTable_Users();
          
          $sql = $table->select()
                       ->where('referral_id = ?', $this->id)
                       ->order('id');
          
          return $table->fetchAll($sql);
      }
      
}
?>
