<?php
class Application_Model_DbTable_Row_Cron_Due extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_Cron_Due';
      
      public function getStatus($time)
      {     
          $data = array('1' => 'wait',
                        '2' => 'processing',
                        '3' => 'done');
          
          if($this->date < $time && $this->status == 2){
              
              return '<span style="color: red">Error time out</span>';
              
          }else{
              
              return $data[$this->status];
              
          }       
      }
      
}
?>
