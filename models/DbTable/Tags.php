<?php


class Application_Model_DbTable_Tags extends Zend_Db_Table_Abstract
{
    protected $_name = 'sites';
    
    public function getWidthTags()
    {
            $this->_name = "sites";
            $select = $this->_db->select()
                                ->from('tags', array(
                                                    'site_id'=>'sites.SiteID',
                                                    'tag_id'=>'tags.id',
                                                    'user_id'=>'sites.PubID',
                                                    'name'=>'sites.SiteName',
                                                    'notified'=>'sites.notified',
                                                    'notes'=>'sites.notes',
                                                    'sent_approval'=>'sites.sent_approval'))
                               ->joinLeft('sites', 'tags.site_id=sites.SiteID', array())
                               ->joinLeft('users', 'users.id=sites.PubID', 
                                          array('email'=>'users.email'));
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                
    }
    
    public function getNoTags()
    {
            $this->_name = "sites";
            
            $select = $this->_db->select()
                                ->from('sites', array(
                                                    'site_id'=>'sites.SiteID',
                                                    'tag_id'=>'tags.id',
                                                    'user_id'=>'sites.PubID',
                                                    'name'=>'sites.SiteName',
                                                    'notes'=>'sites.notes',
                                                    'sent_approval'=>'sites.sent_approval'))
                               ->joinLeft('tags', 'tags.site_id=sites.SiteID', array())
                               ->joinLeft('users', 'users.id=sites.PubID', array('email'=>'users.email'))
                               ->where('users.reg_AdExchage IS NULL') 
                               ->where("tags.user_id IS NULL"); 
            $result = $this->getAdapter()->fetchAll($select);

            return $result;                
    }
    
    public function getNoTagsAdExchange()
    {
           $this->_name = "sites";
            
            $select = $this->_db->select()
                                ->from('sites', array(
                                                    'site_id'=>'sites.SiteID',
                                                    'tag_id'=>'tags.id',
                                                    'user_id'=>'sites.PubID',
                                                    'name'=>'sites.SiteName',
                                                    'notes'=>'sites.notes',
                                                    'sent_approval'=>'sites.sent_approval'))
                               ->joinLeft('tags', 'tags.site_id=sites.SiteID', array())
                               ->joinLeft('users', 'users.id=sites.PubID', array())
                               ->where('users.reg_AdExchage IS NOT NULL') 
                               ->where("tags.user_id IS NULL"); 
           
            $result = $this->getAdapter()->fetchAll($select);

            return $result;        
    }

    public function countNoTags()
    {
            $this->_name = "sites";
            
            $select = $this->_db->select()
                                ->from('sites', array(
                                                            'count'=>'COUNT(sites.SiteID)'
                                                        ))
                               ->joinLeft('sites_tags', 'sites_tags.site_id=sites.SiteID', array())
                               ->joinLeft('users', 'users.id=sites.PubID', array())
                               ->where("sites_tags.pub_id IS NULL AND sites.status = 3")
                               ; 
            $result = $this->getAdapter()->fetchRow($select);
            return $result;                
    }

    public function countNoTagsAsx()
    {
            $this->_name = "sites";
            
            $select = $this->_db->select()
                                ->from('sites', array(
                                                            'count'=>'COUNT(sites.SiteID)'
                                                        ))
                               ->joinLeft('tags', 'tags.site_id=sites.SiteID', array())
                               ->joinLeft('users', 'users.id=sites.PubID', array())
                               ->joinLeft('sites_want_api', 'sites_want_api.SiteID=sites.SiteID', array())
                               ->where("users.reg_AdExchage IS NOT NULL AND tags.user_id IS NULL AND sites_want_api.SiteID IS NULL AND sites.status = 3")
                               ; 
            $result = $this->getAdapter()->fetchRow($select);
            return $result;                
    }    
    
    public function save($data){
        
        $this->_name = "tags";
        
        $result = array(
                        'site_id'=>$data['site_id'],
                        'user_id'=>$data['user_id'],
        				'type'=>$data['type'] ? $data['type'] : NULL,
                        'rubiconType'=>$data['rubiconType'] ? $data['rubiconType'] : NULL,
                        'head'=> !empty($data['head']) ? $data['head'] : NULL,
                        'account_id_728'=>!empty($data['account_id_728']) ? $data['account_id_728'] : NULL,
                        'site_id_728'=>!empty($data['site_id_728']) ? $data['site_id_728'] : NULL,
                        'zone_id_728'=>!empty($data['zone_id_728']) ? $data['zone_id_728'] : NULL,
                        'google_id_728'=>!empty($data['google_id_728']) ? $data['google_id_728'] : NULL,
                        'account_id_300'=>!empty($data['account_id_300']) ? $data['account_id_300'] : NULL,
                        'site_id_300'=>!empty($data['site_id_300']) ? $data['site_id_300'] : NULL,
                        'zone_id_300'=>!empty($data['zone_id_300']) ? $data['zone_id_300'] : NULL,
                        'google_id_300'=>!empty($data['google_id_300']) ? $data['google_id_300'] : NULL,
                        'account_id_160'=>!empty($data['account_id_160']) ? $data['account_id_160'] : NULL,
                        'site_id_160'=>!empty($data['site_id_160']) ? $data['site_id_160'] : NULL,
                        'zone_id_160'=>!empty($data['zone_id_160']) ? $data['zone_id_160'] : NULL,
                        'google_id_160'=>!empty($data['google_id_160']) ? $data['google_id_160'] : NULL,
        				'accountRub_id'=>!empty($data['accountRub_id']) ? $data['accountRub_id'] : NULL,
        				'siteRub_id'=>!empty($data['siteRub_id']) ? $data['siteRub_id'] : NULL,
        				'zoneRub_id'=>!empty($data['zoneRub_id']) ? $data['zoneRub_id'] : NULL,
		        		'dfp_728'=>!empty($data['dfp_728']) ? $data['dfp_728'] : NULL,
		        		'dfp_300'=>!empty($data['dfp_300']) ? $data['dfp_300'] : NULL,
		        		'dfp_160'=>!empty($data['dfp_160']) ? $data['dfp_160'] : NULL,  
                        'dfp'=>!empty($data['dfp']) ? $data['dfp'] : NULL,  
                        'site_name'=>$data['site_name'] ? $data['site_name'] : NULL,  
                        'kPubID'=>!empty($data['kPubID']) ? $data['kPubID'] : NULL,  
                        'kSiteID'=>!empty($data['kSiteID']) ? $data['kSiteID'] : NULL,  
                        'kadID'=>!empty($data['kadID']) ? $data['kadID'] : NULL,  
                        'kadWidth'=>!empty($data['kadWidth']) ? $data['kadWidth'] : NULL,  
                        'kadHeight'=>!empty($data['kadHeight']) ? $data['kadHeight'] : NULL,  
                        'kadType'=>!empty($data['kadType']) ? $data['kadType'] : NULL,  
                        'kadPageUrl'=>!empty($data['kadPageUrl']) ? $data['kadPageUrl'] : NULL,
                        'slot_uuid'=>!empty($data['slot_uuid']) ? $data['slot_uuid'] : NULL,
                        'PulsePubID'=>!empty($data['PulsePubID']) ? $data['PulsePubID'] : NULL,
                        'PulseTagID'=>!empty($data['PulseTagID']) ? $data['PulseTagID'] : NULL,
                        'updated'   => date('Y-m-d')
                        );
                      
        if(isset($data['id']) && $data['id']){
            $where = $this->getAdapter()->quoteInto('id  = ?', $data['id']);
            $this->update($result, $where);
        }else{
                          $result['created'] = date('Y-m-d');
            $this->insert($result);
        }
    }
    
    public function getTagById($id)
    {
        $this->_name = "tags";
        
        $data = $this->fetchRow("id = '$id'");

        if($data){
            return $data->toArray();
        }else{
            return FALSE;
        }            
        
    }

    public function getTagBySiteID($siteID)
    {
        $this->_name = "tags";
        
        $data = $this->fetchRow("site_id = '$siteID'");

        if($data){
            return $data->toArray();
        }else{
            return FALSE;
        }            
        
    }    
    
    public function getSitesByUserId($userID)
    {
        $this->_name = "tags";

        $select = $this->_db->select()
                            ->from('tags', array(
                                                        'site_id'=>'sites.SiteID',
                                                        'name'=>'sites.SiteName',
														'type'=>'sites.type',
                            							'siteType'=>'tags.type'
                                                    ))
                           ->joinLeft('sites', 'tags.site_id=sites.SiteID', array())
                           ->where("tags.user_id = '$userID'")
                           ; 
        $result = $this->getAdapter()->fetchAll($select);

        return $result;                
    }

    public function getConfirmSitesByUserId($userID)
    {
        $this->_name = "tags";

        $select = $this->_db->select()
                            ->from('tags', array(
                                                        'site_id'=>'sites.SiteID',
                                                        'name'=>'sites.SiteName',
                                                        'type'=>'sites.type',
                                                        'new_burst_tags'=>'sites.new_burst_tags',
                                                        'siteType'=>'tags.type',
                                                        'creative_adexchange'=>'sites.creative_adexchange',
                                                        'baner_320'=>'sites.baner_320',
                            							'kPubID'=>'tags.kPubID',
                            							'kSiteID'=>'tags.kSiteID',
                            							'kadID'=>'tags.kadID',
                            							'kadWidth'=>'tags.kadWidth',
                            							'kadHeight'=>'tags.kadHeight',
                            							'kadType'=>'tags.kadType',
                            							'kadPageUrl'=>'tags.kadPageUrl',
                                                        'PulsePubID'=>'tags.PulsePubID',
                                                        'PulseTagID'=>'tags.PulseTagID'
                                                    ))
                           ->joinLeft('sites', 'tags.site_id=sites.SiteID', array())
                           ->where("tags.user_id = '$userID'")
                           ->where("sites.status = '3'"); 
        $result = $this->getAdapter()->fetchAll($select);
        return $result;                
    }    
    
    public function getSitesInfoById($id)
    {
        $this->_name = "tags";

        $select = $this->_db->select()
                            ->from('tags', array(
                                                        'site_id'=>'sites.SiteID',
                                                        'name'=>'sites.SiteName',
                                                        'type'=>'sites.type',
                                                        'pop_unders'=>'sites.pop_unders',
                                                        'new_burst_tags'=>'sites.new_burst_tags',
                                                        'siteType'=>'tags.type',
                                                        'creative_adexchange'=>'sites.creative_adexchange',
                                                        'baner_320'=>'sites.baner_320',
                            							'kPubID'=>'tags.kPubID',
                            							'kSiteID'=>'tags.kSiteID',
                            							'kadID'=>'tags.kadID',
                            							'kadWidth'=>'tags.kadWidth',
                            							'kadHeight'=>'tags.kadHeight',
                            							'kadType'=>'tags.kadType',
                            							'kadPageUrl'=>'tags.kadPageUrl',
                                                        'PulsePubID'=>'tags.PulsePubID',
                                                        'PulseTagID'=>'tags.PulseTagID'
                                                    ))
                           ->joinLeft('sites', 'tags.site_id=sites.SiteID', array())
                           ->where("sites.SiteID = '$id'")
                           ; 
        $result = $this->getAdapter()->fetchRow($select);

        return $result;                
    }
    
   
    // When user click on Request Size button (AdManager)
    public function setBannerWanted($params,$type = 1)//type = 1 - Admanager, type = 2 - Rubicon
    {
    	$result = array('status' => 1);
    	if(isset($params['banner_params']) AND isset($params['site_id']))
    	{
    		$select_exist = $this->_db->select()
							    		->from('request_banners', array('SiteID'))
							    		->where('SiteID = ?', $params['site_id'])
							    		->limit(1);
    		$exist = $this->_db->fetchAll($select_exist);
    		if($exist)
    		{
    			$this->_db->update('request_banners', array($params['banner_params'] => 1), 'SiteID = '.$params['site_id']);	
    			$result['status'] = 0;
    		}
    	}	
    	return $result;
    }
    
    public function getWantedBanners($site_id)
    {
    	$site_id = intval($site_id);
    	$select_wanted_banners = $this->_db->select()
    									   ->from('request_banners')
    									   ->where('request_banners.SiteID = ?', $site_id)
    									   ->limit(1);
    	$result_wanted_banners = $this->_db->fetchRow($select_wanted_banners);
    	$result = array();
    	if(count($result_wanted_banners))
    	{
    		unset($result_wanted_banners['SiteID']);
    		unset($result_wanted_banners['created']);
    		unset($result_wanted_banners['type']);
    		foreach($result_wanted_banners as $key => $value)
    		{
    			$banner_params = explode('x',$key);
    			$result[$key] = array(
    				'width' => $banner_params[0],
    				'height' => $banner_params[1],
    				'value' => $value
    			);
    		}	
    	}
    	return $result;
    }
    
    public function getRequestedBanners($params = false)
    {
    	$select_new = $this->_db->select()
    						->from('request_banners',array('request_banners.created','request_banners.type','request_banners.SiteID', 'request_banners.300x250', 'request_banners.728x90', 'request_banners.160x600', 'request_banners.468x60', 'request_banners.120x600', 'request_banners.320x50', 'request_banners.300x600', 'request_banners.336x280', 'request_banners.125x125'))
    						->joinLeft('sites', 'sites.SiteID = request_banners.SiteID', array('SiteName', 'alexaRank'))
    						->joinLeft('users', 'sites.PubID = users.id', array('email'))
    						->joinLeft('tags','tags.site_id = sites.SiteID', array('user_id','id as tag_id', 'type as site_type'))
    						->where('300x250 IS NOT NULL')
    						->orWhere('728x90 IS NOT NULL')
    						->orWhere('160x600 IS NOT NULL')
    						->orWhere('468x60 IS NOT NULL')
    						->orWhere('120x600 IS NOT NULL')
    						->orWhere('320x50 IS NOT NULL')
    						->orWhere('300x600 IS NOT NULL')
    						->orWhere('336x280 IS NOT NULL')
    						->orWhere('125x125 IS NOT NULL');
    	
    	$str = $select_new->__toString();
    	
    	$select_old = ' SELECT sites_want_baner.created, 2 AS type, sites.SiteID, 0 AS 300x250, 0 AS 728x90, 0 AS 160x600, 0 AS 468x60, 0 AS 120x600, 1 AS 320x50, 0 AS 300x600, 0 AS 336x280, 0 AS 125x125, sites.SiteName, sites.alexaRank, users.email,   tags.user_id, tags.id as tag_id, tags.type as site_type FROM sites_want_baner
					   LEFT JOIN sites ON sites.SiteID = sites_want_baner.SiteID
    				   LEFT JOIN users ON users.id = sites.PubID
    				   LEFT JOIN tags ON sites.SiteID = tags.site_id
    				';
    	$result_select = $this->_db->select()
    								->union(array($select_new,$select_old),Zend_Db_Select::SQL_UNION_ALL);
    	$count = count($this->_db->fetchAll($result_select));
    	$str = $select_new->__toString();
    	if($params)
    	{
    		switch($params['iSortCol_0'])
    		{
    			case "0":
    				$result_select->order('SiteID '.$params['sSortDir_0']);
    				break;
    			case "1":
    				$result_select->order('SiteName '.$params['sSortDir_0']);
    				break;
    			case "2":
    				$result_select->order('email '.$params['sSortDir_0']);
    				break;
    			case "3":
    				$result_select->order('alexaRank '.$params['sSortDir_0']);
    				break;
    			case "4":
    				$result_select->order('created '.$params['sSortDir_0']);
    				break;
    			case "5":
    				$result_select->order('width '.$params['sSortDir_0']);
    				break;
    			case "6":
    				$result_select->order('type '.$params['sSortDir_0']);
    				break;
    			default:
    				$result_select->order('SiteID '.$params['sSortDir_0']);
    				break;;
    		}
    		if(strlen($params['sSearch']))
    			$result_select->where('SiteName LIKE "%'.$params['sSearch'].'%"');
    		if(strtolower($params['iDisplayLength']) != '-1')
    		{
				$result_select->limit($params['iDisplayLength'],$params['iDisplayStart']);
    		}
    	} 
    	$str = $result_select->__toString();
    	$requested_banners = $this->_db->fetchAll($result_select);
    	$result = array(
    		'count' => $count,
    		'aaData' => array()
    	);
    	foreach($requested_banners as $requested_banner)
    	{
    		$item = array();
    		$item[] = $requested_banner['SiteID'];
    		$item[] = $requested_banner['SiteName'];
    		$item[] = $requested_banner['email'];
    		$item[] = ($requested_banner['alexaRank'] ? $requested_banner['alexaRank']: 0 );
    		$item[] = date('m/d/Y',strtotime($requested_banner['created']));
    		$size = '';
    		if($requested_banner['300x250'])
    			$size .= '300x250 ';
    		if($requested_banner['728x90'])
    			$size .= '728x90 ';
    		if($requested_banner['160x600'])
    			$size .= '160x600 ';
    		if($requested_banner['320x50'])
    			$size .= '320x50 ';
    		if($requested_banner['468x60'])
    			$size .= '468x60 ';
    		if($requested_banner['120x600'])
    			$size .= '120x600 ';
    		if($requested_banner['300x600'])
    			$size .= '300x600 ';
    		if($requested_banner['336x280'])
    			$size .= '336x280 ';
    		if($requested_banner['125x125'])
    			$size .= '125x125 ';
    		$item[] = $size;
    		$item[] = $requested_banner['site_type'];
    		$item[] = $requested_banner['user_id'];
    		$item[] = $requested_banner['tag_id'];
    		$result['aaData'][] = $item;
    	}
    	return $result;
    }
    
    public function createNewBannerRequestField($site_id,$old_site = false)
    {
    	$site_id = intval($site_id);
    	try 
    	{
    		$data_to_db = array(
    			'SiteID' => $site_id,
    			'type' => 1,
    			'created' => date('Y-m-d H:i:s',time())
    		);
    		$this->_db->insert('request_banners', $data_to_db);	
    	}
    	catch(Exception $e)
    	{
    		if(!$old_site)
    		{
	    		$data_to_db = array(
	    			'type' => 1,
	    			'created' => date('Y-m-d H:i:s',time()),
	    			'300x250' => null,
	    			'728x90' => null,
	    			'160x600' => null,
	    			'468x60' => null,
	    			'120x600' => null,
	    			'320x50' => null,
	    			'300x600' => null,
	    			'336x280' => null,
	    			'125x125' => null,
	    		);
	    		$this->_db->update('request_banners', $data_to_db, 'SiteID = '.$site_id);
    		}
    	}
    }


    public function confirmUserSites($siteID, $userID)
    {
        $this->_name = "sites";
        
            $select = $this->_db->select()
                                ->from('sites')
                               ->where("SiteID = '$siteID'")
                               ->where("PubID = '$userID'")
                               ; 
            $result = $this->getAdapter()->fetchRow($select);

            return $result;            
    }
    
    public function addSite($data, $alexaData=null){
        
        $this->_name = "sites";
        
        $result = array(
                        'PubID'=>$data['user'],
                        'SiteName'=>$data['name'],
                        'SiteURL'=>$data['SiteURL'],
                        'category'=>$data['category'] ? $data['category'] : NULL, 
                        'approved'=>date("Y-m-d H:i:s"),
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
                        'status' => $data['status'],
                        'limited_demand_tag' => $data['limited_demand_tag'] ? 1 : NULL ,
                        'creative_passback' => !empty($data['creative_passback']) ? $data['creative_passback'] : NULL,
                        'created' => date('Y-m-d'),
                        'admin_email'=>$data['admin_email'] ? $data['admin_email'] : NULL,
                        'define_url'=>!empty($data['define_url']) ? $data['define_url'] : NULL,
                        'alexaRank'=>$data['alexaRank'] ? $data['alexaRank'] : NULL,
                        'alexaRankUS'=>$data['alexaRankUS'] ? $data['alexaRankUS'] : NULL,
                        'approved_by'=>$data['approved_by'] ? $data['approved_by'] : NULL,
						'desired_types'=>$data['desired_types']
                        );
                
        if($alexaData){
            $alexaArray = array();
            foreach ($alexaData as $key=>$value){
                $alexaArray[$key] = str_replace("<td class='text-right' ><span class=''>", "", str_replace("</span></td>", "", $value));
            }
            $result['alexa_click'] = serialize($alexaArray);
        }
        
        return $this->insert($result);
     }
    
    public function saveSent($siteID, $status){
        
        $this->_name = "sites";
        
        $result = array(
                        'sent_approval'=>$status ? NULL : 1
                        );
                        
        $where = $this->getAdapter()->quoteInto('SiteID  = ?', $siteID);
        return $this->update($result, $where);
    }
    
    public function addSitePricing($PubID, $SiteID, $date, $price, $percent){
    
    	$this->_name = "sites_floor_price";
        $this->_primary = "SiteID";
        
    	$result = array(
    			'PubID'=>$PubID,
    			'SiteID'=>$SiteID,
    			'date'=>$date,
    			'price'=>$price,
    			'percent'=>$percent
    	);
    
    	return $this->insert($result);
    }
    
    public function deleteSitePricing($PubID, $SiteID)
    {
    	$this->_name = 'sites_floor_price';
    	$this->delete(array("PubID = '$PubID'", "SiteID = '$SiteID'"));
    }
    
    public function getInfoForUpdate($where = false)
    {
        $this->_name = 'tags';
        
        $sql = $this->_db->select()
                    ->from(array('t' => 'tags'),
                           array('t.id AS id',
                                 't.user_id AS PubID',
                                 't.site_id AS SiteID'))
                    ->join(array('s' => 'sites'),('s.SiteID = t.site_id'),
                           array('s.pop_unders AS pop_unders',
                                 's.SiteName AS SiteName'))
                    ->order('t.id ASC');
        
        if($where) $sql->where($where);
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getInfoForUpdate2($demandTag = false)
    {
        $this->_name = 'tags';
        
        $demand = $demandTag ? 'IS NOT NULL' : 'IS NULL';

        $sql = $this->_db->select()
                    ->from(array('t' => 'tags'),
                           array('t.id AS id',
                                 't.user_id AS PubID',
                                 't.site_id AS SiteID'))
                    ->join(array('s' => 'sites'),('s.SiteID = t.site_id AND s.floor_pricing = 1 AND s.limited_demand_tag '.$demand),
                           array('s.pop_unders AS pop_unders'))
                    ->order('t.id ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getAllIframe()
    {
        $this->_name = 'tags';
        
        $sql = $this->_db->select()
                    ->from(array('t' => 'tags'),
                           array('t.id AS id',
                                 't.user_id AS PubID',
                                 't.site_id AS SiteID'))
                    ->where('s.iframe_tags = 1')
                    ->join(array('s' => 'sites'),('s.SiteID = t.site_id'),
                           array('s.pop_unders AS pop_unders',
                                 's.SiteName AS SiteName'))
                    ->joinLeft(array('m' => 'minimum_cpm'),('m.SiteID = s.SiteID AND m.status = 3'),
                               array('m.ID as cpmID'))
                    ->order('t.id ASC');
        
        return $this->_db->query($sql)->fetchAll();
    }
           
    
    public function setTagTable()
    {
        $this->_name = 'tags';
    }
    
    public function setRequestIframe($params)
    {
    	$SiteID = (int)$params['site_id'];
    	if($SiteID)
    	{
    		$select_exist = $this->_db->select()
							    		->from('request_iframe', array('SiteID'))
							    		->where('SiteID = ?', $SiteID)
							    		->limit(1);
    		$exist = $this->_db->fetchRow($select_exist);
    		if(!$exist['SiteID'])
    		{
	    		$data_to_db = array(
	    			'SiteID' => $SiteID,
	    			'created' => date('Y-m-d')
	    		);
	    		$this->_db->insert('request_iframe', $data_to_db);	
    		}
    	}	
    	return true;
    }    
    
    public function getWantedIframe($site_id)
    {
    	$site_id = intval($site_id);
    	$select_wanted_banners = $this->_db->select()
    									   ->from('request_iframe', array('SiteID'))
    									   ->where('request_iframe.SiteID = ?', $site_id)
    									   ->limit(1);
    	$result_wanted_banners = $this->_db->fetchRow($select_wanted_banners);
    	$result = array();
    	if($result_wanted_banners['SiteID'])
    	{
    		return true;
    	}
    	return false;
    }    
    
    public function getRequestedIframes()
    {            
            $select = $this->_db->select()
                                ->from('request_iframe', array(
                                                            'count'=>'COUNT(request_iframe.SiteID)'
                                                        )); 
            $result = $this->getAdapter()->fetchRow($select);
            return $result;                
    }    
    
    public function getAllByCreated($date)
    {
        $this->_name = 'tags';
        
        $sql = $this->_db->select()
                    ->from(array('t' => $this->_name), 
                           array(''))
                    ->join(array('u' => 'users'), 'u.id = t.user_id',
                           array('u.email'))
                    ->join(array('s' => 'sites'), 's.SiteID = t.site_id',
                           array('s.SiteID', 's.PubID', 's.SiteName'))
                    ->where('t.created = ?', $date);
        
        return $this->_db->query($sql)->fetchAll();
    }  
    
    public function setRequestNewBurst($params)
    {
    	$SiteID = (int)$params['site_id'];
    	if($SiteID)
    	{
    		$select_exist = $this->_db->select()
							    		->from('sites_want_burst', array('SiteID'))
							    		->where('SiteID = ?', $SiteID)
							    		->limit(1);
    		$exist = $this->_db->fetchRow($select_exist);
    		if(!$exist['SiteID'])
    		{
	    		$data_to_db = array(
	    			'SiteID' => $SiteID,
	    			'created' => date('Y-m-d H:i:s')
	    		);
	    		$this->_db->insert('sites_want_burst', $data_to_db);	
    		}
    	}	
    	return true;
    }    

    public function getWantedNewBurst($site_id)
    {
    	$site_id = intval($site_id);
    	$select_wanted_banners = $this->_db->select()
    									   ->from('sites_want_burst', array('SiteID'))
    									   ->where('sites_want_burst.SiteID = ?', $site_id)
    									   ->limit(1);
    	$result_wanted_banners = $this->_db->fetchRow($select_wanted_banners);
    	$result = array();
    	if($result_wanted_banners['SiteID'])
    	{
    		return true;
    	}
    	return false;
    }    
              
}