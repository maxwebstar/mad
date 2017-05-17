<?php
class Application_Model_DbTable_Row_Cpm_File extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_Cpm_File';
      
      public function _postDelete()
      { 
         /*unlink($this->getPath().'/'.$this->file);
          
         if($this->iframe) unlink($this->getPath().'/iframe/'.$this->size.'.html');*/            
      }
      
      public function getPath()
      {
          return 'tags/'.$this->PubID.'/'.$this->SiteID.'/default/'.$this->dynamic;
      }
      
      public function checkPathIframe()
      {
          $path = $this->getPath().'/iframe';
          
          /*if (!is_dir($path)) {
                 mkdir($path);
                 chmod($path, 0777);
          }*/          
      }
      
      public function getFile()
      {
          return '/'.$this->getPath().'/'.$this->file;
      }
      
      public function getZoneSize()
      {
          $tableSize = new Application_Model_DbTable_Sizes();
          
          return $tableSize->getZoneSize($this->size_id);
      }
      
      
      
      
}
?>
