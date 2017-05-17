<?php
class Application_Model_DbTable_Row_Mail_File extends Zend_Db_Table_Row_Abstract{
            
        protected $_tableClass='Application_Model_DbTable_Mail_File';
      
        public function getPath($type = 'original')
        {
            $path = array('original' => 'file/mail/'.$this->mail_id);
            
            return $path[$type];
        }
        
        public function createName()
        {
            return date('Y-m-d').'_'.rand(0, 10000000);
        }
        
        public function getFile()
        {
            return '/'.$this->getPath().'/'.$this->file;
        }
        
        public function getFullFile()
        {
            return $_SERVER['DOCUMENT_ROOT'].'/'.$this->getPath().'/'.$this->file;
        }
        
        /// проверить существует ли путь /// 
        public function checkPath()
        { 
            $original = $this->getPath('original');
                 
            if (!is_dir($original)) {
                 mkdir($original);
                 chmod($original, 0777);
            }
        }
        
        public function _postDelete()
        {       
            unlink($this->getPath('original').'/'.$this->file);
        }
      
}
?>
