<?php

class Application_Model_DbTable_Exchange extends Zend_Db_Table_Abstract
{
    protected $_name = 'exchange_stats';
    
    public function getExchangeReport($site, $start_date, $end_date, $ad_size)
    {
    	if($ad_size){
    		$whereSize = 'exchange_stats.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSize = '1=1';
    	}
                
            $strDate = explode("/", $start_date);
            $start_date = $strDate[2].'-'.$strDate[0].'-'.$strDate[1];
            $enDate = explode("/", $end_date);
            $end_date = $enDate[2].'-'.$enDate[0].'-'.$enDate[1];
                    
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'query_date'=>'exchange_stats.query_date',
                                                            'rub_impressiosn'=>'SUM(exchange_stats.rub_impressiosn)',
                                                            'madads_impressions'=>'SUM(exchange_stats.madads_impressions)',
                                                            'cost'=>'ROUND(SUM(exchange_stats.cost),2)',
                                                            'revenue'=>'ROUND(SUM(exchange_stats.revenue),2)'
                                                        ))
                               ->where("DATE_FORMAT(exchange_stats.query_date, '%Y-%m-%d')>='$start_date' AND DATE_FORMAT(exchange_stats.query_date, '%Y-%m-%d')<='$end_date'")
                               ->where($whereSize)
                               ->where("exchange_stats.SiteID IN (?)", $site) 
                               ->group(array("exchange_stats.query_date"))
                               ->order("exchange_stats.query_date DESC"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }    
    
    public function getReportByDate($site, $date, $ad_size)
    {
    	if($ad_size){
    		$whereSize = 'exchange_stats.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSize = '1=1';
    	}
                        
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'SiteID'=>'sites.SiteID',
                                                            'SiteName'=>'sites.SiteName',
                                                            'query_date'=>'exchange_stats.query_date',
                                                            'rub_impressiosn'=>'SUM(exchange_stats.rub_impressiosn)',
                                                            'madads_impressions'=>'SUM(exchange_stats.madads_impressions)',
                                                            'cost'=>'ROUND(SUM(exchange_stats.cost),2)',
                                                            'revenue'=>'ROUND(SUM(exchange_stats.revenue),2)'                                    
                                                        ))
                                ->joinLeft('sites', "sites.SiteID=exchange_stats.SiteID", array())
                               ->where("DATE_FORMAT(exchange_stats.query_date, '%Y-%m-%d')='$date'")
                               ->where($whereSize)
                               ->where("exchange_stats.SiteID IN (?)", $site) 
                               ->group(array("exchange_stats.query_date", "exchange_stats.SiteID"))
                               ->order("sites.SiteName"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }           
        
    public function getReportBySize($site, $date)
    {                        
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'name'=>'display_size.name',
                                                            'query_date'=>'exchange_stats.query_date',
                                                            'rub_impressiosn'=>'SUM(exchange_stats.rub_impressiosn)',
                                                            'madads_impressions'=>'SUM(exchange_stats.madads_impressions)',
                                                            'cost'=>'ROUND(SUM(exchange_stats.cost),2)',
                                                            'revenue'=>'ROUND(SUM(exchange_stats.revenue),2)'
                                                        ))
                                ->joinLeft('display_size', "display_size.id=exchange_stats.AdSize", array())
                               ->where("DATE_FORMAT(exchange_stats.query_date, '%Y-%m-%d')='$date'")
                               ->where("exchange_stats.SiteID = ?", $site) 
                               ->group(array("exchange_stats.query_date", "exchange_stats.AdSize"))
                               ->order("exchange_stats.query_date DESC"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }       
    
}