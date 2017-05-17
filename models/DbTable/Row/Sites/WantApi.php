<?php
class Application_Model_DbTable_Row_Sites_WantApi extends Zend_Db_Table_Row_Abstract{
            
      protected $_tableClass='Application_Model_DbTable_Sites_WantApi';
      
      public function setAdexchange($dataOld, $dataTemp)
      {
          $result = array();   
          
          foreach($dataTemp as $key => $iter){

              if(isset($dataOld[$key])){
                  if($dataOld[$key] != $iter['adexchange']) $result[$key] = 'google_ad_client="ca-pub-1032345577796158";google_ad_slot="'.$iter['adexchange'].'";google_ad_width='.$iter['width'].';google_ad_height='.$iter['height'].';'; 
                  else $result[$key] = 'google_ad_client="ca-pub-1032345577796158";google_ad_slot="'.$iter['adexchange'].'";google_ad_width='.$iter['width'].';google_ad_height='.$iter['height'].';';
              }   else $result[$key] = 'google_ad_client="ca-pub-1032345577796158";google_ad_slot="'.$iter['adexchange'].'";google_ad_width='.$iter['width'].';google_ad_height='.$iter['height'].';';            
          }
          
          $this->creative_adexchange = serialize($result);          
      }
      
}
?>
