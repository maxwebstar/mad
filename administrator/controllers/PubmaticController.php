<?php
/**
 * Description of PubmaticController
 *
 * @author nik
 */
class Administrator_PubmaticController extends Zend_Controller_Action 
{

    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
    }
    
    public function indexAction()
    {
    	
    }    
                    
    public function getSiteDataAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering     
		
		$data = array();
		$id = (int)$this->_getParam('id');
		$pubmaticID = (int)$this->_getParam('pubmaticID');
		
        $pubmaticSitesModel = new Application_Model_DbTable_PubmaticSites();
		$sql = $pubmaticSitesModel->select()->setIntegrityCheck(false)
							->from(array('ps' => 'pubmatic_sites'), array(
								'ps.PubID AS PubID',
								'ps.PubmaticID AS PubmaticID',
								'ps.SiteID AS SiteID',
								'ps.PubmaticSiteID AS PubmaticSiteID'
							))
	                        ->joinLeft(array('s' => 'sites'),('s.SiteID = ps.SiteID'),
	                               array('s.SiteName As SiteName'))							
							->where("ps.SiteID = ?", $id)
							->where("ps.PubmaticID = ?", $pubmaticID);                    
		$dataSite = $pubmaticSitesModel->fetchRow($sql);
		if($dataSite['PubmaticSiteID']){
			$data['status'] = 'OK';
			$data['message'] = 'The site '.$dataSite['SiteName'].' have already been added to the API... OK';
			$data['PubID'] = $dataSite['PubID'];
			$data['PubmaticID'] = $dataSite['PubmaticID'];
			$data['SiteID'] = $dataSite['SiteID'];
			$data['PubmaticSiteID'] = $dataSite['PubmaticSiteID'];
			$data['SiteName'] = $dataSite['SiteName'];			
		}else{
			$sitesModel = new Application_Model_DbTable_Sites();
	        $sql = $sitesModel->select()->setIntegrityCheck(false)
	                            ->from(array('s' => 'sites'),array(
		                            's.SiteID AS SiteID',
		                            's.PubID AS PubID',
	                                's.define_url AS define_url',
	                                's.SiteName AS SiteName'))
	                            ->where("s.SiteID = ?", $id);                          
			$data = $sitesModel->fetchRow($sql)->toArray();
			if($data['SiteID']==$id){
				$data['status']='OK';
				$data['message']='';
			}else
				$data['error']='Site not found';			
		}
		    
        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);	    
    } 
                
    public function addSiteAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $id = (int)$this->_getParam('id');
        $new_burst = (int)$this->_getParam('new_burst');

        $this->view->id = $id;
        $this->view->new_burst = $new_burst;
    }
    
    public function addPubSiteAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering     
		
        $form = new Application_Form_PubmaticSite();
        if($this->getRequest()->isPost()){
        	$formData = $this->getRequest()->getPost();
        	$formData['SiteID'] = (int)$formData['SiteID'];
        	$formData['PubID'] = (int)$formData['PubID'];        	
			if($form->isValid($formData) && $formData['SiteID']){
	            $pubmaticSitesModel = new Application_Model_DbTable_PubmaticSites();
				$sql = $pubmaticSitesModel->select()->setIntegrityCheck(false)
									->from(array('ps' => 'pubmatic_sites'), array(
										'ps.PubID AS PubID',
										'ps.PubmaticID AS PubmaticID',
										'ps.SiteID AS SiteID',
										'ps.PubmaticSiteID AS PubmaticSiteID'
									))
									->where("ps.PubID = ?", $formData['PubID'])
									->where("ps.SiteID = ?", $formData['SiteID'])
									->where("ps.PubmaticID = ?", $form->getValue('publisherId'));                    
				$dataSite = $pubmaticSitesModel->fetchRow($sql);
	            if(!$dataSite['PubmaticSiteID']){				
					$pubmatic = new My_Pubmaticv2();
					$siteData = $pubmatic->createPublisherSite($form->getValues());
					if($siteData->siteId){
						$data['status']='OK';	
						$data['siteId']=$siteData->siteId;
						$data['publisherId']=$siteData->publisherId;

		                $dataPubmaticSites = $pubmaticSitesModel->createRow();	              
	                    $dataPubmaticSites->setFromArray(array(
	                        'PubID' => $formData['PubID'],
	                        'PubmaticID' => $siteData->publisherId,
	                        'SiteID' => $formData['SiteID'],
	                        'PubmaticSiteID' => $siteData->siteId,
	                        'domainName'=> $siteData->siteDomain->domainName,
	                        'siteURL'=> $siteData->siteUrl,
	                        'verticalId'=> $siteData->verticalId,
	                        'microVerticalId'=> $siteData->microVerticalId,
	                        'monthlyImpressions'=> $siteData->monthlyImpressions,
	                        'platform'=> $siteData->platform,
	                        'privacyPolicyUrl'=> $siteData->privacyPolicyUrl,
	                        'created' => date("Y-m-d H:i:s"),
	                        'updated' => date("Y-m-d H:i:s")
	                    ));
		                $dataPubmaticSites->save();

					}elseif($siteData[0]->error){
						$data['error']=$siteData[0]->error;
					}elseif($siteData[0]->errorMessage){
						$data['error']=$siteData[0]->errorMessage;
					}else
						$data['error']='API ERROR';
				}else{
					$data['status']='OK';	
					$data['siteId']=$dataSite['PubmaticSiteID'];
					$data['publisherId']=$dataSite['PubmaticID'];	
					$data['response']='';						
				}
			}else{
				$data['error']='All fields are required';
				$data['errorsData'] = $form->getMessages();
			}	
		}        
        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);	    
    }  
        
    public function createTagSiteAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering     
		$formData = $this->getRequest()->getPost();

		$sizes = explode('x', $formData['size']);
		
		$sizeName = $formData['size'];
		$publisherId = (int)$formData['publisherId'];		
		
		$adSizeIdArr = array(
			'728x90'=>7,
			'300x250'=>9,
			'160x600'=>10,
			'336x280'=>1
		);
		
		$adNetwIdArr = array(
			'49542'=>1067,
			'63240'=>165,
			'68582'=>165,
			'93536'=>165
		);

		$adPlacemIdArr = array(
			'728x90'=>array(
				'49542'=>161158,
				'63240'=>162426,
				'68582'=>161553,
				'93536'=>253633
			),
			'300x250'=>array(
				'49542'=>161159,
				'63240'=>162427,				
				'68582'=>161554,
				'93536'=>253634
			),
			'160x600'=>array(
				'49542'=>161160,
				'63240'=>162428,								
				'68582'=>161552,
				'93536'=>253635
			),
			'336x280'=>array(
				'49542'=>195107,
				'63240'=>195108,								
				'68582'=>163686,
				'93536'=>253636
			)
		);
		
        $pubmaticTagsModel = new Application_Model_DbTable_PubmaticTags();
		$sql = $pubmaticTagsModel->select()->setIntegrityCheck(false)
							->from(array('pt' => 'pubmatic_tags'), array(
								'pt.adTagId AS adTagId'
							))
							->where("pt.PubmaticID = ?", (int)$formData['publisherId'])
							->where("pt.PubmaticSiteID = ?", (int)$formData['siteId'])
							->where("pt.width = ?", $sizes[0])
							->where("pt.height = ?", $sizes[1]);                    
		$dataTags = $pubmaticTagsModel->fetchRow($sql);
		
		if($dataTags['adTagId']){
			$data['status'] = 'OK';	
			$data['name'] = $sizeName;
			$data['adTagId'] = $dataTags['adTagId'];	
			$data['response']='';
			$data['message'] = 'The tag '.$formData['siteName'].'_'.$formData['size'].' have already been added to the API... OK';
		}else{
			$dataRequest = array();
			$dataRequest['accessToken']	= '';
			$dataRequest['publisherId']	= $formData['publisherId'];
			$dataRequest['siteId']		= $formData['siteId'];
			$dataRequest['adTag']		= array(
											'adCodeType'=>'JAVASCRIPT',
											'adSize'=>array(
												'adSizeId'=>$adSizeIdArr[$sizeName],
												'width'=>$sizes[0],
												'height'=>$sizes[1]
											),
											'foldPlacement'=>'ABOVE',
											'name'=>$formData['siteName'].'_'.$formData['size'],
											'pagePlacement'=>'TOP',
											'platform'=>'WEB',
											'adType'=>'TEXT_IMAGE_FLASH_HTML'
										);
			$dataRequest['adTagPlacement']= array(
											'adNetworkId'=>$adNetwIdArr[$publisherId],
											'placementId'=>$adPlacemIdArr[$sizeName][$publisherId]
										);
			
			$pubmatic = new My_Pubmatic();
			$pubmatic->_getAccessToken();
			$tagsData = $pubmatic->createAdTagForSite($dataRequest);						
			
			if($tagsData['adTagId']){			
				$data['status'] = 'OK';	
				$data['name'] = $sizeName;
				$data['adTagId'] = $tagsData['adTagId'];	
				$data['response']=$tagsData['response'];	

                $dataPubmaticTag = $pubmaticTagsModel->createRow();	              
                $dataPubmaticTag->setFromArray(array(
                    'PubID' => (int)$formData['madPubID'],
                    'SiteID' => (int)$formData['madSiteID'],                        
                    'PubmaticID' => (int)$formData['publisherId'],
                    'PubmaticSiteID' => (int)$formData['siteId'],                                                
                    'adTagId' => (int)$tagsData['adTagId'],
                    'width' => $sizes[0],
                    'height' => $sizes[1],
                    'name' => $formData['siteName'].'_'.$formData['size'],
                    'created' => date("Y-m-d H:i:s")
                ));                	                
                $dataPubmaticTag->save();			                
			}elseif($tagsData['error']){
				$data['error']=$tagsData['error'];
				$data['response']=$tagsData['response'];
				$data['request']=$tagsData['request'];			
			}else{
				$data['error']='API ERROR';
				$data['response']=$tagsData['response'];			
			}	    			
		}
		
        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);	    
	    
    }
    
    public function generateTagSiteAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering   
        $formData = $this->getRequest()->getPost();
        
        $pubmaticTagsModel = new Application_Model_DbTable_PubmaticTags();
		$sql = $pubmaticTagsModel->select()->setIntegrityCheck(false)
							->from(array('pt' => 'pubmatic_tags'), array(
								'pt.PubID AS PubID',
								'pt.SiteID AS SiteID',
								'pt.PubmaticID AS PubmaticID',
								'pt.PubmaticSiteID AS PubmaticSiteID',
								'pt.adTagId AS adTagId',
								'pt.width AS width',								
								'pt.height AS height'			
							))
	                        ->joinLeft(array('s' => 'sites'),('s.SiteID = pt.SiteID'),
	                               array('s.define_url As define_url',
	                               		's.SiteName AS SiteName',
	                               		's.new_burst_tags AS new_burst_tags'))							
	                        ->joinLeft(array('u' => 'users'),('s.PubID = u.id'),
	                               array('u.account_manager_id As account_manager_id',
	                               		'u.name AS name',
	                               		'u.email'))
							->where("pt.PubmaticID = ?", (int)$formData['publisherId'])
							->where("pt.PubmaticSiteID = ?", (int)$formData['siteId']);                    
		$dataTags = $pubmaticTagsModel->fetchAll($sql);          
		
		if($dataTags[0]['adTagId']){
            $siteTagsModel = new Application_Model_DbTable_SitesTags();
            $tagsPropModel = new Application_Model_DbTable_TagsProperties();

			foreach($dataTags as $tag){

                $sql = $siteTagsModel->select()->setIntegrityCheck(false)
                    ->from(['st' => 'sites_tags'], [
                        'st.id AS id',
                        'st.size_id AS size_id',
                        'st.primary AS primary',
                        'st.network_id AS network_id'
                    ])
                    ->joinLeft("display_size AS ds", "ds.id=st.size_id", [])
                    ->where("st.site_id = ?", $tag['SiteID'])
                    ->where("st.pub_id = ?", $tag['PubID'])
                    ->where("ds.width = ?", $tag['width'])
                    ->where("ds.height = ?", $tag['height']);
                $dataAdTags = $siteTagsModel->fetchAll($sql);

                $sizeModel = new Application_Model_DbTable_Sizes();
                $dataSize = $sizeModel->fetchRow([
                    $siteTagsModel->getAdapter()->quoteInto('width = ?', $tag['width']),
                    $siteTagsModel->getAdapter()->quoteInto('height = ?', $tag['height'])
                ]);

                if($dataAdTags->count()){
                    foreach($dataAdTags as $row){
                        if($row->network_id==8){
                            $tagsPropModel->update(
                                [
                                    'value'=>$tag['PubmaticID'],
                                    'updated_at'=>date("Y-m-d H:i:s")
                                ],
                                [
                                    $tagsPropModel->getAdapter()->quoteInto('name = ?', 'kPubID'),
                                    $tagsPropModel->getAdapter()->quoteInto('tag_id = ?', $row->id),
                                ]);

                            $tagsPropModel->update(
                                [
                                    'value'=>$tag['PubmaticSiteID'],
                                    'updated_at'=>date("Y-m-d H:i:s")
                                ],
                                [
                                    $tagsPropModel->getAdapter()->quoteInto('name = ?', 'kSiteID'),
                                    $tagsPropModel->getAdapter()->quoteInto('tag_id = ?', $row->id),
                                ]);

                            $tagsPropModel->update(
                                [
                                    'value'=>$tag['adTagId'],
                                    'updated_at'=>date("Y-m-d H:i:s")
                                ],
                                [
                                    $tagsPropModel->getAdapter()->quoteInto('name = ?', 'kadID'),
                                    $tagsPropModel->getAdapter()->quoteInto('tag_id = ?', $row->id),
                                ]);
                        }else{
                            $dataSitesTags = $siteTagsModel->createRow();
                            $dataSitesTags->setFromArray([
                                'pub_id'=>$tag['PubID'],
                                'site_id'=>$tag['SiteID'],
                                'network_id'=>8,
                                'size_id'=>$dataSize->id,
                                'primary'=>0,
                                'env'=>APPLICATION_ENV,
                                'created_at'=>date("Y-m-d H:i:s")
                            ]);
                            $dataSitesTags->save();

                            $dataProp = $tagsPropModel->createRow();
                            $dataProp->setFromArray([
                                'tag_id'=>$dataSitesTags->id,
                                'name'=>'kPubID',
                                'value'=>$tag['PubmaticID'],
                                'created_at'=>date("Y-m-d H:i:s")
                            ]);
                            $dataProp->save();

                            $dataProp = $tagsPropModel->createRow();
                            $dataProp->setFromArray([
                                'tag_id'=>$dataSitesTags->id,
                                'name'=>'kSiteID',
                                'value'=>$tag['PubmaticSiteID'],
                                'created_at'=>date("Y-m-d H:i:s")
                            ]);
                            $dataProp->save();

                            $dataProp = $tagsPropModel->createRow();
                            $dataProp->setFromArray([
                                'tag_id'=>$dataSitesTags->id,
                                'name'=>'kadID',
                                'value'=>$tag['adTagId'],
                                'created_at'=>date("Y-m-d H:i:s")
                            ]);
                            $dataProp->save();

                            $dataProp = $tagsPropModel->createRow();
                            $dataProp->setFromArray([
                                'tag_id'=>$dataSitesTags->id,
                                'name'=>'kadWidth',
                                'value'=>$tag['width'],
                                'created_at'=>date("Y-m-d H:i:s")
                            ]);
                            $dataProp->save();

                            $dataProp = $tagsPropModel->createRow();
                            $dataProp->setFromArray([
                                'tag_id'=>$dataSitesTags->id,
                                'name'=>'kadHeight',
                                'value'=>$tag['height'],
                                'created_at'=>date("Y-m-d H:i:s")
                            ]);
                            $dataProp->save();

                            $dataProp = $tagsPropModel->createRow();
                            $dataProp->setFromArray([
                                'tag_id'=>$dataSitesTags->id,
                                'name'=>'kadType',
                                'value'=>1,
                                'created_at'=>date("Y-m-d H:i:s")
                            ]);
                            $dataProp->save();

                            $dataProp = $tagsPropModel->createRow();
                            $dataProp->setFromArray([
                                'tag_id'=>$dataSitesTags->id,
                                'name'=>'kadPageUrl',
                                'value'=>'http://'.$tag['define_url'],
                                'created_at'=>date("Y-m-d H:i:s")
                            ]);
                            $dataProp->save();
                        }
                    }
                    $siteTagsModel->changeAction($tag['SiteID'], 'gen', APPLICATION_ENV);
                }else{

                    $dataSitesTags = $siteTagsModel->createRow();
                    $dataSitesTags->setFromArray([
                        'pub_id'=>$tag['PubID'],
                        'site_id'=>$tag['SiteID'],
                        'network_id'=>8,
                        'size_id'=>$dataSize->id,
                        'primary'=>1,
                        'action'=>'gen',
                        'env'=>APPLICATION_ENV,
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataSitesTags->save();

                    $dataProp = $tagsPropModel->createRow();
                    $dataProp->setFromArray([
                        'tag_id'=>$dataSitesTags->id,
                        'name'=>'kPubID',
                        'value'=>$tag['PubmaticID'],
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataProp->save();

                    $dataProp = $tagsPropModel->createRow();
                    $dataProp->setFromArray([
                        'tag_id'=>$dataSitesTags->id,
                        'name'=>'kSiteID',
                        'value'=>$tag['PubmaticSiteID'],
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataProp->save();

                    $dataProp = $tagsPropModel->createRow();
                    $dataProp->setFromArray([
                        'tag_id'=>$dataSitesTags->id,
                        'name'=>'kadID',
                        'value'=>$tag['adTagId'],
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataProp->save();

                    $dataProp = $tagsPropModel->createRow();
                    $dataProp->setFromArray([
                        'tag_id'=>$dataSitesTags->id,
                        'name'=>'kadWidth',
                        'value'=>$tag['width'],
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataProp->save();

                    $dataProp = $tagsPropModel->createRow();
                    $dataProp->setFromArray([
                        'tag_id'=>$dataSitesTags->id,
                        'name'=>'kadHeight',
                        'value'=>$tag['height'],
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataProp->save();

                    $dataProp = $tagsPropModel->createRow();
                    $dataProp->setFromArray([
                        'tag_id'=>$dataSitesTags->id,
                        'name'=>'kadType',
                        'value'=>1,
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataProp->save();

                    $dataProp = $tagsPropModel->createRow();
                    $dataProp->setFromArray([
                        'tag_id'=>$dataSitesTags->id,
                        'name'=>'kadPageUrl',
                        'value'=>'http://'.$tag['define_url'],
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataProp->save();
                }

            }

			if($formData['new_burst']==1 && !$dataTags[0]['new_burst_tags']){

				$sitesModel = new Application_Model_DbTable_Sites();
				$sitesModel->update(array('new_burst_tags' => 1), "SiteID=".$dataTags[0]['SiteID']);
				
				$burst_model = new Application_Model_DbTable_WantBurst();
	            $burst_model->update(array('status' => 3), "SiteID=".$dataTags[0]['SiteID']);
				
		    	$tableMail = new Application_Model_DbTable_Mail();
		    	$tableManager = new Application_Model_DbTable_ContactNotification();	
		    	$tableStatic = new Application_Model_DbTable_StaticPage();			
		    	
		    	$dataManager = $tableManager->getDataByID($dataTags[0]['account_manager_id']);
				
				if($dataManager['id']){
					
					$dataAuth = Zend_Auth::getInstance()->getIdentity();
					
					$dataSignature = $tableStatic->getDataByName('signature');
					
					$dataSignature['content'] = str_replace('{ADMIN_NAME_HERE}', $dataManager['name'], $dataSignature['content']);
			
			    	$dataSignature['content'] = str_replace('{ADMIN_EMAIL_HERE}', $dataManager['mail'], $dataSignature['content']);		
			    	
			    	$text = "<p>Hello,</p><p>I just wanted to let you know that your website was approved for our new platform and your ad tags are ready to be implemented. Should you have any questions, please let me know.</p><p>Regards,</p>";
			    	$subject = 'Your Ad Tags for '.$dataTags[0]['SiteName'].' are Upgraded!';
			    	
		    		$classMail = new Zend_Mail();
		    		$classMail->setFrom($dataManager['mail'], $dataManager['name']);
		    		$classMail->addTo($dataTags[0]['email'], $dataTags[0]['name']);
		    		$classMail->setSubject($subject);
		    		$classMail->setBodyHtml($text.$dataSignature['content']);
		    		
                    $dataTags = $siteTagsModel->getSiteTags($dataTags[0]['SiteID']);
                    $modefiSiteName = str_replace(".", "_", $dataTags[0]['SiteName']);
	                foreach($dataTags as $key => $size){
                            $txt = '<!-- MadAdsMedia.com Asynchronous Ad Tag For '.$dataTags[0]['SiteName'].' -->
                        <!-- Size: '.$tag['name'].' -->
                        <script src="http://ads-by.madadsmedia.com/tags/'.$dataTags[0]['PubID'].'/'.$dataTags[0]['SiteID'].'/async/'.$tag['file_name'].'.js" type="text/javascript"></script>
                        <!-- MadAdsMedia.com Asynchronous Ad Tag For '.$dataTags[0]['SiteName'].' -->';

                        $atachment[$tag['file_name']] = $classMail->createAttachment($txt, Zend_Mime::ENCODING_BASE64, Zend_Mime::TYPE_TEXT);
                        $atachment[$tag['file_name']]->filename = $modefiSiteName.'-'.$tag['file_name'].'.txt';
	                }
		    		
		    		$classMail->send();
		    
		    		$dataInsert = array('PubID'   => $dataTags[0]['PubID'],
		    				'subject' => $subject,
		    				'text'    => $text.$dataSignature['content'],
		    				'author'  => $dataAuth->email,
		    				'account_manager' => $dataManager['id'],
		    				'created' => date('Y-m-d H:i:s'));
		    
		    		$tableMail->insert($dataInsert);
			    				
				}		    	
			}
			
			$data['status'] = 'OK';
		}else{
			$data['error'] = 'adTagId not found';
		}
	    
        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);	    	    
    }
    
    public function updateAdTagPlacementAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering   
        $formData = $this->getRequest()->getPost();

		$pubmatic = new My_Pubmatic();
		$pubmatic->_getAccessToken();
	    
		$dataRequest = array();
		$dataRequest['accessToken']	= '';
		$dataRequest['publisherId']	= (int)$formData['publisherId'];
		$dataRequest['siteId']		= (int)$formData['siteId'];
		$dataRequest['adTagPlacement']= array(
										'adTagId'=>(int)$formData['adTagId'],
										'adNetworkId'=>(int)$formData['adNetworkId'],
										'adTagPlacementId'=>(int)$formData['adTagPlacementId'],
										'placementId'=>(int)$formData['placementId']
									);
		
		$placementData = $pubmatic->updateAdTagPlacement($dataRequest);		
		
		if($placementData['adTagId']){			
			$data['status'] = 'OK';	
			$data['response']=$placementData['response'];	
		}elseif($placementData['error']){
			$data['error']=$placementData['error'];
			$data['response']=$placementData['response'];
			$data['request']=$placementData['request'];			
		}else{
			$data['error']='API ERROR';
			$data['response']=$placementData['response'];			
		}	   
        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);		 					
    }
    
    public function updatePlacementAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'updatePlacement';
	    
    }
    
    public function getAjaxTagAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $output = array();
        $sitesModel = new Application_Model_DbTable_PubmaticTags();

        $search = $this->_getParam('term');

        $sql = $sitesModel->select()->setIntegrityCheck(false)
                            ->from(array('pt' => 'pubmatic_tags'),array(
                                'pt.PubmaticID AS PubmaticID',
                                'pt.PubmaticSiteID AS PubmaticSiteID',
                                'pt.adTagId AS adTagId',
                                'pt.name AS name',
                                'pt.width AS width',
                                'pt.height AS height'))
                            ->where("pt.SiteID!=0")
                            ->where("pt.SiteID = '".$search."' OR pt.PubmaticSiteID = '".$search."' OR pt.adTagId = '".$search."' OR pt.name LIKE '%".$search."%'");

        $data = $sitesModel->fetchAll($sql);
        if($data){
            foreach ($data as $value) {
                $output[] = array(
                    'label'=>$value->name,
                    'PubmaticID'=>$value->PubmaticID,
                    'PubmaticSiteID'=>$value->PubmaticSiteID,
                    'adTagId'=>$value->adTagId,
                    'name'=>$value->name,
                    'size'=>$value->width.'x'.$value->height);
            }
        }

        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    
    public function getTagPlacementIdAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering     
		$formData = $this->getRequest()->getPost();
			
		$publisherId = 	(int)$formData['publisherId'];
        $type = $formData['type']=='mobile' ? 'mobile' : 'desktop';

        $adNetwIdArr = [
            'mobile' =>[
                '49542'=>2161,
                '63240'=>1219,
                '68582'=>1219,
                '93536'=>'',
            ],
            'desktop' =>[
                '49542'=>1067,
                '63240'=>165,
                '68582'=>165,
                '93536'=>165,
            ]
        ];

		$dataRequest = array();
		$dataRequest['accessToken']	= '';
		$dataRequest['publisherId']	= (int)$formData['publisherId'];
		$dataRequest['siteId']		= (int)$formData['siteId'];
		$dataRequest['adTagId']		= (int)$formData['adTagId'];
		
		$pubmatic = new My_Pubmatic();
		$pubmatic->_getAccessToken();
		$tagsData = $pubmatic->getAdTagPlacements($dataRequest);
		
		if($tagsData['adTagPlacementId']){			
			$data['status'] = 'OK';	
			$data['adTagPlacementId'] = $tagsData['adTagPlacementId'];	
			$data['adNetwId'] = $adNetwIdArr[$type][$publisherId];
			$data['response']=$tagsData['response'];	
		}elseif($tagsData['error']){
			$data['error']=$tagsData['error'];
			$data['response']=$tagsData['response'];
			$data['request']=$tagsData['request'];			
		}else{
			$data['error']='API ERROR';
			$data['response']=$tagsData['response'];			
		}	    			
					
        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);	    	    
    }    
    
    public function getNetworkPlacementsAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering     
		$formData = $this->getRequest()->getPost();
					
		$dataRequest = array();
		$dataRequest['accessToken']	= '';
		$dataRequest['publisherId']	= (int)$formData['publisherId'];
		$dataRequest['adNetworkId']		= (int)$formData['adNetwId'];
		
		$pubmatic = new My_Pubmatic();
		$pubmatic->_getAccessToken();
		$tagsData = $pubmatic->getAdNetworkPlacements($dataRequest);						
		
		if($tagsData['error']){
			$data['error']=$tagsData['error'];
			$data['response']=$tagsData['response'];
			$data['request']=$tagsData['request'];						
		}elseif($tagsData[0])
		
		if($tagsData[0]->placementId){			
			$data['status'] = 'OK';	
			$data['placements'];
			foreach($tagsData as $tags){
				if(strpos($tags->name, $formData['size'])){
					$data['placements'][] = array('name'=>(string)$tags->name, 'id'=>(int)$tags->placementId);
				}
			}
		}elseif($tagsData['error']){
			$data['error']=$tagsData['error'];
			$data['response']=$tagsData['response'];
			$data['request']=$tagsData['request'];			
		}else{
			$data['error']='API ERROR';
			$data['response']=$tagsData['response'];			
		}	    			
					
        $this->getResponse()->setBody(Zend_Json::encode($data))->setHeader('content-type', 'application/json', true);	    	    
    }        
}