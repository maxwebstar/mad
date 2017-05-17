<?php


class Application_Model_DbTable_Sites extends Zend_Db_Table_Abstract
{
    protected $_name = 'sites';
      
    //////// status ///////
    // 1 - new           //
    // 2 - reject        //
    // 3 - approved      //
    ///////////////////////
    
    public function getNullPlacments()
    {
            $select = $this->_db->select()
                                ->from('placement', array(
                                                            'UName'=>'display_zone.UName',
                                                            'id'=>'placement.id'
                                                        ))
                                ->joinLeft('display_zone','placement.display_zone=display_zone.id', array())
                                ->where('placement.site IS NULL')
                               ; 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;        
    }    
    
    public function getAllSites()
    {
        $this->_name = "sites";
        return $this->fetchAll();
    }
    
    public function insertSiteId($id, $where){
        
        $this->_name = "placement";
        
        $result = array(
                        'site'=>$id,
                        );
                        
        $where = $this->getAdapter()->quoteInto('id  = ?', $where);
        $this->update($result, $where);
    }
    
    public function getApprovedSites($filter = false)
    {        
        $str = preg_split('/[- :]/', date("Y-m-d H:i:s"));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, $str[1], $str[2] - 1 /* day */, $str[0]));
         
        $select = $this->_db->select()
                            ->from('sites', array(
                                                        'PubID'=>'sites.PubID',
                                                        'SiteID'=>'sites.SiteID',
                                                        'SiteName'=>'sites.SiteName',
                                                        'live'=>'sites.live',
                                                        'live_name'=>'sites.live_name',
                                                        'privacy'=>'sites.privacy',
                                                        'privacy_name'=>'sites.privacy_name',
                                                        'type'=>'sites.type',
                            							'tag_name'=>'sites.tag_name',
					                            		'alexaRank'=>'sites.alexaRank',
					                            		'alexaRank_update'=>'sites.alexaRank_update'
                                                    ))
                            ->joinLeft('users','users.id=sites.PubID', array(
                                'email'=>'users.email',
                                'user_id'=>'users.id'
                            )) 
                            ->joinLeft(array('st' => 'stats_impressions'),('st.SiteID = sites.SiteID AND st.date = "'.$yesterday.'"'),
                                       array('st.impressions AS today_impressions',
                                             'st.yesterday AS yesterday_impressions'));
        
        switch($filter){
            
            case 'live':       $select->where('sites.live = 1');          break;
            case 'no_longer':  $select->where('sites.lived IS NOT NULL AND sites.live IS NULL'); break;
            case 'never_live': $select->where('sites.lived IS NULL');     break;
            default:break;
        
    	}
        
        $result = $this->getAdapter()->fetchAll($select);

        return $result;                
    }

    public function getApprovedSitesByUserId($id, $filter = false, $order=false)
    {
        $str = preg_split('/[- :]/', date("Y-m-d H:i:s"));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, $str[1], $str[2] - 1 /* day */, $str[0]));
                        
        $order = $order ? $order : "sites.SiteID";
        
        $select = $this->_db->select()
                            ->from('sites', array(
                                                        'PubID'=>'sites.PubID',
                                                        'SiteID'=>'sites.SiteID',
                                                        'SiteName'=>'sites.SiteName',
                                                        'live'=>'sites.live',
                                                        'live_name'=>'sites.live_name',
                                                        'privacy'=>'sites.privacy',
                                                        'privacy_name'=>'sites.privacy_name',
                                                        'type'=>'sites.type',
                            							'floor_pricing'=>'sites.floor_pricing',
                            							'tag_name'=>'sites.tag_name',
					                            		'alexaRank'=>'sites.alexaRank',
					                            		'alexaRank_update'=>'sites.alexaRank_update',
					                            		'burst_tag'=>'sites.burst_tag'
                                                    ))
                            ->joinLeft('users','users.id=sites.PubID', array(
                                'email'=>'users.email',
                                'user_id'=>'users.id'
                            ))
                            ->joinLeft('tags','tags.site_id=sites.SiteID', array(
                                'tag_type'=>'tags.type'
                            ))
                            ->where('sites.PubID = ' .$id)
                            ->where('sites.status = 3 OR (sites.status = 2 AND sites.lived IS NOT NULL)')
                            ->order($order);
        
        switch($filter){
            
            case 'live':       $select->where('sites.live = 1');          break;
            case 'no_longer':  $select->where('sites.lived IS NOT NULL'); break;
            case 'never_live': $select->where('sites.lived IS NULL');     break;
            default:break;
        
    	}
        
        $result = $this->getAdapter()->fetchAll($select);
        
        return $result;                
    }
    
    public function getSiteByID($SiteID)
    {
        $this->_name = "sites";
        
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('SiteID = ?', $SiteID);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getSiteInfoByID($id)
    {
        $select = $this->_db->select()
                            ->from('sites', array(
                                                        'PubID'=>'sites.PubID',
                                                        'SiteID'=>'sites.SiteID',
                                                        'SiteName'=>'sites.SiteName',
                            				'SiteURL'=>'sites.SiteURL',
                                                        'ServingURL'=>'sites.ServingURL',
                                                        'BlockedURL'=>'sites.BlockedURL',
                            				'rubicon_type'=>'sites.rubicon_type',
                                                        'live'=>'sites.live',
                                                        'live_name'=>'sites.live_name',
                                                        'privacy'=>'sites.privacy',
                                                        'privacy_name'=>'sites.privacy_name',
                                                        'category'=>'sites.category',
                                                        'cat_rubicon'=>'sites.cat_rubicon',
                                                        'approved'=>'sites.approved',
                                                        'approved_by'=>'sites.approved_by',
                                                        'co_approved_date'=>'sites.co_approved_date',
                                                        'co_approved_by'=>'sites.co_approved_by',
                                                        'partners'=>'sites.partners',
                                                        'type'=>'sites.type',
                            							'floor_pricing'=>'sites.floor_pricing',
                            							'cpm'=>'sites.cpm',
                            							'tag_name'=>'sites.tag_name',
                            							'pop_unders'=>'sites.pop_unders',
					                            		'alexaRank'=>'sites.alexaRank',
                                                                                'alexaRankUS'=>'sites.alexaRankUS',
					                            		'alexaRank_update'=>'sites.alexaRank_update',
                                                                                'auto_report_file'=>'sites.auto_report_file', 
                                                                                'email_notlive_3day'=>'email_notlive_3day',
                            							'create_dfp_passbacks'=>'sites.create_dfp_passbacks',
                                                                                'status'=>'sites.status',
                                                        'iframe_tags'=>'sites.iframe_tags',
                                                        'limited_demand_tag' => 'sites.limited_demand_tag',
                                                        'rub_io' => 'sites.rub_io',
                                                        'creative_passback'=>'sites.creative_passback',
                                                        'creative_adexchange'=>'sites.creative_adexchange',
                                                        'store_tag_url'=>'sites.store_tag_url',
                                                        'store_tag_url_start'=>'sites.store_tag_url_start',
                                                        'lock_tags'=>'sites.lock_tags',
                                                        'define_url'=>'sites.define_url',
                                                        'reject_notes'=>'sites.reject_notes',
                                                        'admin_email'=>'sites.admin_email',
                                                        'factor_revshare'=>'sites.factor_revshare',
                                                        'video_ads'=>'sites.video_ads',
                                                        'disable_rubicon_revenue'=>'sites.disable_rubicon_revenue',
                                                        'date_disable_rubicon'=>'sites.date_disable_rubicon',
                                                        'disable_google_revenue'=>'sites.disable_google_revenue',
                                                        'date_disable_google'=>'sites.date_disable_google',
                                                        'baner_320'=>'sites.baner_320',
                                                        'co-approved'=>'sites.co-approved',
                                                        'hide_question'=>'sites.hide_question',
                                                        'blank_ref_serve'=>'sites.blank_ref_serve',
                                                        'header_bidding'=>'sites.header_bidding',
                                                        'burst_tag'=>'sites.burst_tag',
                                                        'desired_types'=>'sites.desired_types'
                                                        ))
                            ->joinLeft('tags','tags.site_id = sites.SiteID', array('tag_type'=>'tags.type'))
                            ->joinLeft('users','users.id=sites.PubID', array(
                                'email'=>'users.email',
                                'name'=>'users.name',
                                'user_id'=>'users.id',
                                'notification_control_user' => 'users.notification_control_user',
                                'user_date_approve' => 'users.date_approve',
                                'account_manager_id' => 'users.account_manager_id'
                            ))
                            ->where('sites.SiteID = ' .$id); 
        $result = $this->getAdapter()->fetchRow($select);
        return $result;                            
    }
    
    public function save($id, $data)
    {
    	$this->_name = "sites";
        $result = array(
                        'SiteURL' =>$data['SiteURL'],
                        'ServingURL' =>$data['ServingURL'] ? $data['ServingURL'] : NULL,
                        'BlockedURL' =>$data['BlockedURL'] ? $data['BlockedURL'] : NULL,
                        'category'=>$data['category'],
                        'cat_rubicon'=>$data['cat_rubicon'],
                        'partners'=>$data['partners'],
        		'floor_pricing'=>$data['floor_pricing']==1 ? 1 : NULL,
        		'cpm'=>$data['cpm'] ? $data['cpm'] : NULL,
        		'tag_name'=>$data['tag_name'] ? $data['tag_name'] : NULL,
                        'auto_report_file'=>$data['auto_report_file']==1 ? 1 : NULL,
                        'email_notlive_3day'=>$data['email_notlive_3day']==1 ? 1 : NULL,
                        'iframe_tags'=>$data['iframe_tags']==1 ? 1 : NULL,
                        'limited_demand_tag'=>$data['limited_demand_tag'] == 1 ? 1 : NULL,
                        'rub_io'=>$data['rub_io'] ? $data['rub_io'] : NULL,
                        'store_tag_url'=>$data['store_tag_url'] ? 1 : NULL,
                        'store_tag_url_start'=>$data['store_tag_url_start'],
                        'lock_tags'=>$data['lock_tags']==1 ? 1 : NULL,
                        'define_url'=>$data['define_url'] ? $data['define_url'] : NULL,
                        'factor_revshare'=>$data['factor_revshare']==1 ? 1 : NULL,
                        'video_ads'=>$data['video_ads']==1 ? 1 : NULL,
                        'disable_rubicon_revenue'=>$data['disable_rubicon_revenue']==1 ? 1 : NULL,
                        'date_disable_rubicon'=>$data['date_disable_rubicon'] ? $data['date_disable_rubicon'] : NULL,
                        'disable_google_revenue'=>$data['disable_google_revenue']==1 ? 1 : NULL,
                        'date_disable_google'=>$data['date_disable_google'] ? $data['date_disable_google'] : NULL,
                        'baner_320'=>$data['baner_320']==1 ? 1 : NULL,
                        'request_disable_impression' => !empty($data['request_disable_impression']) ? $data['request_disable_impression'] : NULL, 
                        'hide_question' => $data['hide_question']==1 ? 1 : 0,
                        'blank_ref_serve' => $data['blank_ref_serve']==1 ? 1 : 0,
                        'header_bidding' => $data['header_bidding']==1 ? 1 : 0,
                        'burst_tag' => $data['burst_tag']==1 ? 1 : 0,
                        'desired_types'=>$data['desired_types']
                        );
                        
        $where = $this->getAdapter()->quoteInto('SiteID = ?', $id);
        $this->update($result, $where);        
    }
    
    public function deleteLiveStat($id)
    {
    	$this->_name = 'sites_live';
    	$this->_primary = 'SiteID';
    	$this->delete(array("SiteID = '$id'", "date = '".date("Y-m-d")."'"));
    }    

    public function setLiveStat($data)
    {
    	$this->_name = "sites_live";
    	$this->_primary = 'SiteID';
    	$result = array(
    			'SiteID'=>$data['id'],
    			'date'=>date("Y-m-d"),
    			'live'=>$data['color']=='red' ? null : 1,
    			'live_name'=>$data['name']
    	);
    
    	$this->insert($result);
    }    
    
    public function setLive($data)
    {
    	$this->_name = "sites";
        $result = array(
                        'live'=>$data['color']=='red' ? null : 1,
                        'live_name'=>$data['name']
                        );
                        
        $where = $this->getAdapter()->quoteInto('SiteID = ?', $data['id']);
        $this->update($result, $where);                
    }
    
    public function getCountLive()
    {
    	$select = $this->_db->select()
    	->from('sites', array(
    			'count'=>'COUNT(sites.live)'
    	))
		->where("sites.live=1");
    	;
    	$result = $this->getAdapter()->fetchRow($select);
    	return $result;    	 
    }
    
    public function getLiveSites()
    {
    	$select = $this->_db->select()
    	->from('sites_live', array(
    			'SiteID'=>'sites_live.SiteID'
    	))
    	->where("sites_live.live=1")
    	->group("sites_live.SiteID")
    	;
    	$result = $this->getAdapter()->fetchAll($select);
    	return $result;
    }
    
    public function setPrivacy($data)
    {
    	$this->_name = "sites";
        $result = array(
                        'privacy'=>$data['color']=='red' ? null : 1,
                        'privacy_name'=>$data['name']
                        );
                        
        $where = $this->getAdapter()->quoteInto('SiteID = ?', $data['id']);
        $this->update($result, $where);                
    }

    public function setPopup($data)
    {
    	$this->_name = "sites";
    	$result = array(
    			'pop_unders'=>$data['color']=='red' ? null : 1
    	);
    
    	$where = $this->getAdapter()->quoteInto('SiteID = ?', $data['id']);
    	$this->update($result, $where);
    }    
    
    public function addNewUserSite($userID, $data, $alexaData=null)
    {                
        $this->_name = 'sites';

		$data['hidden_url'] = str_replace("http://", "", $data['hidden_url']);
		$data['hidden_url'] = str_replace("https://", "", $data['hidden_url']);
		$data['hidden_url'] = str_replace("www.", "", $data['hidden_url']);
		$data['hidden_url'] = ucfirst($data['hidden_url']);
		$data['hidden_url'] = parse_url('http://'.$data['hidden_url'], PHP_URL_HOST);

		$data['url'] = str_replace("http://", "", $data['url']);
		$data['url'] = str_replace("https://", "", $data['url']);
		$data['url'] = str_replace("www.", "", $data['url']);
		$data['url'] = ucfirst($data['url']);
		$data['url'] = parse_url('http://'.$data['url'], PHP_URL_HOST);
		$data['url'] = 'http://'.strtolower($data['url']);
           
        $dataSite = array('PubID'    => $userID,
                          'SiteName' => $data['hidden_url'],
                          'SiteURL' => $data['url'],
                          'privacy'  => $data['privacy'] ? $data['privacy'] : NULL,
                          'type'     => $data['type'] ? $data['type'] : NULL,
                          'rubicon_type' => NULL,
                          'category' => $data['category'],
                          'status'   => 1,
                          'rub_io'     => $data['rub_io'] ? $data['rub_io'] : NULL,
                          'created'  => date('Y-m-d H:i:s'),
                            'desired_types'=>$data['desired_types']);
        
        if($alexaData){
            $alexaArray = array();
            foreach ($alexaData as $key=>$value){
                $alexaArray[$key] = str_replace("<td class='text-right' ><span class=''>", "", str_replace("</span></td>", "", $value));
            }
            $dataSite['alexa_click'] = serialize($alexaArray);
        }
        
        $SiteID = $this->insert($dataSite);  
 
        $this->_name = 'pending_sites';
                    
        $dataPending = array('PubID'  => $userID,
                             'SiteID' => $SiteID,
                             'url'    => $data['hidden_url'],
                             'title'  => $data['title'],
                             'description' => $data['description'],
                             'keywords'    => $data['keywords'],
                             'category'    => $data['category'],
                             'privacy'     => $data['privacy'] ? $data['privacy'] : NULL,
                             'type'        => $data['type'] ? $data['type'] : NULL,
                             'daily'       => $data['daily'] ? $data['daily'] : NULL,
                             'followers'   => $data['followers'] ? $data['followers'] : NULL,
                            'desired_types'=>$data['desired_types']);
                        
        return $this->insert($dataPending);    
            
    }
    
    public function getNewSites()
    {
        $select = $this->_db->select()
                       ->from(array('s' => 'sites'),
                              array('s.SiteID AS SiteID',
                                    's.PubID AS PubID',
                                    's.SiteName AS url',
                                    's.created AS created'))
                       ->where('s.status = 1')
                       ->joinLeft(array('p' => 'pending_sites'), ('p.SiteID = s.SiteID'), 
                                  array('p.id AS id'))
                       ->joinLeft(array('u' => 'users'),('u.id = s.PubID'), 
                                  array('u.email AS email',
                                        'u.id AS user_id'));
        
        $result = $this->getAdapter()->fetchAll($select);
        return $result;                
    }    
    
    public function getNewSiteInfoById($id)
    {
        $select = $this->_db->select()
                            ->from('sites', array('PubID' => 'sites.PubID',
                                                  'SiteID' => 'sites.SiteID',
                                                  'SiteURL' => 'sites.SiteURL',
                                                  'url' => 'sites.SiteName',
                                                  'rub_io' => 'sites.rub_io',
                                                  'privacy' => 'sites.privacy',
                                                  'alexa_click' => 'sites.alexa_click',
                                                  'desired_types' => 'sites.desired_types'))
                            ->joinLeft('pending_sites', 'pending_sites.SiteID = sites.SiteID', array(
                                                        'id'=>'pending_sites.id',
                                                        'title'=>'pending_sites.title',
                                                        'description'=>'pending_sites.description',
                                                        'keywords'=>'pending_sites.keywords',
                                                        'type'=>'pending_sites.type',
                                                        'daily'=>'pending_sites.daily',
                                                        'followers'=>'pending_sites.followers'
                                                    ))
                            ->joinLeft('users','users.id=sites.PubID', array(
                                'company'=>'users.company',
                                'name'=>'users.name',
                                'email'=>'users.email',
                                'phone'=>'users.phone',
                                'ssn'=>'users.ssn',
                                'ein'=>'users.ein',
                                'server'=>'users.server'
                            ))
                            ->joinLeft('timezone','timezone.id=users.zone', array(
                                'zone'=>'timezone.name'
                            ))
                            ->joinLeft('country','country.id=users.country', array(
                                'country'=>'country.name'
                            ))
                            ->joinLeft('category','category.id=sites.category', array(
                                'category'=>'category.name'
                            ))
                            ->where('sites.SiteID = ' .$id); 
        
        $result = $this->getAdapter()->fetchRow($select);
        return $result;                                    
    }
    
    public function deleteNewSite($id)
    {
        $this->_name = 'pending_sites';    
        $this->delete(array("id = '$id'"));                            
    }
    
    public function rejectNewSite($id)
    {
        $this->_name = 'pending_sites';
        
        $where = $this->getAdapter()->quoteInto('id = ?', $id);
    	$this->update(array('reject' => 1), $where);  
      
    }
    
    public function setPaymentComent($month, $year, $userID, $comment)
    {
    	$this->_name = 'payments_due_coments';
        
    	$result = array(
    			'PubID'=>$userID,
    			'date'=>$year.'-'.$month.'-'.date("d"),
    			'comment'=>$comment
    	);
    
    	return $this->insert($result);
    }
    
    public function getPaymentComent($month, $year, $userID)
    {
    	$select = $this->_db->select()
    	->from('payments_due_coments', array(
    			'comment'=>'payments_due_coments.comment'
    	))
    	->where("payments_due_coments.PubID='$userID' AND DATE_FORMAT(payments_due_coments.date, '%Y-%c')='$year-$month'");
    	;
    	$result = $this->getAdapter()->fetchRow($select);
    	return $result;
    }
    
    public function setPaymentNote($month, $year, $userID, $comment)
    {
    	$this->_name = 'payments_due_note';
    
    	$result = array(
    			'PubID'=>$userID,
    			'date'=>$year.'-'.$month.'-'.date("d"),
    			'comment'=>$comment
    	);
    
    	return $this->insert($result);
    }
    
    public function updatePaymentNote($id, $month, $year, $userID, $comment)
    {
    	$this->_name = 'payments_due_note';
    
    	$result = array(
    			'PubID'=>$userID,
    			'date'=>$year.'-'.$month.'-'.date("d"),
    			'comment'=>$comment
    	);
    
        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        $this->update($result, $where);                
    	    
    }
    
    public function getPaymentNoteByID($id)
    {
    	$select = $this->_db->select()
    	->from('payments_due_note', array(
    			'comment'=>'payments_due_note.comment'
    	))
    	->where("payments_due_note.id='$id'");
    	;
    	$result = $this->getAdapter()->fetchRow($select);
    	return $result;
    }
    
    public function getSiteFlorPricing($PubID, $SiteID)
    {
    	$select = $this->_db->select()
    	->from('sites_floor_price', array(
    			'date'=>'sites_floor_price.date',
    			'price'=>'sites_floor_price.price',
    			'percent'=>'sites_floor_price.percent'
    	))
    	->where('sites_floor_price.PubID = ' .$PubID. ' AND sites_floor_price.SiteID = ' .$SiteID);
    	$result = $this->getAdapter()->fetchAll($select);
    	return $result;
    }
    
    public function getSiteTagsById($SiteID)
    {
    	$select = $this->_db->select()
    	->from('tags', array(
    			'site_id'=>'tags.site_id',
    			'user_id'=>'tags.user_id',
                        'type'=>'tags.type',
                        'rubiconType'=>'tags.rubiconType',
    			'accountRub_id'=>'tags.accountRub_id',
    			'siteRub_id'=>'tags.siteRub_id',
    			'zoneRub_id'=>'tags.zoneRub_id',
                        'site_name'=>'tags.site_name',
                        'SiteName'=>'sites.SiteName',
                        'SiteURL'=>'sites.SiteURL',
                        'iframe_tags'=>'sites.iframe_tags',
                        'floor_pricing'=>'sites.floor_pricing',
                        'limited_demand_tag'=>'sites.limited_demand_tag',
                        'pop_unders'=>'sites.pop_unders'
    	))
        ->joinLeft('sites','tags.site_id=sites.SiteID', array())
    	->where("tags.site_id='$SiteID'");
    	$result = $this->getAdapter()->fetchRow($select);
    	return $result;
    }    
    
    public function getLiveSitesByDate($date)
    {
    	$select = $this->_db->select()
    	->from('sites_live', array(
    			'PubID'=>'sites.PubID',
    			'SiteID'=>'sites.SiteID',
    			'SiteName'=>'sites.SiteName',
    			'live'=>'sites_live.live',
    			'live_name'=>'sites_live.live_name',
    			'privacy'=>'sites.privacy',
    			'privacy_name'=>'sites.privacy_name',
    			'type'=>'sites.type',
    			'tag_name'=>'sites.tag_name'
    	))
    	->joinLeft('sites','sites_live.SiteID=sites.SiteID', array())
    	->joinLeft('users','users.id=sites.PubID', array(
    			'email'=>'users.email',
    			'user_id'=>'users.id'
    	))    	 
    	->where("sites_live.date='$date' AND sites_live.live=1");    	 
    	$result = $this->getAdapter()->fetchAll($select);
    	return $result;
    }
    
    public function getCsvDataByUser($arrSite, $start_date, $end_date, $ad_size)
    {
     
        $siteIDs = NULL;
         
        foreach($arrSite as $iter){
            
            $siteIDs .= $iter.',';
            
        }   $siteIDs = substr($siteIDs, 0, strlen($siteIDs) - 1);
        
        if($start_date && $end_date){
    		$whereDateRub = "DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')<='$end_date'";
    		$whereDateDfp = "DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')<='$end_date'";
    		$whereDateEst = "DATE_FORMAT(madads_estimated.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_estimated.query_date, '%m/%d/%Y')<='$end_date'";
    		$whereDateFill = "DATE_FORMAT(madads_nonfilled.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_nonfilled.query_date, '%m/%d/%Y')<='$end_date'";
    	}
    	else{
    		$whereDateRub = '1=1';
    		$whereDateDfp = '1=1';
    		$whereDateEst = '1=1';
    		$whereDateFill = '1=1';
    	}
    	if($ad_size){
    		$whereSizeRub = 'madads_rubicon_table.AdSize = ' . $ad_size;
    		$whereSizeDfp = 'madads_dfp_table.AdSize = ' . $ad_size;
    		$whereSizeEst = 'madads_estimated.AdSize = ' . $ad_size;
    		$whereSizeFill = 'madads_nonfilled.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSizeRub = '1=1';
    		$whereSizeDfp = '1=1';
    		$whereSizeEst = '1=1';
    		$whereSizeFill = '1=1';
    	}
        
        $sql = "SELECT
                res.Date as Date,
                res.SiteID as SiteID,
                publisher.email AS user_email,
                sites.SiteName AS site_name,
                sites.cpm AS cpm,
                sites.auto_report_file AS Admeld,
                sites.tag_name AS tag_name,

                /*estimated*/
                IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0) AS estimated,

                /*impressions*/
                        CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)		
                                WHEN 1 THEN
                                SUM(
                                        /*pay*/			IF(sites.cpm IS NOT NULL, impressionEstim,
                                        /*flor*/		IF(sites.floor_pricing=1, impressionEstim,
                                        /*rubicon*/		IF(tags.type=2, impressionEstim,
                /*google admanager*/		IF(tags.type=1, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,
                /*google admanager new*/	IF(tags.type=4, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,
                                        /*others*/		impressionAdsense+impressionEstimRubicon+impressionEstim )))))
                                )
                                ELSE
                                ROUND(SUM(
                                        /*pay*/			IF(sites.cpm IS NOT NULL, allocated,
                                        /*flor*/		IF(sites.floor_pricing=1, impressionRubicon,
                                        /*rubicon*/		IF(tags.type=2, allocated,
                /*google admanager*/		IF(tags.type=1, allocated,
                /*google admanager new*/	IF(tags.type=4, allocated,
                                        /*others*/		allocated )))))
                                ))
                        END AS impressions,
                        

                /*paid impressions*/
                                CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)
                                        WHEN 1 THEN
                                        SUM(
                                                                    /*flor*/	IF(sites.floor_pricing=1, impressionEstim-impressionNofill, 
                                                                       /*rubicon*/	IF(tags.type=2, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                            /*google admanager new*/IF(tags.type=4, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                            /*google admanager*/	IF(tags.type=1, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                                                    /*other*/	impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim))))
                                        )
                                        ELSE
                                        ROUND(SUM(
                                                            /*pay*/			IF(sites.cpm IS NOT NULL, allocated,    			
                                                                    /*rubicon*/	IF(tags.type=2, allocated,    			
                                            /*google admanager new*/IF(tags.type=4, allocated,    			
                                            /*google admanager*/	IF(tags.type=1, allocated,    			
                                                                    /*flor*/	IF(sites.floor_pricing=1, allocated, 
                                                                    /*other*/	allocated)))))
                                        ))
                                END AS paid_impressions,


                                /*revenue*/
                        CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)
                                WHEN 1 THEN
                                ROUND(SUM(
                                                    /*pay*/				IF(sites.cpm IS NOT NULL, impressionEstim/1000*sites.cpm,
                                                    /*flor*/			IF(sites.floor_pricing=1, 0,
                                                    /*rubicon*/			IF(tags.type=2, 0,
                                    /*google admanager*/		IF(tags.type=1, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon+revenueAdExchange),		
                                    /*google admanager new*/	IF(tags.type=4, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon+revenueAdExchange),		
                                    /*others*/					(users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon) )))))
                                ), 2)
                                ELSE
                                ROUND(SUM(
                                                    /*pay*/				IF(sites.cpm IS NOT NULL, impressionEstim/1000*sites.cpm,
                                                    /*flor*/			IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*revenueRubicon)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000),
                                                    /*rubicon*/			IF(tags.type=2, (users_revshare.RevShare/100.0)*(revenueRubicon+revenueAdExchange),
                                    /*google admanager*/		IF(tags.type=1, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueRubicon+revenueAdExchange),				
                                    /*google admanager new*/	IF(tags.type=4, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueRubicon+revenueAdExchange),				
                                                    /*others*/			(users_revshare.RevShare/100.0)*(revenueAdsense+revenueRubicon) )))))
                                ), 2)
                        END AS revenue


        FROM
        (
                SELECT
                        madads_rubicon_table.SiteID as SiteID,
                        madads_rubicon_table.AdSize as AdSize,		
                        madads_rubicon_table.`query_date` as Date,	
                        SUM( IF(madads_rubicon_table.impressions IS NULL, 0, madads_rubicon_table.impressions) ) as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        SUM( IF(madads_rubicon_table.allocated_impressions IS NULL, 0, madads_rubicon_table.allocated_impressions) ) as allocated,
                        SUM( IF(madads_rubicon_table.revenue IS NULL, 0, madads_rubicon_table.revenue) ) as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim
                FROM madads_rubicon_table
                WHERE    
                             ".$whereDateRub." 
                         AND ".$whereSizeRub."     
                         AND madads_rubicon_table.`SiteID` IN (".$siteIDs.")
                GROUP BY madads_rubicon_table.`SiteID`, madads_rubicon_table.`AdSize`, madads_rubicon_table.`query_date`

                UNION ALL

                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,		
                        madads_dfp_table.`query_date` as Date,
                       0 as impressionRubicon,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim			
                FROM madads_dfp_table
                WHERE   
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."      
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='AdSense')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`

                UNION ALL

                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,				
                        madads_dfp_table.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim				
                FROM madads_dfp_table
                WHERE   
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."     
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`
                
                UNION ALL
	
                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,				
                        madads_dfp_table.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueAdExchange,
                        0 AS revenueEstim			
                FROM madads_dfp_table
                WHERE
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."     
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='MAM-Google-AdExchange')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`	

                UNION ALL

                SELECT
                        madads_estimated.SiteID as SiteID,
                        madads_estimated.AdSize as AdSize,			
                        madads_estimated.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        SUM( IF(madads_estimated.impressions IS NULL, 0, madads_estimated.impressions) ) AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim						
                FROM madads_estimated
                WHERE
                             ".$whereDateEst." 
                         AND ".$whereSizeEst."     
                         AND madads_estimated.`SiteID` IN (".$siteIDs.")
                GROUP BY madads_estimated.`SiteID`, madads_estimated.`AdSize`, madads_estimated.`query_date`

                UNION ALL

                SELECT
                        madads_nonfilled.SiteID as SiteID,
                        madads_nonfilled.AdSize as AdSize,			
                        madads_nonfilled.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        SUM( IF(madads_nonfilled.impressions IS NULL, 0, madads_nonfilled.impressions) ) AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim					
                FROM madads_nonfilled
                WHERE
                     ".$whereDateFill." 
                 AND ".$whereSizeFill."         
                 AND madads_nonfilled.`SiteID` IN (".$siteIDs.")		 
                GROUP BY madads_nonfilled.`SiteID`, madads_nonfilled.`AdSize`, madads_nonfilled.`query_date`


        )as res

        LEFT JOIN sites ON sites.SiteID=res.SiteID
        LEFT JOIN tags ON tags.site_id=res.SiteID
        LEFT JOIN publisher ON sites.PubID=publisher.ID
        LEFT JOIN users_revshare ON (sites.PubID=users_revshare.PubID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(users_revshare.date, '%Y-%m-%d'))        
        LEFT JOIN sites_floor_price ON (sites_floor_price.SiteID=sites.SiteID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d'))
       
        GROUP BY Date, res.SiteID
        ORDER BY res.Date DESC";
        
        return $this->_db->query($sql)->fetchAll();
        
  
    }
    
    
    public function getEmailForLetters($filter = false)
    {     
        $select = $this->_db->select()
                            ->from('sites', array(''))
                            ->join('users','users.id=sites.PubID', array('email'=>'users.email', 'name' => 'users.name', 'PubID' => 'users.id', 'code' => 'users.password'))
                            ->where('notification_control_admin IS NOT NULL')
                            ->where('notification_control_user IS NOT NULL')
                            ->group('sites.PubID');
        
        switch($filter){
            
            case 'live':       $select->where('sites.live = 1');          break;
            case 'no_longer':  $select->where('sites.lived IS NOT NULL'); break;
            case 'never_live': $select->where('sites.lived IS NULL');     break;
            default:break;
        
    	}
        
        $result = $this->getAdapter()->fetchAll($select);

        return $result;                
    }
    
    public function getAllZones()
    {
    	$select = $this->_db->select()
    	->from('display_size', array(
    			'id'=>'display_size.id',
    			'description'=>'display_size.description'
    	));
    	
    	$result = $this->getAdapter()->fetchAll($select);
    	return $result;    	 
    }
    
    public function getSiteInfoByName($name)
    {
    	$select = $this->_db->select()
    	->from('sites', array(
    			'SiteID'=>'sites.SiteID'
    	))
    	->where("sites.SiteName = '$name'");
    	$result = $this->getAdapter()->fetchRow($select);
    	return $result;    	 
    }
    
    public function deleteRubiconData($query_date, $zone, $name)
    {
    	$this->_name = 'madads_rubicon_table';
    	$this->_primary = 'SiteID';
    	
    	$this->delete(array("query_date = '$query_date'", "zone = '$zone'", "name = '$name'"));    	 
    }
    
    public function addRubiconData($data)
    {
    	$this->_name = 'madads_rubicon_table';
    	$this->_primary = 'SiteID';
    	    	
    	$result = array(
    			'SiteID'=>$data['SiteID'],
    			'AdSize'=>$data['AdSize'],
    			'zone'=>$data['Zone'],
    			'query_date'=>$data['Date'],
    			'name'=>$data['Size'],
    			'impressions'=>$data['Impressions'],
    			'allocated_impressions'=>$data['Paid_Impressions'],
    			'rate'=>$data['Rate'],
    			'revenue'=>$data['Revenue'],
    			'ecpm'=>$data['rCPM'],
    	);
    	
    	return $this->insert($result);
    	 
    }
    
    public function updateDfpCheck($siteID)
    {        
        $sql = "UPDATE `sites` SET `dfp_apiCheck`=1 WHERE `SiteID` = '".(int) $siteID."'";
        $this->getAdapter()->query($sql);          
    }    
    
    public function getAllUserSitesBySiteName($name)
    {
    	$select = $this->_db->select()
    	->from('sites', array(
    			'SiteURL'=>'sites.SiteURL'
    	))
    	->where("sites.SiteName = '$name'");
    	$result = $this->getAdapter()->fetchAll($select);
    	return $result;    	 
    }
    
    public function saveBlacklist($list)
    {
    	$this->_name = 'blacklists';
    	$this->_primary = 'id';
    
    	$result = array(
    			'list'=>$list
    	);
    
    	$where = $this->getAdapter()->quoteInto('id = ?', '1');
    	$this->update($result, $where);
    }
    
    public function getBlacklist()
    {
    	$select = $this->_db->select()
    	->from('blacklists', array(
    			'list'=>'blacklists.list'
    	));
    	$result = $this->getAdapter()->fetchRow($select);
    	return $result;
    }
    
    public function getReportByDate($siteIDs, $date, $ad_size)
    {
        
        if($date){
    		$whereDateRub = "madads_rubicon_table.query_date='$date'";
    		$whereDateDfp = "madads_dfp_table.query_date='$date'";
    		$whereDateEst = "madads_estimated.query_date='$date'";
    		$whereDateFill = "madads_nonfilled.query_date='$date'";
        }else{
                $whereDateRub = '1=1';
    		$whereDateDfp = '1=1';
    		$whereDateEst = '1=1';
    		$whereDateFill = '1=1';
        }                
    	

    	if($ad_size){
    		$whereSizeRub = 'madads_rubicon_table.AdSize = ' . $ad_size;
    		$whereSizeDfp = 'madads_dfp_table.AdSize = ' . $ad_size;
    		$whereSizeEst = 'madads_estimated.AdSize = ' . $ad_size;
    		$whereSizeFill = 'madads_nonfilled.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSizeRub = '1=1';
    		$whereSizeDfp = '1=1';
    		$whereSizeEst = '1=1';
    		$whereSizeFill = '1=1';
    	}
        
        $sql = "SELECT
                res.Date as Date,
                res.SiteID as SiteID,
                users.email AS user_email,
                sites.SiteName AS site_name,
                sites.cpm AS cpm,
                sites.auto_report_file AS Admeld,
                sites.tag_name AS tag_name,

                /*estimated*/
                IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0) AS estimated,

                /*impressions*/
                        CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)		
                                WHEN 1 THEN
                                SUM(
                                                            /*pay*/			IF(sites.cpm IS NOT NULL, impressionEstim,
                                                            /*flor*/		IF(sites.floor_pricing=1, impressionEstim,
                                                            /*rubicon*/		IF(tags.type=2, impressionEstim,
                                    /*google admanager*/		IF(tags.type=1, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,
                                    /*google admanager new*/	IF(tags.type=4, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,
                                                            /*others*/		impressionAdsense+impressionEstimRubicon+impressionEstim )))))
                                )
                                ELSE
                                ROUND(SUM(
                                                            /*pay*/			IF(sites.cpm IS NOT NULL, allocated,
                                                            /*flor*/		IF(sites.floor_pricing=1, impressionRubicon,
                                                            /*rubicon*/		IF(tags.type=2, allocated,
                                    /*google admanager*/		IF(tags.type=1, allocated,
                                    /*google admanager new*/	IF(tags.type=4, allocated,
                                                            /*others*/		allocated )))))
                                ))
                        END AS impressions,
                        

                /*paid impressions*/
                             	CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)
						WHEN 1 THEN
						SUM(
                                                                    /*flor*/	IF(sites.floor_pricing=1, impressionEstim-impressionNofill, 
                                                                       /*rubicon*/	IF(tags.type=2, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                            /*google admanager new*/IF(tags.type=4, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                            /*google admanager*/	IF(tags.type=1, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                                                    /*other*/	impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim))))
    									)
						ELSE
						ROUND(SUM(
                                                            /*pay*/			IF(sites.cpm IS NOT NULL, allocated,    			
                                                                    /*rubicon*/	IF(tags.type=2, allocated,    			
                                            /*google admanager new*/IF(tags.type=4, allocated,    			
                                            /*google admanager*/	IF(tags.type=1, allocated,    			
                                                                    /*flor*/	IF(sites.floor_pricing=1, allocated, 
                                                                    /*other*/	allocated)))))
    									))
					END AS paid_impressions,


                                /*revenue*/
                  		CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)
						WHEN 1 THEN
						ROUND(SUM(
						/*pay*/				IF(sites.cpm IS NOT NULL, impressionEstim/1000*sites.cpm,
						/*flor*/			IF(sites.floor_pricing=1, 0,
				/*rubicon*/			IF(tags.type=2, 0,
				/*google admanager*/		IF(tags.type=1, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon+revenueAdExchange),		
				/*google admanager new*/	IF(tags.type=4, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon+revenueAdExchange),		
				/*others*/					(users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon) )))))
						), 2)
						ELSE
						ROUND(SUM(
						/*pay*/				IF(sites.cpm IS NOT NULL, impressionEstim/1000*sites.cpm,
						/*flor*/			IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*revenueRubicon)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000),
				/*rubicon*/			IF(tags.type=2, (users_revshare.RevShare/100.0)*(revenueRubicon+revenueAdExchange),
				/*google admanager*/		IF(tags.type=1, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueRubicon+revenueAdExchange),				
				/*google admanager new*/	IF(tags.type=4, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueRubicon+revenueAdExchange),				
						/*others*/			(users_revshare.RevShare/100.0)*(revenueAdsense+revenueRubicon) )))))
						), 2)
					END AS revenue

        FROM
        (
                SELECT
                        madads_rubicon_table.SiteID as SiteID,
                        madads_rubicon_table.AdSize as AdSize,		
                        madads_rubicon_table.`query_date` as Date,	
                        SUM( IF(madads_rubicon_table.impressions IS NULL, 0, madads_rubicon_table.impressions) ) as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        SUM( IF(madads_rubicon_table.allocated_impressions IS NULL, 0, madads_rubicon_table.allocated_impressions) ) as allocated,
                        SUM( IF(madads_rubicon_table.revenue IS NULL, 0, madads_rubicon_table.revenue) ) as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim
                FROM madads_rubicon_table
                WHERE    
                             ".$whereDateRub." 
                         AND ".$whereSizeRub."     
                         AND madads_rubicon_table.`SiteID` IN (".$siteIDs.")
                GROUP BY madads_rubicon_table.`SiteID`, madads_rubicon_table.`AdSize`, madads_rubicon_table.`query_date`

                UNION ALL

                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,		
                        madads_dfp_table.`query_date` as Date,
                       0 as impressionRubicon,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim			
                FROM madads_dfp_table
                WHERE   
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."      
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='AdSense')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`

                UNION ALL

                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,				
                        madads_dfp_table.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim				
                FROM madads_dfp_table
                WHERE   
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."     
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`
                
                UNION ALL
	
                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,				
                        madads_dfp_table.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueAdExchange,
                        0 AS revenueEstim			
                FROM madads_dfp_table
                WHERE
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."     
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='MAM-Google-AdExchange')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`	

                UNION ALL

                SELECT
                        madads_estimated.SiteID as SiteID,
                        madads_estimated.AdSize as AdSize,			
                        madads_estimated.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        SUM( IF(madads_estimated.impressions IS NULL, 0, madads_estimated.impressions) ) AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim						
                FROM madads_estimated
                WHERE
                             ".$whereDateEst." 
                         AND ".$whereSizeEst."     
                         AND madads_estimated.`SiteID` IN (".$siteIDs.")
                GROUP BY madads_estimated.`SiteID`, madads_estimated.`AdSize`, madads_estimated.`query_date`

                UNION ALL

                SELECT
                        madads_nonfilled.SiteID as SiteID,
                        madads_nonfilled.AdSize as AdSize,			
                        madads_nonfilled.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        SUM( IF(madads_nonfilled.impressions IS NULL, 0, madads_nonfilled.impressions) ) AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim					
                FROM madads_nonfilled
                WHERE
                     ".$whereDateFill." 
                 AND ".$whereSizeFill."         
                 AND madads_nonfilled.`SiteID` IN (".$siteIDs.")		 
                GROUP BY madads_nonfilled.`SiteID`, madads_nonfilled.`AdSize`, madads_nonfilled.`query_date`


        )as res

        LEFT JOIN sites ON sites.SiteID=res.SiteID
        LEFT JOIN tags ON tags.site_id=res.SiteID
        LEFT JOIN users_revshare ON (users_revshare.PubID = sites.PubID AND users_revshare.date <= res.Date)
        LEFT JOIN sites_floor_price ON (sites_floor_price.SiteID=sites.SiteID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d'))
        LEFT JOIN users ON users.id = sites.PubID

       
        GROUP BY Date, res.SiteID
        ORDER BY res.Date DESC";
        
        return $this->_db->query($sql)->fetchAll();
        
  
    }
    
    public function getFinalDataByUser($siteIDs, $start_date, $end_date, $ad_size)
    {     
        
        if($start_date && $end_date){
    		$whereDateRub = "DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')<='$end_date'";
    		$whereDateDfp = "DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')<='$end_date'";
    		$whereDateEst = "DATE_FORMAT(madads_estimated.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_estimated.query_date, '%m/%d/%Y')<='$end_date'";
    		$whereDateFill = "DATE_FORMAT(madads_nonfilled.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_nonfilled.query_date, '%m/%d/%Y')<='$end_date'";
    	}
    	else{
    		$whereDateRub = '1=1';
    		$whereDateDfp = '1=1';
    		$whereDateEst = '1=1';
    		$whereDateFill = '1=1';
    	}
    	if($ad_size){
    		$whereSizeRub = 'madads_rubicon_table.AdSize = ' . $ad_size;
    		$whereSizeDfp = 'madads_dfp_table.AdSize = ' . $ad_size;
    		$whereSizeEst = 'madads_estimated.AdSize = ' . $ad_size;
    		$whereSizeFill = 'madads_nonfilled.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSizeRub = '1=1';
    		$whereSizeDfp = '1=1';
    		$whereSizeEst = '1=1';
    		$whereSizeFill = '1=1';
    	}
        
        $sql = "SELECT
                res.Date as Date,
                res.SiteID as SiteID,
                users.email AS user_email,
                sites.SiteName AS site_name,
                sites.cpm AS cpm,
                sites.auto_report_file AS Admeld,
                sites.tag_name AS tag_name,

                /*estimated*/
                IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0) AS estimated,

                /*impressions*/
                        CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)		
                                WHEN 1 THEN
                                SUM(
                                                            /*pay*/			IF(sites.cpm IS NOT NULL, impressionEstim,
                                                            /*flor*/		IF(sites.floor_pricing=1, impressionEstim,
                                                            /*rubicon*/		IF(tags.type=2, impressionEstim,
                                    /*google admanager*/		IF(tags.type=1, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,
                                    /*google admanager new*/	IF(tags.type=4, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,
                                                            /*others*/		impressionAdsense+impressionEstimRubicon+impressionEstim )))))
                                )
                                ELSE
                                ROUND(SUM(
                                                            /*pay*/			IF(sites.cpm IS NOT NULL, allocated,
                                                            /*flor*/		IF(sites.floor_pricing=1, impressionRubicon,
                                                            /*rubicon*/		IF(tags.type=2, allocated,
                                    /*google admanager*/		IF(tags.type=1, allocated,
                                    /*google admanager new*/	IF(tags.type=4, allocated,
                                                            /*others*/		allocated )))))
                                ))
                        END AS impressions,
                        

                /*paid impressions*/
                CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)
                                WHEN 1 THEN
                                SUM(
                                                                    /*flor*/	IF(sites.floor_pricing=1, impressionEstim-impressionNofill, 
                                                                       /*rubicon*/	IF(tags.type=2, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                            /*google admanager new*/IF(tags.type=4, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                            /*google admanager*/	IF(tags.type=1, impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim,    			
                                                                    /*other*/	impressionAdsense+impressionEstimRubicon+impressionAdExchange+impressionEstim))))
                                                        )
                                ELSE
                                ROUND(SUM(
                                                            /*pay*/			IF(sites.cpm IS NOT NULL, allocated,    			
                                                                    /*rubicon*/	IF(tags.type=2, allocated,    			
                                            /*google admanager new*/IF(tags.type=4, allocated,    			
                                            /*google admanager*/	IF(tags.type=1, allocated,    			
                                                                    /*flor*/	IF(sites.floor_pricing=1, allocated, 
                                                                    /*other*/	allocated)))))
                                                        ))
                        END AS paid_impressions,


                /*revenue*/
                CASE IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)
                                WHEN 1 THEN
                                ROUND(SUM(
                                                    /*pay*/				IF(sites.cpm IS NOT NULL, impressionEstim/1000*sites.cpm,
                                                    /*flor*/			IF(sites.floor_pricing=1, 0,
                                                    /*rubicon*/			IF(tags.type=2, 0,
                                    /*google admanager*/		IF(tags.type=1, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon+revenueAdExchange),		
                                    /*google admanager new*/	IF(tags.type=4, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon+revenueAdExchange),		
                                    /*others*/					(users_revshare.RevShare/100.0) * (revenueAdsense+revenueEstimRubicon) )))))
                                ), 2)
                                ELSE
                                ROUND(SUM(
                                                    /*pay*/				IF(sites.cpm IS NOT NULL, impressionEstim/1000*sites.cpm,
                                                    /*flor*/			IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*revenueRubicon)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000),
                                                    /*rubicon*/			IF(tags.type=2, (users_revshare.RevShare/100.0)*(revenueRubicon+revenueAdExchange),
                                    /*google admanager*/		IF(tags.type=1, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueRubicon+revenueAdExchange),				
                                    /*google admanager new*/	IF(tags.type=4, (users_revshare.RevShare/100.0) * (revenueAdsense+revenueRubicon+revenueAdExchange),				
                                                    /*others*/			(users_revshare.RevShare/100.0)*(revenueAdsense+revenueRubicon) )))))
                                ), 2)
                        END AS revenue


        FROM
        (
                SELECT
                        madads_rubicon_table.SiteID as SiteID,
                        madads_rubicon_table.AdSize as AdSize,		
                        madads_rubicon_table.`query_date` as Date,	
                        SUM( IF(madads_rubicon_table.impressions IS NULL, 0, madads_rubicon_table.impressions) ) as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        SUM( IF(madads_rubicon_table.allocated_impressions IS NULL, 0, madads_rubicon_table.allocated_impressions) ) as allocated,
                        SUM( IF(madads_rubicon_table.revenue IS NULL, 0, madads_rubicon_table.revenue) ) as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim
                FROM madads_rubicon_table
                WHERE    
                             ".$whereDateRub." 
                         AND ".$whereSizeRub."     
                         AND madads_rubicon_table.`SiteID` IN (".$siteIDs.")
                GROUP BY madads_rubicon_table.`SiteID`, madads_rubicon_table.`AdSize`, madads_rubicon_table.`query_date`

                UNION ALL

                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,		
                        madads_dfp_table.`query_date` as Date,
                       0 as impressionRubicon,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim			
                FROM madads_dfp_table
                WHERE   
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."      
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='AdSense')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`

                UNION ALL

                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,				
                        madads_dfp_table.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim				
                FROM madads_dfp_table
                WHERE   
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."     
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`
                
                UNION ALL
	
                SELECT
                        madads_dfp_table.SiteID as SiteID,
                        madads_dfp_table.AdSize as AdSize,				
                        madads_dfp_table.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) ) AS impressionAdExchange,
                        0 AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) ) AS revenueAdExchange,
                        0 AS revenueEstim			
                FROM madads_dfp_table
                WHERE
                            ".$whereDateDfp." 
                        AND ".$whereSizeDfp."     
                        AND  madads_dfp_table.`SiteID` IN (".$siteIDs.")
                        AND (madads_dfp_table.order_name='MAM-Google-AdExchange')
                GROUP BY madads_dfp_table.`SiteID`, madads_dfp_table.`AdSize`, madads_dfp_table.`query_date`	

                UNION ALL

                SELECT
                        madads_estimated.SiteID as SiteID,
                        madads_estimated.AdSize as AdSize,			
                        madads_estimated.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        SUM( IF(madads_estimated.impressions IS NULL, 0, madads_estimated.impressions) ) AS impressionEstim,
                        0 AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim						
                FROM madads_estimated
                WHERE
                             ".$whereDateEst." 
                         AND ".$whereSizeEst."     
                         AND madads_estimated.`SiteID` IN (".$siteIDs.")
                GROUP BY madads_estimated.`SiteID`, madads_estimated.`AdSize`, madads_estimated.`query_date`

                UNION ALL

                SELECT
                        madads_nonfilled.SiteID as SiteID,
                        madads_nonfilled.AdSize as AdSize,			
                        madads_nonfilled.`query_date` as Date,
                        0 as impressionRubicon,
                        0 AS impressionAdsense,
                        0 AS impressionEstimRubicon,
                        0 AS impressionAdExchange,
                        0 AS impressionEstim,
                        SUM( IF(madads_nonfilled.impressions IS NULL, 0, madads_nonfilled.impressions) ) AS impressionNofill,
                        NULL AS allocated,
                        0 as `revenueRubicon`,
                        0 AS revenueAdsense,
                        0 AS revenueEstimRubicon,
                        0 AS revenueAdExchange,
                        0 AS revenueEstim					
                FROM madads_nonfilled
                WHERE
                     ".$whereDateFill." 
                 AND ".$whereSizeFill."         
                 AND madads_nonfilled.`SiteID` IN (".$siteIDs.")		 
                GROUP BY madads_nonfilled.`SiteID`, madads_nonfilled.`AdSize`, madads_nonfilled.`query_date`


        )as res

        LEFT JOIN sites ON sites.SiteID=res.SiteID
        LEFT JOIN tags ON tags.site_id=res.SiteID
        LEFT JOIN users_revshare ON (users_revshare.PubID = sites.PubID AND users_revshare.date <= res.Date)

        LEFT JOIN sites_floor_price ON (sites_floor_price.SiteID=sites.SiteID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d'))
        LEFT JOIN users ON users.id = sites.PubID

       
        GROUP BY Date 
        ORDER BY res.Date DESC";
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getRubiconData($SiteID, $AdSize, $Date)
    {
    	$select = $this->_db->select()
    	->from('madads_rubicon_table', array(
    			'lock_data'=>'madads_rubicon_table.lock_data'
    	))
    	->where("madads_rubicon_table.SiteID = '$SiteID'")
        ->where("madads_rubicon_table.AdSize = '$AdSize'")
        ->where("madads_rubicon_table.query_date = '$Date'");
    	$result = $this->getAdapter()->fetchRow($select);
    	return $result;    	 
    }
    
    public function deleteSite($SiteID)
    {
        $this->_name = 'sites';    
        $this->delete(array("SiteID = '$SiteID'"));                            
    }
    
    public function updateNewSite($array, $id)
    {
    	$this->_name = 'sites';
    	$this->update($array, 'SiteID = ' . $id);
    }    
    
    public function rejectSite($SiteID, $notes = null)
    {
        $this->_name = 'sites';
        
        $notes = $notes ? $notes : NULL;
        $where = $this->getAdapter()->quoteInto('SiteID = ?', $SiteID);
        
        $this->update(array('status' => 2, 
        					'reject_date' => date("Y-m-d H:i:s"),
        					'reject_notes' => $notes), $where);        
    }
    
    public function confirmSite($SiteID, $data)
    {
    	$this->_name = "sites";
        $result = array('SiteName'=>$data['SiteName'],
                        'SiteURL'=>$data['SiteURL'],
                        'category'=>$data['category'],
                        //'approved'=>date("Y-m-d H:i:s"),
                        'notes'=>$data['notes'],
                        'type'=>$data['type'],
        		'rubicon_type'=>$data['rubicon_type'] ? $data['rubicon_type'] : 1,
                        'floor_pricing'=>$data['floor_pricing'] ? 1 : NULL,
        		'cpm'=>$data['cpm'] ? $data['cpm'] : NULL,
                        'rub_io'=>$data['rub_io'] ? $data['rub_io'] : NULL,
                        'auto_report_file' => $data['auto_report_file'] ? 1 : NULL,
                        'email_notlive_3day' => $data['email_notlive_3day'] ? 1 : NULL,
                        'email_notlive_7day' => 1,
                        'create_dfp_passbacks' => $data['create_dfp_passbacks'] ? 1 : NULL,
                        'status' => 3,
                        'denied_no_tag' => NULL, 
                        'limited_demand_tag' => $data['limited_demand_tag'] ? 1 : NULL,
                        'creative_passback' => !empty($data['creative_passback']) ? $data['creative_passback'] : NULL,
                        'admin_email'=>$data['admin_email'],
                        'define_url'=>!empty($data['define_url']) ? $data['define_url'] : NULL);
                        
        $where = $this->getAdapter()->quoteInto('SiteID = ?', $SiteID);
        $this->update($result, $where);        
    }
    
    public function getArraySites($PubID)
    {
        $sql = $this->_db->select()
                    ->from(array('s' => 'sites'), array('s.SiteID AS SiteID', 's.SiteName AS SiteName'))
                    ->where('s.status = 3')
                    ->where('s.PubID = ?', $PubID)
                    ->join(array('t' => 'tags'),('t.site_id = s.SiteID'), array(''));

        $data = $this->_db->query($sql)->fetchAll();

        $result = array();

        foreach($data as $item){ $result[$item['SiteID']] = $item['SiteName']; }

        return $result;

    }

    public function getArrayBurstSites($PubID)
    {
        $sql = $this->_db->select()
                    ->from(array('s' => 'sites'), array('s.SiteID AS SiteID', 's.SiteName AS SiteName'))
                    ->where('s.status = 3')
                    ->where('s.PubID = ?', $PubID)
                    ->where('t.type != 6')
                    ->join(array('t' => 'tags'),('t.site_id = s.SiteID'), array(''));

        $data = $this->_db->query($sql)->fetchAll();

        $result = array();

        foreach($data as $item){ $result[$item['SiteID']] = $item['SiteName']; }

        return $result;

    }
    
    public function checkSiteCpm($SiteID)
    {
        $sql = $this->_db->select()
                    ->from(array('s' => 'sites'), array(''))
                    ->where('s.SiteID = ?', $SiteID)
                    ->where('s.floor_pricing = 1')
                    ->join(array('f' => 'sites_floor_price'),('f.SiteID = s.SiteID'),
                           array('f.price AS price', 
                                 'f.percent AS percent'))
                    ->order('f.date DESC')
                    ->limit(1);
        
        return $this->_db->query($sql)->fetchAll();
                
    }
    
    
    public function getAccess($PubID, $SiteID)
    {
        $sql = $this->_db->select()
                    ->from('sites', array('SiteID'))
                    ->where('PubID = ?', $PubID)
                    ->where('SiteID = ?', $SiteID);
        
        return $this->_db->query($sql)->fetch();
    }
    
    
    public function getDailyStats($year, $month)
    {
           $start = date('Y-m-d', strtotime($year.'-'.$month)); 
           $str = preg_split('/[-]/', $start);
           $end = date("Y-m-d", mktime(0, 0, 0, $str[1] + 1 /* month */, $str[2], $str[0]));

           $sql = $this->_db->select()
                       ->from(array('ss' => 'stat_site'), array('ss.lived_all AS numSite', 'ss.date', 'ss.tag_created', 'ss.prev_live_sites', 'ss.first_live', 'ss.inactive'))
                       ->joinLeft(array('su' => 'stat_user'), 'su.date = ss.date', array('su.lived_all AS numUser'))
                       ->where('ss.date != ?', date('Y-m-d'))
                       ->where('ss.date >= ?', $start)
                       ->where('ss.date < ?', $end)
                       ->group('ss.date')
                       ->order('ss.date ASC');

           return $this->_db->query($sql)->fetchAll();                
    }
        
    public function getMaxSiteLive()
    {
        $sql = $this->_db->select()
                    ->from('stat_site', array('lived_all AS num', 'DATE_FORMAT(date, "%Y/%m/%d") AS date'))
                    ->where('lived_all = (SELECT MAX(lived_all) FROM stat_site)')
                    ->order('date ASC')
                    ->limit(1);

        return $this->_db->query($sql)->fetch();        
    }

    public function getMaxUserLive()
    {
        $sql = $this->_db->select()
                    ->from('stat_user', array('lived_all AS num', 'DATE_FORMAT(date, "%Y/%m/%d") AS date'))
                    ->where('lived_all = (SELECT MAX(lived_all) FROM stat_user)')
                    ->order('date ASC')
                    ->limit(1);

        return $this->_db->query($sql)->fetch();        
    }
    

    public function setNotification($data)
    {
    	$this->_name = "sites_want_notification";
        $this->_primary = 'id';
        
        $result = array(
                        'to'=>$data['filter'] ? $data['filter'] : null,
                        'subject'=>$data['subject'] ? $data['subject'] : null,
                        'message'=>$data['message'] ? $data['message'] : null,
                        'from_email'=>$data['admin_email'] ? $data['admin_email'] : null,
                        'from_name'=>$data['admin_name'] ? $data['admin_name'] : null
                        );
                        
        return $this->insert($result);
    }
        
    public function getSitesWidthTags()
    {
            $this->_name = "sites";
            $select = $this->_db->select()
                                ->from('tags', array(
                                                    'site_id'=>'sites.SiteID',
                                                    'name'=>'sites.SiteName'))
                               ->joinLeft('sites', 'tags.site_id=sites.SiteID', array())
                               ->order("sites.SiteName");
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                        
    }
    
    public function getDataByID($siteID)
    {
        $this->_name = 'sites';
        
        $sql = $this->_db->select()
                    ->from('sites', array('*'))
                    ->where('SiteID = ?', $siteID);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getIDbyDoname($doname)
    {
        $this->_name = 'sites';
        
        $sql = " SELECT `PubID`, `SiteID`, `SiteName` FROM `sites` WHERE LOCATE(`SiteName`, '".$doname."') !=0 LIMIT 1 ";
             
        return $this->_db->query($sql)->fetch();
    }
    
    public function setSiteTable()
    {
        $this->_name = 'sites';
    }
    
    // Logging changes for website    
    public function logging($pubId,$siteId, $formData)
    {
    	$websiteLogsModel = new Application_Model_DbTable_WebsiteLogs();
    	$websiteLogsModel->logIFramesChanges($pubId, $siteId, $formData);
    	$websiteLogsModel->logServingUrlChanges($pubId, $siteId, $formData);
    	$websiteLogsModel->logSiteUrlChanges($pubId, $siteId, $formData);
	}
	
    public function everLiveByUser($PubID)
    {
        $this->_name = "sites";
        $sql = $this->_db->select()
                    ->from($this->_name, array('SiteID'))
                    ->where('PubID = ?', $PubID)
                    ->where('lived = 1');
        
        return $this->_db->query($sql)->fetch();
    }	
	   
    public function setTable($name = 'sites')
    {
        $this->_name = $name;
    }	    
    
    public function getPrevLiveByData($date)
    {
        $arrDate = preg_split('/[-]/', $date);            
        $DAY1 = date('Y-m-d', mktime(0, 0, 0, $arrDate[1], $arrDate[2] + 1, $arrDate[0])); 

        $sql = "SELECT s.SiteID, s.PubID, s.SiteName, u.email 
                  FROM sites AS s 
                  JOIN users AS u ON u.id = s.PubID
                  JOIN sites_live AS sl1 ON sl1.SiteID = s.SiteID AND sl1.live = 1 AND sl1.date = '".$DAY1."'
                  JOIN sites_live AS sl2 ON sl2.SiteID = s.SiteID AND sl2.live IS NULL AND sl2.date = '".$date."' 
                  JOIN sites_live AS sl3 ON sl3.SiteID = s.SiteID AND sl3.live = 1 AND sl3.date < sl2.date     
              GROUP BY s.SiteID";
        
        return $this->_db->query($sql)->fetchAll();
    }  
    
    public function getAllByFirstLive($date)
    {
        $this->_name = "sites";
        
        $sql = $this->_db->select()
                    ->from(array('s' => $this->_name), 
                           array('s.SiteID', 's.PubID', 's.SiteName'))
                    ->join(array('u' => 'users'), 'u.id = s.PubID',
                           array('u.email'))
                    ->where('s.first_live = ?', $date);
        
        return $this->_db->query($sql)->fetchAll();
    } 
    
    public function getAllByInactive($date)
    {
        $this->_name = "sites";

        $sql = $this->_db->select()
            ->from(array('i' => 'inactive_sites_count'),  array('i.SiteID'))
            ->join(array('u' => 'users'), 'u.id = i.PubID', array('u.email'))
            ->join(array('s' => 'sites'), 's.SiteID = i.SiteID', array('s.SiteName', 's.PubID', 's.manual_followup'))
            ->where('i.inactive_date = ?', $date);

        return $this->_db->query($sql)->fetchAll();
    }

    function getConfirmSitesPublisher($pub_id){
        $sql = $this->_db->select()
            ->from([
                's' => $this->_name
            ],
            [
                's.PubID',
                's.SiteID',
                's.SiteName'
            ])
            ->where('s.PubID = ?', $pub_id)
            ->where('s.status = ?', 3);

        return $this->_db->query($sql)->fetchAll();
    }

    function getSitesWidthTagsByPubID($pub_id){
        $sql = $this->_db->select()
            ->from([
                's' => $this->_name
            ],
                [
                    's.PubID',
                    's.SiteID',
                    's.SiteName'
                ])
            ->join(['st'=>'sites_tags'], 'st.site_id=s.SiteID', [])
            ->where('s.PubID = ?', $pub_id)
            ->where('s.status = ?', 3)
            ->group(['st.site_id']);

        return $this->_db->query($sql)->fetchAll();
    }
}