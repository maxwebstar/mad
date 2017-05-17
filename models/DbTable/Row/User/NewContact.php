<?php
class Application_Model_DbTable_Row_User_NewContact extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_User_NewContact';
      
      public function checkIdentity($dataDb, $dataForm)
      {
          if($dataDb['company'] == $dataForm['company'] && 
             $dataDb['name']    == $dataForm['name']    && 
             $dataDb['email']   == $dataForm['email']   && 
             $dataDb['phone']   == $dataForm['phone']) return true;
          else                                         return false; 
      }
      
      public function checkEmail()
      {
          $table = new Application_Model_DbTable_User_NewContact();
          
          $sql = $table->select()
                       ->where('email = ?', $this->email)
                       ->where('PubID != ?', $this->PubID);
                                  
          return $table->fetchRow($sql);          
      }
      
      public function appendData($dataForm)
      {
          $this->setFromArray(array(
              'company' =>  $dataForm['company'], 
              'name'    =>  $dataForm['name'], 
              'email'   =>  $dataForm['email'], 
              'phone'   =>  $dataForm['phone']
          ));
      }
      
}
?>
