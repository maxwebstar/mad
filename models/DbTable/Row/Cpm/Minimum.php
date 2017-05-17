<?php
class Application_Model_DbTable_Row_Cpm_Minimum extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_Cpm_Minimum';
      
      public function _postDelete()
      { 
          /*$path = $this->getPath('full');
      
          My_Advanced::deleteData($path.'/iframe');
          My_Advanced::deleteData($path);*/
      }
            
      public function deleteData($status = 3)
      {
          $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
          $tableFile = new Application_Model_DbTable_Cpm_File();
                              
          $sql = $tableCpm->select()
                          ->where('status = ?', $status)
                          ->where('SiteID = ?', $this->SiteID);
          
          $tempCpm = $tableCpm->fetchAll($sql);
          
          $returnDynamic = 0;
          foreach($tempCpm as $iter){ 
             
              if($iter->status == 3){ $returnDynamic = $iter->dynamic; }
              
              $tableFile->delete('minimum_cpm_id = '.$iter->id);
              $iter->delete(); 
          } 
          
          return $returnDynamic;
      }
      
      public function updateForDelete($status = 3)
      {
          $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
                    
          $tableCpm->update(array('status' => 4), array('status = ?' => $status, 'SiteID = ?' => $this->SiteID));    
      }
     
      public function deleteAllData()
      {
          $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
          $tableFile = new Application_Model_DbTable_Cpm_File();
          $tableBurst = new Application_Model_DbTable_Cpm_Burst();
          $tableMessage = new Application_Model_DbTable_Cpm_Message();
          
          $sql = $tableCpm->select()->where('SiteID = ?', $this->SiteID);
          
          $dataCpm = $tableCpm->fetchAll($sql);
          
          $returnDynamic = 0;
          foreach($dataCpm as $iter){ 
          
              if($iter->status == 3){ $returnDynamic = $iter->dynamic; }
              
              $tableFile->delete('minimum_cpm_id = '.$iter->id);
              $tableBurst->delete('minimum_cpm_id = '.$iter->id);
              $tableMessage->delete('minimum_cpm_id = '.$iter->id);
              
              $iter->delete();                
          }
          
          return $returnDynamic;
      }
      
      public function checkPrevApprove()
      {
          $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
          
          $sql = $tableCpm->select()
                          ->where('SiteID = ?', $this->SiteID)  
                          ->where('status = 3');
          
          return $tableCpm->fetchRow($sql);
                    
      }
      
      public function getPath($type = 'full')
      {
          $path['base'] = 'tags/'.$this->PubID.'/'.$this->SiteID.'/default';
          $path['full'] = 'tags/'.$this->PubID.'/'.$this->SiteID.'/default/'.$this->dynamic;
          
          $path['user'] = 'tags/'.$this->PubID;
          $path['site'] = 'tags/'.$this->PubID.'/'.$this->SiteID;
          
          return $path[$type];
      }
      
      public function getPathTest($type = 'full')
      {          
          $date = date('Y-m-d');
          
          $path['base'] = 'tags-test/'.$date.'/'.$this->PubID.'/'.$this->SiteID.'/default';
          $path['full'] = 'tags-test/'.$date.'/'.$this->PubID.'/'.$this->SiteID.'/default/'.$this->dynamic;
       
          $path['date'] = 'tags-test/'.$date;
          $path['user'] = 'tags-test/'.$date.'/'.$this->PubID;
          $path['site'] = 'tags-test/'.$date.'/'.$this->PubID.'/'.$this->SiteID;
          
          return $path[$type];
      }
      
      public function getPathAsync()
      {
          return 'tags/'.$this->PubID.'/'.$this->SiteID.'/async';
      }
      
      public function getArrayFile($exist = false)
      {                    
          $tableFile = new Application_Model_DbTable_Cpm_File();
          
          $sql = $tableFile->select()->where('minimum_cpm_id = ?', $this->id);
          
          if($exist) $sql->where('file IS NOT NULL');
          
          return $tableFile->fetchAll($sql);                           
      }
      
      public function createDynamic()
      {
          $this->dynamic = rand(0, 10000000);
      }
      
      public function checkPath()
      {          
          $path['user'] = $this->getPath('user');
          $path['site'] = $this->getPath('site');
          $path['base'] = $this->getPath('base');
          $path['full'] = $this->getPath('full');
          
          /*if (!is_dir($path['user'])) {
                 mkdir($path['user']);
                 chmod($path['user'], 0777);
          }
          if (!is_dir($path['site'])) {
                 mkdir($path['site']);
                 chmod($path['site'], 0777);
          }
          if (!is_dir($path['base'])) {
                 mkdir($path['base']);
                 chmod($path['base'], 0777);
          }
          if (!is_dir($path['full'])) {
                 mkdir($path['full']);
                 chmod($path['full'], 0777);
          }*/
      }
      
      public function checkPathIframe()
      {
          $path = $this->getPath('full').'/iframe';
          
          /*if (!is_dir($path)) {
                 mkdir($path);
                 chmod($path, 0777);
          }*/          
      }
      
      public function checkPathTest()
      {
          $path['date'] = $this->getPathTest('date');
          $path['user'] = $this->getPathTest('user');
          $path['site'] = $this->getPathTest('site');
          $path['base'] = $this->getPathTest('base');
          $path['full'] = $this->getPathTest('full');
          
          /*if (!is_dir($path['date'])) {
                 mkdir($path['date']);
                 chmod($path['date'], 0777);
          }
          if (!is_dir($path['user'])) {
                 mkdir($path['user']);
                 chmod($path['user'], 0777);
          }
          if (!is_dir($path['site'])) {
                 mkdir($path['site']);
                 chmod($path['site'], 0777);
          }
          if (!is_dir($path['base'])) {
                 mkdir($path['base']);
                 chmod($path['base'], 0777);
          }
          if (!is_dir($path['full'])) {
                 mkdir($path['full']);
                 chmod($path['full'], 0777);
          }*/
      }
      
      public function checkPathAsync()
      {
          $path = $this->getPathAsync();
          
          /*if (!is_dir($path)) {
                 mkdir($path);
                 chmod($path, 0777);
          }*/
      }
      
      public function getArraySite()
      {
          $tableSite = new Application_Model_DbTable_Sites();
          
          return $tableSite->getArraySites($this->PubID);
      }
                  
      public function getStatus()
      {
         $data = array(1 => 'new', 2 => 'reject', 3 => 'approved');
         
         return $data[$this->status];
      }
      
            
      public function getPrevFloor()
      {
          $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
          
          $sql = $tableCpm->select()
                          ->where('status = 3')
                          ->where('SiteID = ?', $this->SiteID)
                          ->order('id DESC');
          
          $dataCpm = $tableCpm->fetchRow($sql);
          
          if(count($dataCpm)) $this->prev_cpm = $dataCpm->cpm;
          else                $this->prev_cpm = 'Max Fill';
      }
      
      public function getSiteName()
      {
          $db_adapter = Zend_Db_Table::getDefaultAdapter();
          
          $sql = $db_adapter->select()
                            ->from('sites', array('SiteName'))
                            ->where('SiteID = ?', $this->SiteID);
          
          $data = $db_adapter->fetchRow($sql);
          
          unset($db_adapter);
          
          return $data['SiteName'];          
      }
      
      public function getUserEmail()
      {
          $db_adapter = Zend_Db_Table::getDefaultAdapter();
          
          $sql = $db_adapter->select()
                            ->from('users', array('email'))
                            ->where('id = ?', $this->PubID);
          
          $data = $db_adapter->fetchRow($sql);
          
          unset($db_adapter);
          
          return $data['email'];      
      }
      
}
?>
