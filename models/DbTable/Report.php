<?php

class Application_Model_DbTable_Report extends Zend_Db_Table_Abstract
{
    protected $_name = 'users_reports_final';
    
    public function getUserReport($site, $start_date, $end_date, $ad_size)
    {
    	if($ad_size){
    		$whereSize = 'users_reports_final.AdSize = ' . $ad_size;
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
                                                            'query_date'=>'users_reports_final.query_date',
                                                            'paid_impressions'=>'SUM(users_reports_final.paid_impressions)',
                                                            //'paid_impressions'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.paid_impressions END)',
                                                            'paid_impressions_RubiconCsv'=>'SUM(users_reports_final.paid_impressions_RubiconCsv)',
                                                            'impressions'=>'SUM(users_reports_final.impressions)',
                                                            //'impressions'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.impressions END)',
                                                            'impressions_denied'=>'SUM(users_reports_final.impressions_denied)',
                                                            'revenue'=>'ROUND(SUM(users_reports_final.revenue),2)',
                                                            'estimated'=>'users_reports_final.estimated',
                                                            //'estimated'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.estimated END)',
                                                            'type'=>'users_reports_final.type'
                                                        ))
                               ->where("DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')>='$start_date' AND DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')<='$end_date'")
                               ->where($whereSize)
                               ->where("users_reports_final.SiteID IN (?)", $site)
                                ->where("users_reports_final.estimated<>1")
                               ->group(array("users_reports_final.query_date"))
                               ->order("users_reports_final.query_date DESC"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }    
    
    public function getUserReportShowDemand($site, $start_date, $end_date, $ad_size)
    {
    	if($ad_size){
    		$whereSize = 'users_reports_final.AdSize = ' . $ad_size;
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
                                                            'query_date'=>'users_reports_final.query_date',
                                                            'impressions_estimated'=>'SUM(users_reports_final.impressions_estimated)',
                                                            'impressions_nonfilled'=>'SUM(users_reports_final.impressions_nonfilled)',
                                                            'impressions_RubiconComics'=>'SUM(users_reports_final.impressions_RubiconComics)',
                                                            'revenue_RubiconComics'=>'ROUND(SUM(users_reports_final.revenue_RubiconComics),2)',
                                                            'impressions_RubiconCsv'=>'SUM(users_reports_final.impressions_RubiconCsv)',
                                                            'paid_impressions_RubiconCsv'=>'SUM(users_reports_final.paid_impressions_RubiconCsv)',
                                                            'revenue_RubiconCsv'=>'ROUND(SUM(users_reports_final.revenue_RubiconCsv),2)',
                                                            'impressions_pubmatic'=>'SUM(users_reports_final.impressions_pubmatic)',
                                                            'paid_impressions_pubmatic'=>'SUM(users_reports_final.paid_impressions_pubmatic)',
                                                            'revenue_pubmatic'=>'ROUND(SUM(users_reports_final.revenue_pubmatic),2)',
                                                            'paid_impressions'=>'SUM(users_reports_final.paid_impressions)',
                                                            'impressions_denied'=>'SUM(users_reports_final.impressions_denied)',
                                                            'impressions'=>'SUM(users_reports_final.impressions)',
                                                            'revenue'=>'ROUND(SUM(users_reports_final.revenue),2)',
                                                            'estimated'=>'users_reports_final.estimated',
                                                            'type'=>'users_reports_final.type',
                                                            'impressions_amazon'=>'SUM(users_reports_final.impressions_amazon)',
                                                            'paid_impressions_amazon'=>'SUM(users_reports_final.paid_impressions_amazon)',
                                                            'revenue_amazon'=>'ROUND(SUM(users_reports_final.revenue_amazon),2)',
                                                            'impressions_pulse'=>'SUM(users_reports_final.impressions_pulse)',
                                                            'paid_impressions_pulse'=>'SUM(users_reports_final.paid_impressions_pulse)',
                                                            'revenue_pulse'=>'ROUND(SUM(users_reports_final.revenue_pulse),2)',
                                                            'impressions_pop'=>'SUM(users_reports_final.impressions_pop)',
                                                            'paid_impressions_pop'=>'SUM(users_reports_final.paid_impressions_pop)',
                                                            'revenue_pop'=>'ROUND(SUM(users_reports_final.revenue_pop),2)',
                                                            'impressions_sekindo'=>'SUM(users_reports_final.impressions_sekindo)',
                                                            'paid_impressions_sekindo'=>'SUM(users_reports_final.paid_impressions_sekindo)',
                                                            'revenue_sekindo'=>'ROUND(SUM(users_reports_final.revenue_sekindo),2)',
                                                            'impressions_aol'=>'SUM(users_reports_final.impressions_aol)',
                                                            'paid_impressions_aol'=>'SUM(users_reports_final.paid_impressions_aol)',
                                                            'revenue_aol'=>'ROUND(SUM(users_reports_final.revenue_aol),2)',
                                                            'impressions_aol_o'=>'SUM(users_reports_final.impressions_aol_o)',
                                                            'paid_impressions_aol_o'=>'SUM(users_reports_final.paid_impressions_aol_o)',
                                                            'revenue_aol_o'=>'ROUND(SUM(users_reports_final.revenue_aol_o),2)',
                                                            'impressions_brt'=>'SUM(users_reports_final.impressions_brt)',
                                                            'paid_impressions_brt'=>'SUM(users_reports_final.paid_impressions_brt)',
                                                            'revenue_brt'=>'ROUND(SUM(users_reports_final.revenue_brt),2)',

                                ))
                               ->where("DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')>='$start_date' AND DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')<='$end_date'")
                               ->where($whereSize)
                               ->where("users_reports_final.SiteID IN (?)", $site) 
                               ->group(array("users_reports_final.query_date"))
                               ->order("users_reports_final.query_date DESC"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }    
    
    public function getReportByDate($site, $date, $ad_size)
    {
    	if($ad_size){
    		$whereSize = 'users_reports_final.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSize = '1=1';
    	}
                        
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'SiteID'=>'sites.SiteID',
                                                            'SiteName'=>'sites.SiteName',
                                                            'query_date'=>'users_reports_final.query_date',
                                                            'paid_impressions'=>'SUM(users_reports_final.paid_impressions)',
                                                            //'paid_impressions'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.paid_impressions END)',
                                                            'impressions'=>'SUM(users_reports_final.impressions)',
                                                            //'impressions'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.impressions END)',
                                                            'revenue'=>'ROUND(SUM(users_reports_final.revenue),2)',
                                                            'estimated'=>'users_reports_final.estimated',
                                                            //'estimated'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.estimated END)',
                                                            'type'=>'users_reports_final.type',
                                                            'floor_pricing'=>'users_reports_final.floor_pricing'
                                                        ))
                                ->joinLeft('sites', "sites.SiteID=users_reports_final.SiteID", array())
                               ->where("DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')='$date'")
                               ->where($whereSize)
                               ->where("users_reports_final.SiteID IN (?)", $site)
                                ->where("users_reports_final.estimated<>1")
                               ->group(array("users_reports_final.query_date", "users_reports_final.SiteID"))
                               ->order("users_reports_final.query_date DESC"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }       
    
    public function getUserReportCsv($site, $start_date, $end_date, $ad_size, $break_size=null)
    {
    	if($ad_size){
    		$whereSize = 'users_reports_final.AdSize = ' . $ad_size;
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
                                                            'SiteName'=>'sites.SiteName',
                                                            'query_date'=>'users_reports_final.query_date',
                                                            'paid_impressions'=>'SUM(users_reports_final.paid_impressions)',
                                                            'impressions'=>'SUM(users_reports_final.impressions)',
                                                            'revenue'=>'ROUND(SUM(users_reports_final.revenue),2)',
                                                            'estimated'=>'users_reports_final.estimated',
                                                            'type'=>'users_reports_final.type'
                                                        ))
                                ->joinLeft('sites', "sites.SiteID=users_reports_final.SiteID", array())
                               ->where("DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')>='$start_date' AND DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')<='$end_date'")
                               ->where($whereSize)
                               ->where("users_reports_final.SiteID IN (?)", $site) 
                               ->order("users_reports_final.query_date DESC"); 
                               
            if($break_size==1){
	            $select->joinLeft('display_size AS ds', "ds.id=users_reports_final.AdSize", array(
	            			'AdSizeName'=>'ds.name'
	            ));
	            $select->group(array("users_reports_final.query_date", "users_reports_final.SiteID", "users_reports_final.AdSize"));
            }else{
	            $select->group(array("users_reports_final.query_date", "users_reports_final.SiteID"));	            
            }                   
                               
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }    
    
    public function getReportBySize($site, $user, $date)
    {                        
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'name'=>'display_size.name',
                                                            'query_date'=>'users_reports_final.query_date',
                                                            'paid_impressions'=>'SUM(users_reports_final.paid_impressions)',
                                                            //'paid_impressions'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.paid_impressions END)',
                                                            'impressions'=>'SUM(users_reports_final.impressions)',
                                                            //'impressions'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.impressions END)',
                                                            'revenue'=>'ROUND(SUM(users_reports_final.revenue),2)',
                                                            'estimated'=>'users_reports_final.estimated',
                                                            //'estimated'=>'SUM(CASE WHEN (users_reports_final.AdSize=6 AND users_reports_final.estimated=1) THEN 0 ELSE users_reports_final.estimated END)',
                                                            'type'=>'users_reports_final.type',
                                                            'floor_pricing'=>'users_reports_final.floor_pricing'
                                                        ))
                                ->joinLeft('display_size', "display_size.id=users_reports_final.AdSize", array())
                               ->where("DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')='$date'")
                               ->where("users_reports_final.SiteID = ?", $site) 
                               ->where("users_reports_final.PubID = ?", $user)
                                ->where("users_reports_final.estimated<>1")
                               ->group(array("users_reports_final.query_date", "users_reports_final.AdSize"))
                               ->order("users_reports_final.query_date DESC"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }       
    
    public function getUserReportEdit($site, $start_date, $end_date, $ad_size)
    {
    	if($ad_size){
    		$whereSize = 'users_reports_final.AdSize = ' . $ad_size;
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
                                                            'query_date'=>'users_reports_final.query_date',
                                                            'paid_impressions'=>'users_reports_final.paid_impressions',
                                                            'impressions'=>'users_reports_final.impressions',
                                                            'revenue'=>'users_reports_final.revenue',
                                                            'estimated'=>'users_reports_final.estimated',
                                                            'SizeName'=>'display_size.description',
                                                            'SizeID'=>'display_size.id'
                                                        ))
                                ->joinLeft("display_size", 'display_size.id=users_reports_final.AdSize')
                               ->where("DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')>='$start_date' AND DATE_FORMAT(users_reports_final.query_date, '%Y-%m-%d')<='$end_date'")
                               ->where($whereSize)
                               ->where("users_reports_final.SiteID = (?)", $site) 
                               ->order("users_reports_final.query_date DESC"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }    
    
    public function getReportMadx($site, $date, $ad_size, $PubID)
    {
        $select = $this->_db->select()
                            ->from('users_reports_madex', array(
        'query_date'=>'users_reports_madex.query_date',
        'impressions_pubm'=>'SUM(users_reports_madex.impressions_pubm)',
        'revenue_pubm'=>'ROUND(SUM(users_reports_madex.revenue_pubm),2)',
        'impressions_tlv'=>'SUM(users_reports_madex.impressions_tlv)',
        'revenue_tlv'=>'ROUND(SUM(users_reports_madex.revenue_tlv),2)',
        'impressions_tagg'=>'SUM(users_reports_madex.impressions_tagg)',
        'revenue_tagg'=>'ROUND(SUM(users_reports_madex.revenue_tagg),2)',
        'impressions_passb'=>'SUM(users_reports_madex.impressions_passb)'
                                                    ))
           ->where("DATE_FORMAT(users_reports_madex.query_date, '%Y-%m-%d')='$date'")
           ->group(array("users_reports_madex.query_date"))
           ->order("users_reports_madex.query_date DESC"); 
           
        if($PubID) { $select->where("users_reports_madex.PubID = ?", $PubID); } 
        if($site)  { $select->where("users_reports_madex.SiteID IN (?)", $site);  }
        if($ad_size){ $select->where("users_reports_madex.AdSize = ?", $ad_size); }
           
        $result = $this->getAdapter()->fetchAll($select);

        return $result;                        
    }           
}