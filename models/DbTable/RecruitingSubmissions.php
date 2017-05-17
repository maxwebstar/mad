<?php

class Application_Model_DbTable_SubmittedWebsites extends Zend_Db_Table_Abstract {

    protected $_name = 'submitted_websites';

    public function import($data, $staffID) {
        $data = explode("\r\n", $data);
        
        
        $response = array();
        $response['error']=false;
        $count = 0;
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $website) {
                $website=trim($website);
                $http = substr($website, 0, 7);
                if ($http != "http://" || (strstr($website, ",") != false)){
                    $response['error']=true;
                    continue;
                }
                
                $select = $this->select();
                $where = $this->getAdapter()->quoteInto("LOWER(website) = LOWER(?)", $website);

                $result = $this->fetchRow($where);
                
                if (!$result):
                    try{
                    $resdata = array();
                    $row = $this->createRow();  
                    $row->website = $website;
                    $row->date_created = date("Y-m-d H:i:s");
                    $row->created_by = Zend_Auth::getInstance()->getIdentity()->email;
                    $row->staff_id = $staffID;
                    $row->save();
                    $count++;
                    }
                    catch (Exception $e){
                        
                      //$response['data']=$e->getMessage();
                    }
                     

                endif;
            }
        }
        $response['data'] = $count;
        if ($response['error']){
            $response['data']="Bad data found... (Use Form 'http://YOURWEBSITE'... One per line), $count good records imported";
        }
        return $response;
    }

}

?>
