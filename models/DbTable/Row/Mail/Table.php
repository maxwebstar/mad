<?php
class Application_Model_DbTable_Row_Mail_Table extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_Mail_Table';
      
      public function createMessageID($mailTo, $date)
      {
         return sha1($mailTo .'_'. $date);
      }
      
}
?>
