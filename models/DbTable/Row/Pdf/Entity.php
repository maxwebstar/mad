<?php
class Application_Model_DbTable_Row_Pdf_Entity extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_Pdf_Entity';
      
      public function _postDelete()
      { 
          if(is_file('pdf/file/' . $this->file)) unlink('pdf/file/' . $this->file);
      }
      
}
?>
