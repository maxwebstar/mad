<?php
class Application_Model_DbTable_Row_User extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_Users';
 
      public function getArraySite()
      {
          $table = new Application_Model_DbTable_Sites();
          
          $sql = $table->select()
                       ->where('PubID = ?', $this->id);
          
          return $table->fetchAll($sql);
      }
      
      public function getRevenue()
      {
          $table = new Application_Model_DbTable_PaymentDue();

          $sql = $table->select()->setIntegrityCheck(false)
                       ->from(array('p' => 'payments_due'),
                              array('SUM(p.revenue) AS revenue'))
                       ->where('p.PubID = ?', $this->id)
                       ->group('p.PubID');
          
          $data = $table->fetchAll($sql);
 
          $revenue = 0;
          
          if(count($data)) $revenue = number_format($data[0]->revenue, 2, ".", "");  
          
          return $revenue;
      }      
      
}
?>
