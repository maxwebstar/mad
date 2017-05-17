<?php

class Application_Model_DbTable_Psa extends Zend_Db_Table_Abstract
{
    protected $_name = 'madads_psa';
    
    public function viewd($userID)
    {        
        $sql = "SELECT SiteID FROM sites WHERE PubID = '".$userID."'";
        
        $dataSite = $this->_db->query($sql)->fetchAll();
        
        foreach($dataSite as $iter){
            
           $sql = "UPDATE `madads_psa` SET `viewed`=1 WHERE `SiteID` = '".$iter['SiteID']."'";
         
           $this->getAdapter()->query($sql);
            
        }        
    }
    
    public function getUserPsaSites($userID)
    {
        $select = $this->_db->select()
                            ->from('madads_psa', array('query_date'=>'madads_psa.query_date',
                                                       'impressions'=>'SUM(madads_psa.num)',
                                                       'domain'=>'IFNULL(madads_psa.src, madads_psa.url_full)'))
                            ->join("sites", "sites.SiteID=madads_psa.SiteID", array())
                           ->where("sites.PubID = $userID")
                           ->group("madads_psa.url_full")
                           ->order("madads_psa.query_date DESC");
        
        $result = $this->getAdapter()->fetchAll($select);
        return $result;                        
    }

    public function getReport($pub_id, $date_from, $date_to, $site_id=null, $ad_size=null)
    {
        $sql = $this->_db->select()
            ->from($this->_name, array(
                'AdSize',
                'query_date',
                'impressions'=>'SUM(num)'
            ))
            ->where('pub_id = ?', $pub_id)
            ->where('query_date >= ?', $date_from)
            ->where('query_date <= ?', $date_to)
            ->group('query_date')
            ->order('query_date DESC');

        if($site_id){
            $sql->where('SiteID = ?', $site_id);
        }

        if($ad_size){
            $sql->where('AdSize = ?', $ad_size);
        }

        $data = $this->_db->query($sql)->fetchAll();
        $dataArr = [];
        if($data){
            foreach($data as $item){
                $dataArr[$item['query_date']] = $item;
            }
        }

        return $dataArr;
    }
}