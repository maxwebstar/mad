<?php
class Administrator_TagsController extends Zend_Controller_Action
{
    
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin_v2');
    }
    
    public function indexAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'tags';  
        
        $auth = Zend_Auth::getInstance()->getIdentity();

        $tableManager = new Application_Model_DbTable_ContactNotification();
        $networksModel = new Application_Model_DbTable_Networks();
        
        $dataManager = $tableManager->fetchRow("mail='".$auth->email."'");

        $this->view->manager = $dataManager;        
        $this->view->contactManager = $tableManager->getActiveContact();
        $this->view->dataAuth = $auth;
        $this->view->dataNetworks = $networksModel->getActiveNetworks();
    }

    public function addAction()
    {
        $site_id = (int)$this->_getParam('site');

        if($site_id){
            $siteTagsModel = new Application_Model_DbTable_SitesTags();

            if($siteTagsModel->fetchRow($siteTagsModel->getAdapter()->quoteInto('site_id = ?', $site_id)))
                $this->_redirect('/administrator/tags');

            Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/administrator/controllers/helper', 'Helper');

            $sitesModel = new Application_Model_DbTable_Sites();
            $networksModel = new Application_Model_DbTable_Networks();
            $sizesModel = new Application_Model_DbTable_Sizes();
            $form = new Application_Form_Tags();

            $dataNetworks = $networksModel->getActiveNetworks();
            $dataSite = $sitesModel->fetchRow($sitesModel->getAdapter()->quoteInto('SiteID = ?', $site_id));
            $dataNetworksSizes = $sizesModel->getSizesNetworks();

            if($this->getRequest()->isPost()){
                $dataPost = array_filter($this->getRequest()->getPost());

                //remove fields for not selected networks
                if($dataPost['networks']){
                    $form = $this->_helper->TagValidators->setValidators($form, $dataPost);
                }

                if($form->isValid($dataPost)){
                    $tagsPropModel = new Application_Model_DbTable_TagsProperties();
                    $pabmTagsModel = new Application_Model_DbTable_PubmaticTags();
                    $pubmGetTagsModel = new Application_Model_DbTable_PubmaticGetTags();

                    foreach($form->getValue('networks') as $network){
                        if($form->getDisplayGroup($network)){
                            if($form->getDisplayGroup($network)->getElement('sizes_'.$network)->getValue()){
                                foreach($form->getDisplayGroup($network)->getElement('sizes_'.$network)->getValue() as $size_id=>$value){
                                    $kPubID = null;
                                    $kSiteID = null;
                                    $kadID = null;
                                    $dataSitesTags = $siteTagsModel->createRow();
                                    $dataSitesTags->setFromArray([
                                        'pub_id'=>$dataSite['PubID'],
                                        'site_id'=>$dataSite['SiteID'],
                                        'network_id'=>$network,
                                        'size_id'=>$size_id,
                                        'primary'=>$network==$form->getValue('activeNetwork') ? 1 : 0,
                                        'action'=>$network==$form->getValue('activeNetwork') ? 'gen' : NULL,
                                        'env'=>APPLICATION_ENV,
                                        'created_at'=>date("Y-m-d H:i:s")
                                    ]);
                                    $dataSitesTags->save();
                                    foreach($form->getDisplayGroup($network)->getElements() as $element){

                                        if($element->getName()!='sizes_'.$network){
                                            if($element->getName()=='kPubID')
                                                $kPubID = $element->getValue($element->getName())[$size_id];

                                            if($element->getName()=='kSiteID')
                                                $kSiteID = $element->getValue($element->getName())[$size_id];

                                            if($element->getName()=='kadID'){
                                                $kadID = $element->getValue($element->getName())[$size_id];
                                            }
                                        }

                                        if($element->getName()!='sizes_'.$network && !empty($element->getValue($element->getName())[$size_id])){

                                            $dataProp = $tagsPropModel->createRow();
                                            $dataProp->setFromArray([
                                                'tag_id'=>$dataSitesTags->id,
                                                'network_id'=>$network,
                                                'name'=>$element->getName(),
                                                'value'=>$element->getValue($element->getName())[$size_id],
                                                'created_at'=>date("Y-m-d H:i:s")
                                            ]);
                                            $dataProp->save();
                                        }
                                    }

                                    if($kPubID && $kSiteID && $kadID){
                                        $dataPubmTags = $pabmTagsModel->fetchAll([
                                            $pabmTagsModel->getAdapter()->quoteInto('PubmaticID = ?', $kPubID),
                                            $pabmTagsModel->getAdapter()->quoteInto('PubmaticSiteID = ?', $kSiteID),
                                            $pabmTagsModel->getAdapter()->quoteInto('adTagId = ?', $kadID)
                                        ]);
                                        if(!$dataPubmTags->count()){
                                            $dataGetTags = $pubmGetTagsModel->createRow();
                                            $dataGetTags->setFromArray([
                                                'PubmaticID'=>$kPubID,
                                                'PubmaticSiteID'=>$kSiteID
                                            ]);
                                            $dataGetTags->save();
                                        }
                                    }
                                }
                            }else{
                                $dataSitesTags = $siteTagsModel->createRow();
                                $dataSitesTags->setFromArray([
                                    'pub_id'=>$dataSite['PubID'],
                                    'site_id'=>$dataSite['SiteID'],
                                    'network_id'=>$network,
                                    'size_id'=>NULL,
                                    'primary'=>0,
                                    'env'=>APPLICATION_ENV,
                                    'created_at'=>date("Y-m-d H:i:s")
                                ]);
                                $dataSitesTags->save();
                            }
                        }
                    }
                    $this->_redirect('/administrator/tags');
                }else{ 
                    $this->view->formErrors = $form->getMessages();
                    $this->view->formValues = $form->getValues();
                }
            }

            $this->view->dataNetworksSizes = $dataNetworksSizes;
            $this->view->dataSite = $dataSite;
            $this->view->dataNetworks = $dataNetworks;
        }else{
            $this->_redirect('/administrator/tags');
        }
    }
    
    public function editAction()
    {
        $site_id = (int)$this->_getParam('site');

        if($site_id){
            $siteTagsModel = new Application_Model_DbTable_SitesTags();
            $dataTags = $siteTagsModel->fetchAll([
                $siteTagsModel->getAdapter()->quoteInto('site_id = ?', $site_id),
                "(`action`<>'remove' OR `action` IS NULL)"
            ]);

            if(!$dataTags->count())
                $this->_redirect('/administrator/tags');

            Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/administrator/controllers/helper', 'Helper');

            $sitesModel = new Application_Model_DbTable_Sites();
            $networksModel = new Application_Model_DbTable_Networks();
            $sizesModel = new Application_Model_DbTable_Sizes();
            $tagsPropModel = new Application_Model_DbTable_TagsProperties();
            $form = new Application_Form_Tags();

            $dataNetworks = $networksModel->getActiveNetworks();
            $dataSite = $sitesModel->fetchRow($sitesModel->getAdapter()->quoteInto('SiteID = ?', $site_id));
            $dataNetworksSizes = $sizesModel->getSizesNetworks();

            if($this->getRequest()->isPost()){
                $dataPost = array_filter($this->getRequest()->getPost());

                //remove fields for not selected networks
                if($dataPost['networks']){
                    $form = $this->_helper->TagValidators->setValidators($form, $dataPost);
                }

                if($form->isValid($dataPost)){
                    $null = new Zend_Db_Expr("NULL");

                    $pabmTagsModel = new Application_Model_DbTable_PubmaticTags();
                    $pubmGetTagsModel = new Application_Model_DbTable_PubmaticGetTags();

                    $siteTagsModel->update(['updated_at'=>$null], $siteTagsModel->getAdapter()->quoteInto('site_id = ?', $site_id));

                    foreach($form->getValue('networks') as $network){
                        if($form->getDisplayGroup($network)){
                            if($form->getDisplayGroup($network)->getElement('sizes_'.$network)->getValue()){
                                foreach($form->getDisplayGroup($network)->getElement('sizes_'.$network)->getValue() as $size_id=>$value){

                                    $kPubID = null;
                                    $kSiteID = null;
                                    $kadID = null;

                                    $dataSitesTags = $siteTagsModel->fetchRow([
                                        $siteTagsModel->getAdapter()->quoteInto('site_id = ?', $dataSite['SiteID']),
                                        $siteTagsModel->getAdapter()->quoteInto('network_id = ?', $network),
                                        $siteTagsModel->getAdapter()->quoteInto('size_id = ?', $size_id)
                                    ]);
                                    if($dataSitesTags){
                                        $dataSitesTags->primary = $network==$form->getValue('activeNetwork') ? 1 : 0;
                                        $dataSitesTags->action = 'gen';
                                        $dataSitesTags->env = APPLICATION_ENV;
                                        $dataSitesTags->updated_at = date("Y-m-d H:i:s");
                                        $dataSitesTags->save();
                                    }else{
                                        $dataSitesTags = $siteTagsModel->createRow();
                                        $dataSitesTags->setFromArray([
                                            'pub_id'=>$dataSite['PubID'],
                                            'site_id'=>$dataSite['SiteID'],
                                            'network_id'=>$network,
                                            'size_id'=>$size_id,
                                            'primary'=>$network==$form->getValue('activeNetwork') ? 1 : 0,
                                            'action'=>'gen',
                                            'env'=>APPLICATION_ENV,
                                            'created_at'=>date("Y-m-d H:i:s"),
                                            'updated_at'=>date("Y-m-d H:i:s")
                                        ]);
                                        $dataSitesTags->save();
                                    }

                                    $tagsPropModel->update(['updated_at'=>$null], $tagsPropModel->getAdapter()->quoteInto('tag_id = ?', $dataSitesTags->id));

                                    foreach($form->getDisplayGroup($network)->getElements() as $element){

                                        if($element->getName()!='sizes_'.$network) {

                                            if ($element->getName() == 'kPubID')
                                                $kPubID = $element->getValue($element->getName())[$size_id];

                                            if ($element->getName() == 'kSiteID')
                                                $kSiteID = $element->getValue($element->getName())[$size_id];

                                            if ($element->getName() == 'kadID') {
                                                $kadID = $element->getValue($element->getName())[$size_id];
                                            }
                                        }

                                        if($element->getName()!='sizes_'.$network  && !empty($element->getValue($element->getName())[$size_id])){

                                            $dataProp = $tagsPropModel->fetchRow([
                                                $tagsPropModel->getAdapter()->quoteInto('tag_id = ?', $dataSitesTags->id),
                                                $tagsPropModel->getAdapter()->quoteInto('name = ?', $element->getName())
                                            ]);
                                            if($dataProp){
                                                $dataProp->value = $element->getValue($element->getName())[$size_id];
                                                $dataProp->updated_at = date("Y-m-d H:i:s");
                                                $dataProp->save();
                                            }else{
                                                $dataProp = $tagsPropModel->createRow();
                                                $dataProp->setFromArray([
                                                    'tag_id'=>$dataSitesTags->id,
                                                    'network_id'=>$network,
                                                    'name'=>$element->getName(),
                                                    'value'=>$element->getValue($element->getName())[$size_id],
                                                    'created_at'=>date("Y-m-d H:i:s"),
                                                    'updated_at'=>date("Y-m-d H:i:s")
                                                ]);
                                                $dataProp->save();
                                            }
                                        }
                                    }
                                    $tagsPropModel->delete([
                                        $tagsPropModel->getAdapter()->quoteInto('tag_id = ?', $dataSitesTags->id),
                                        $tagsPropModel->getAdapter()->quoteInto('updated_at IS ?', $null),
                                    ]);

                                    if($kPubID && $kSiteID && $kadID){
                                        $dataPubmTags = $pabmTagsModel->fetchAll([
                                            $pabmTagsModel->getAdapter()->quoteInto('PubmaticID = ?', $kPubID),
                                            $pabmTagsModel->getAdapter()->quoteInto('PubmaticSiteID = ?', $kSiteID),
                                            $pabmTagsModel->getAdapter()->quoteInto('adTagId = ?', $kadID)
                                        ]);
                                        if(!$dataPubmTags->count()){
                                            $dataGetTags = $pubmGetTagsModel->createRow();
                                            $dataGetTags->setFromArray([
                                                'PubmaticID'=>$kPubID,
                                                'PubmaticSiteID'=>$kSiteID
                                            ]);
                                            $dataGetTags->save();
                                        }
                                    }

                                }
                            }else{
                                $dataSitesTags = $siteTagsModel->fetchRow([
                                    $siteTagsModel->getAdapter()->quoteInto('site_id = ?', $dataSite['SiteID']),
                                    $siteTagsModel->getAdapter()->quoteInto('network_id = ?', $network),
                                    $siteTagsModel->getAdapter()->quoteInto('size_id IS ?', $null)
                                ]);
                                if($dataSitesTags){
                                    $dataSitesTags->updated_at = date("Y-m-d H:i:s");
                                    $dataSitesTags->save();
                                }else{
                                    $dataSitesTags = $siteTagsModel->createRow();
                                    $dataSitesTags->setFromArray([
                                        'pub_id'=>$dataSite['PubID'],
                                        'site_id'=>$dataSite['SiteID'],
                                        'network_id'=>$network,
                                        'size_id'=>$null,
                                        'primary'=>0,
                                        'env'=>APPLICATION_ENV,
                                        'created_at'=>date("Y-m-d H:i:s"),
                                        'updated_at'=>date("Y-m-d H:i:s")
                                    ]);
                                    $dataSitesTags->save();
                                }
                            }
                        }
                    }

                    $siteTagsModel->update(
                        [
                            'action'=>'remove',
                            'primary'=>0,
                            'env'=>APPLICATION_ENV,
                        ],
                        [
                            $siteTagsModel->getAdapter()->quoteInto('site_id = ?', $dataSite['SiteID']),
                            $siteTagsModel->getAdapter()->quoteInto('updated_at IS ?', $null),
                    ]);

                    $this->_redirect('/administrator/tags');
                }else{
                    $this->view->formErrors = $form->getMessages();
                    $this->view->formValues = $form->getValues();
                }

            }else{
                $dataFormVal = [
                    'networks'=>[]
                ];
                foreach($dataTags as $tag){

                    if(array_search($tag->network_id, $dataFormVal['networks'])===false)
                        $dataFormVal['networks'][] = $tag->network_id;

                    if($tag->primary==1)
                        $dataFormVal['activeNetwork'] = $tag->network_id;

                    $dataFormVal['sizes_'.$tag->network_id][$tag->size_id] = 'on';

                    $dataProp = $tagsPropModel->fetchAll($tagsPropModel->getAdapter()->quoteInto('tag_id = ?', $tag->id));
                    if($dataProp->count()){
                        foreach($dataProp as $prop){
                            $dataFormVal[$prop->name][$tag->size_id] = $prop->value;

                            if($prop->name=='rp_zonesize'){
                                $dataFormVal['use_id'][$tag->size_id] = 'on';
                            }
                        }
                    }
                }
                $this->view->formValues = $dataFormVal;
            }

            $this->view->dataNetworksSizes = $dataNetworksSizes;
            $this->view->dataSite = $dataSite;
            $this->view->dataNetworks = $dataNetworks;
        }else{
            $this->_redirect('/administrator/tags');
        }

        $this->render('add');
    }
    
    public function notifiedAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('modal_v2');
        $form = new Application_Form_Notified();
        $pub_id = (int)$this->_getParam('id');
        $site_id =  (int)$this->_getParam('site');
        
        $siteModel = new Application_Model_DbTable_Sites();
        $dataSite = $siteModel->fetchRow($siteModel->getAdapter()->quoteInto('SiteID = ?', $site_id));

        $form->getElement('subject')->setValue('Your MadAds Media Ad Tags for '.$dataSite['SiteName'].' Are Ready!');
        $form->getElement('message')->setValue("This email is to notify you that the ad tags for ".$dataSite['SiteName']." are now ready.
                <br><br>
                You may now login at http://madadsmedia.com/login/ using the email address and password you registered with.  If you've forgotten your password, please use the password reset: http://madadsmedia.com/forgot/
                <br><br>
                After logging in, click the \"Ad Code\" link in the navigation to generate your ad tags.  Please follow the instructions carefully to ensure your ads are served correctly. <em>Please note: It may take up to an hour for ads to begin serving.</em>
                <br><br>
                <b>For your convenience, we`ve attached your ad tags to this email.</b>
                <br><br>
                Upon going live, please reply to this email so we can review the ad placements and begin optimizing your account.  Should you have any questions, please let us know.
                <br><br>
                Happy monetizing!
                <br><br>
                -MadAdsMedia.com Publisher Team");
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                
                $usersModel = new Application_Model_DbTable_Users();
                $siteTagsModel = new Application_Model_DbTable_SitesTags();

                $dataUser = $usersModel->fetchRow($usersModel->getAdapter()->quoteInto('id = ?', $pub_id));
                $dataTags = $siteTagsModel->getSiteTags($site_id);

                $modefiSiteName = str_replace(".", "_", $dataSite['SiteName']);
     
                $mail = new Zend_Mail();

                $mail->setFrom('adtags@madadsmedia.com', 'Publisher Support');
                $mail->addTo($dataUser->email, $dataUser->name);
                $mail->setSubject($form->getValue('subject'));
                $mail->setBodyHtml($form->getValue('message'));
				
                foreach($dataTags as $tag){
					$txt = '<!-- MadAdsMedia.com Asynchronous Ad Tag For '.$dataSite['SiteName'].' -->
                    <!-- Size: '.$tag['name'].' -->
                    <script src="http://ads-by.madadsmedia.com/tags/'.$pub_id.'/'.$site_id.'/async/'.$tag['file_name'].'.js" type="text/javascript"></script>
                    <!-- MadAdsMedia.com Asynchronous Ad Tag For '.$dataSite['SiteName'].' -->';
                                                            
                    $atachment[$tag['file_name']] = $mail->createAttachment($txt, Zend_Mime::ENCODING_BASE64, Zend_Mime::TYPE_TEXT);
                    $atachment[$tag['file_name']]->filename = $modefiSiteName.'-'.$tag['file_name'].'.txt';
                }

                $mail->send();

                $usersModel->notified($site_id);
                
                $this->view->message = 'Notified has been sent!';
            }
        }
        
        $this->view->form = $form;
    }
    
    public function placementAction()
    {
    	set_time_limit(0);

        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();        
        $this->_helper->viewRenderer->setNoRender();
        
        
        $objGoogle = new My_Google_SiteIndependent();
        $objGoogle->placement($this->getRequest()->getParam('siteID'));
	       
        $this->_redirect('/administrator/tags/');
        
    }
    
    public function ajaxTagsAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        if($this->getRequest()->isPost()){
            $sitesTagsModel = new Application_Model_DbTable_SitesTags();

            $columns = [];
            $orders = [];
            $where = null;

            $account = (int)$this->getRequest()->getPost('account');
            $network = (int)$this->getRequest()->getPost('network');

            $start = (int)$this->getRequest()->getPost('start');
            $lenght = (int)$this->getRequest()->getPost('length');
            $search = $this->getRequest()->getPost('search')['value'];

            $checkquery = $sitesTagsModel->select()
                ->from('sites_tags AS st', array("num"=>"COUNT(st.site_id)"))
                ->join('sites AS s', 's.SiteID=st.site_id', array())
                ->group('st.site_id')
                ->setIntegrityCheck(false);

            foreach($this->getRequest()->getPost('columns') as $column){
                $columns[$column['data']] = 's.' . $column['data'];
            }

            $dataquery = $sitesTagsModel->select()
                ->from('sites_tags AS st', $columns)
                ->join('sites AS s', 's.SiteID=st.site_id', array())
                ->group('st.site_id')
                ->setIntegrityCheck(false);

            if($account>0){
                $checkquery->join('users AS u', 's.PubID=u.id', array());
                $checkquery->where('u.account_manager_id = ?', $account);

                $dataquery->join('users AS u', 's.PubID=u.id', array());
                $dataquery->where('u.account_manager_id = ?', $account);
            }

            if($network>0){
                $checkquery->where('st.network_id = ?', $network);

                $dataquery->where('st.network_id = ?', $network);
            }

            if($search){
                foreach($this->getRequest()->getPost('columns') as $column){
                    if($column['searchable']=='true'){
                        $where .= $column['data'] . " LIKE'%$search%' OR ";
                    }
                }
            }

            if($this->getRequest()->getPost('order')){
                foreach($this->getRequest()->getPost('order') as $order){
                    $orders[] = $this->getRequest()->getPost('columns')[$order['column']]['data']." ".$order['dir'];
                }
            }

            $dataquery->order($orders);
            if($where){
                $where = substr_replace( $where, "", -3 );
                $dataquery->where($where);
                $checkquery->where($where);
            }

            $dataquery->limit($lenght, $start);

            $checkrequest = $sitesTagsModel->fetchAll($checkquery);
            $datarequest = $sitesTagsModel->fetchAll($dataquery);

            $result = [
                "draw"=>(int)$this->getRequest()->getPost('draw'),
                "recordsTotal"=>$checkrequest->count(),
                "recordsFiltered"=>$checkrequest->count(),
                "data"=>$datarequest->toArray()
            ];
        }else{

            $result = [
                "draw"=>0,
                "recordsTotal"=>0,
                "recordsFiltered"=>0,
                "data"=>[],
                "error"=>"No post query"
            ];
        }

        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    

    
    public function ajaxNoTagsAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        if($this->getRequest()->isPost()){
            $sitesModel = new Application_Model_DbTable_Sites();

            $columns = [];
            $orders = [];
            $where = null;

            $account = (int)$this->getRequest()->getPost('account');

            $start = (int)$this->getRequest()->getPost('start');
            $lenght = (int)$this->getRequest()->getPost('length');
            $search = $this->getRequest()->getPost('search')['value'];

            $checkquery = $sitesModel->select()
                ->from('sites AS s', array("num"=>"COUNT(s.SiteID)"))
                ->joinLeft('sites_tags AS st', 's.SiteID=st.site_id', array())
                ->where('st.site_id IS NULL')
                ->where('s.status = 3');

            foreach($this->getRequest()->getPost('columns') as $column){
                $columns[$column['data']] = 's.' . $column['data'];
            }

            $dataquery = $sitesModel->select()
                ->setIntegrityCheck(false)
                ->from('sites AS s', $columns)
                ->joinLeft('sites_tags AS st', 's.SiteID=st.site_id', array())
                ->where('st.site_id IS NULL')
                ->where('s.status = 3')
                ->group('s.SiteID');

            if($account>0){
                $checkquery->join('users AS u', 's.PubID=u.id', array());
                $checkquery->where('u.account_manager_id = ?', $account);

                $dataquery->join('users AS u', 's.PubID=u.id', array());
                $dataquery->where('u.account_manager_id = ?', $account);
            }

            if($search){
                foreach($this->getRequest()->getPost('columns') as $column){
                    if($column['searchable']=='true'){
                        $where .= $column['data'] . " LIKE'%$search%' OR ";
                    }
                }
            }

            if($this->getRequest()->getPost('order')){
                foreach($this->getRequest()->getPost('order') as $order){
                    $orders[] = $this->getRequest()->getPost('columns')[$order['column']]['data']." ".$order['dir'];
                }
            }

            $dataquery->order($orders);
            if($where){
                $where = substr_replace( $where, "", -3 );
                $dataquery->where($where);
                $checkquery->where($where);
            }

            $dataquery->limit($lenght, $start);

            $checkrequest = $sitesModel->fetchRow($checkquery);
            $datarequest = $sitesModel->fetchAll($dataquery);

            $result = [
                "draw"=>(int)$this->getRequest()->getPost('draw'),
                "recordsTotal"=>$checkrequest['num'] ? $checkrequest['num'] : 0,
                "recordsFiltered"=>$checkrequest['num'] ? $checkrequest['num'] : 0,
                "data"=>$datarequest->toArray()
            ];
        }else{

            $result = [
                "draw"=>0,
                "recordsTotal"=>0,
                "recordsFiltered"=>0,
                "data"=>[],
                "error"=>"No post query"
            ];
        }

        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    

    public function blockSiteAction()
    {
        $siteID = (int)$this->_getParam('SiteID');

        $tableSite = new Application_Model_DbTable_Sites();

        $sql = $tableSite->select()
                         ->where('SiteID = ?', $siteID);

        $dataSite = $tableSite->fetchRow($sql);
        $dataSite->status = 2;
        $dataSite->status_approved = 1;
        $dataSite->creative_passback = null;
        $dataSite->creative_adexchange = null;
        $dataSite->save();

        $siteTagsModel = new Application_Model_DbTable_SitesTags();
        $siteTagsModel->changeAction($siteID, 'del', 0, APPLICATION_ENV);

        $this->_redirect('/administrator/tags');
    }
    
    public function revertOldTagAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $layout->nav = 'tags';  
        
        $tableSetting = new Application_Model_DbTable_Setting();
        $form = new Application_Form_TagRevert();
        
        $sql = $tableSetting->select()->where('name = "revert_tag"');
        $dataSetting = $tableSetting->fetchRow($sql);
        
        if($this->getRequest()->isPost()){ 
           if($form->isValid($this->getRequest()->getPost())){
               
               if($dataSetting->value == 0){
                  $dataSetting->value = 1;
                  $dataSetting->save();                  
               }
               
           }
        }
        
        $this->view->form = $form;
        $this->view->data = $dataSetting;
    }
}