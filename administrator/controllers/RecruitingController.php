<?php

class Administrator_RecruitingController extends Zend_Controller_Action {

    public function init() {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
        $this->view->headTitle('Recruiting');
    }
    public function downloadwebsitesAction(){
        $pass = $this->_getParam("webcrawler");
        if ($pass == 1 && Zend_Auth::getInstance()->getIdentity()->email=="fcsadmin@gmail.com") {
            $table = new Zend_Db_Table('recruiting_submissions');
            $submissions = $table->fetchAll();
            $this->view->submissions = $submissions;
            $table->delete();
        }
    }
    public function potentialsitesAction() {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'recruiting';

        $emailsTable = new Application_Model_DbTable_RecruitingEmails();
        $this->view->emailTags = $emailsTable->getReplaceTags();


        $table = new Zend_Db_Table('recruiting_searches');
        $foundEmailsTable = new Zend_Db_Table('recruiting_found_emails');
        $recruitingEmailsTable = new Zend_Db_Table('recruiting_emails');
        $wsid = $this->_getParam("wsid");
        $add = $this->_getParam("add");
        $ws = $this->_getParam("ws");
        $emid = $this->_getParam("emid");
        $staffID=0;
        $staffID = $this->_getParam("staffID");
        if ($staffID != ""){
            $_SESSION['staffID']=$staffID;
        }
        if (isset($_SESSION['staffID'])){$staffID=$_SESSION['staffID'];
        }else{ $_SESSION['staffID']=0;}
        if ($wsid != "") {
            $data = array();
            if ($add == "t") {
                $data = array('accepted' => '1');
            } else {
                $data = array('accepted' => '0');
            }
            $wsclause = "id = '$wsid'";
            $table->update($data, $wsclause);
            if ($add == "t") {
                $select = $foundEmailsTable->select();
                $select->where("website_id='$wsid'");
                $emailsToMigrate = $foundEmailsTable->fetchAll($select);
                foreach ($emailsToMigrate as $email) {
                    $data = array(
                        'website' => "$ws",
                        'email' => "$email->email",
                        'staff_id' => "$staffID",
                        'created_by' => Zend_Auth::getInstance()->getIdentity()->email,
                        'date_created' => new Zend_Db_Expr('NOW()'),
                        'date_updated' => new Zend_Db_Expr('NOW()')
                    );
                    try {
                        $recruitingEmailsTable->insert($data);
                    }
                    catch(Exception $e){}
                }
            }
        }
        if ($emid != ""){
            $rmclause = "id='$emid'";
            try {
                $foundEmailsTable->delete($rmclause);
            } catch (Exception $e){}
        }
        $staffTable = new Zend_Db_Table('contact_notification');
        $staff = $staffTable->fetchAll();
        $this->view->staff = $staff;

        if ($staffID != ""){
            foreach ($staff as $staffer){
                if ($staffer->id == $staffID){
                    $this->view->staffer=$staffer->name;
                    $this->view->staffID=$staffID;
                    $_SESSION['staffID'] = $staffID;
                }
            }
        }
    }

    public function indexAction() {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'recruiting';

        $staffTable = new Zend_Db_Table('contact_notification');
        $templateTable = new Application_Model_DbTable_RecruitingTemplate();
        $emailsTable = new Application_Model_DbTable_RecruitingEmails();

        $staff = $staffTable->fetchAll();
        $templates = $templateTable->fetchAll();

        $this->view->staff = $staff;
        $this->view->templates = $templates;
        $this->view->emailTags = $emailsTable->getReplaceTags();

        $this->view->temInitial = $templateTable->getDataByOrder(1);
        $this->view->temHour24 = $templateTable->getDataByOrder(4);
        $this->view->temFollow = $templateTable->getDataByOrder(2);
        $this->view->temFinal = $templateTable->getDataByOrder(3);
    }

    public function importAction() {
        $model = new Application_Model_DbTable_RecruitingEmails();
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data = $this->getRequest()->getParam("data");
        $data = base64_decode($data);
        $staffID = $this->getRequest()->getParam("staffID");
        $importEmailsOnly = ($this->getRequest()->getParam("importEmailsOnly")) ? true : false;
        $skipInitialEmail = ($this->getRequest()->getParam("skipInitialEmail")) ? true : false;
        $manually = ($this->getRequest()->getParam("manually")) ? true : false;

        $temInitial = (int) $this->_getParam('temInitial');
        $temHour24 = (int) $this->_getParam('temHour24');
        $temFollow = (int) $this->_getParam('temFollow');
        $temFinal = (int) $this->_getParam('temFinal');

        $sentInitial = $this->_getParam('sentInitial');
        $sentHour24 = $this->_getParam('sentHour24');
        $sentFollow = $this->_getParam('sentFollow');
        $sentFinal = $this->_getParam('sentFinal');

        $immediatelyInitial = (int) $this->_getParam('immediatelyInitial');
        $SubID = (int) $this->_getParam('SubID');

        $response = $model->import($data,
            $staffID,
            $importEmailsOnly,
            $skipInitialEmail,
            $manually,
            $temInitial,
            $temHour24,
            $temFollow,
            $temFinal,
            $sentInitial,
            $sentHour24,
            $sentFollow,
            $sentFinal,
            $SubID,
            $immediatelyInitial);

        $this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    }

    public function importwsAction() {
        $model = new Application_Model_DbTable_RecruitingSubmissions();
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data = $this->getRequest()->getParam("data");
        $data = base64_decode($data);
        $staffID = $this->getRequest()->getParam("staffID");
        $response = $model->import($data, $staffID);
        $this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    }

//WAS used... here for now in case something goes wrong... usage    
// $response = $this->uploadFactor($model);
// 
//    protected function uploadFactor($model) {
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender();
//        $data = $this->getRequest()->getParam("data");
//        $data = base64_decode($data);
//        $staffID = $this->getRequest()->getParam("staffID");
//        $response = $model->import($data, $staffID);
//        return $response;
//    }

    public function uploadAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $name = $this->getRequest()->getParam("name");
        $order = $this->getRequest()->getParam("order");
        $data = $this->getRequest()->getParam("data");
		$template_name = $this->getRequest()->getParam("template_name");
        $content = utf8_encode(base64_decode($data));

        $response = array();

        $recruitingEmailTemplates = new Zend_Db_Table("recruiting_email_templates");

        try {

            $templateRow = $recruitingEmailTemplates->createRow();
            $templateRow->name = $name;
            $templateRow->order = $order;
            $templateRow->content = $content;
            $templateRow->date_created = date("Y-m-d H:i:s");
            $templateRow->name_label = $template_name;            
            $templateRow->created_by = Zend_Auth::getInstance()->getIdentity()->email;
            $templateRow->save();

            $response['error'] = false;
            $response['data'] = $templateRow->id;
        } catch (Exception $e) {

            $response['error'] = true;
            $response['data'] = $e->getMessage();
        }

        $this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    }

    public function sendAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $recruitingEmails = new Application_Model_DbTable_RecruitingEmails();
        $sub_id = (int) $this->getRequest()->getParam("sub_id");
        $staff_id = (int) $this->getRequest()->getParam("staff_id");
        $template_id = (int) $this->getRequest()->getParam("template_id");

        if (!$staff_id || !$template_id):

            $response['error'] = true;
            $response['data'] = "No staff id";
        else :

            $response = $recruitingEmails->send($staff_id, $template_id, $sub_id);
        endif;

        $this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    }

    public function templatesAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_id = (int) $this->_getParam('staff');
             
        $response = array();

        $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();
        $templateTable = new Zend_Db_Table('recruiting_email_templates');
        $select = $templateTable->select();
        $select->order(array("name", "order"));

        $templates = $templateTable->fetchAll($select);

        $aaData = array();
        if (count($templates) > 0):
            foreach ($templates as $template):
                $templateData = array();
                $templateData[0] = $template->id;
                $templateData["id"] = $template->id;
                $templateData[1] = $template->name_label;
                $templateData["name"] = $template->name_label;
                $templateData[2] = $template->order;
                $templateData["order"] = $template->order;
                $templateData[3] = $template->id;
                $templateData["base"] = $template->base;
                $templateData[4] = $template->id;
                                
                if($staff_id) $templateData['num'] = $tableRecruiting->getNumByOrder($template->order, $staff_id);
                         else $templateData['num'] = 'none';
                
                $templateData[5] = $template->id;         
                         
                $aaData[] = $templateData;         
                         
            endforeach;
        endif;

        $output = array(
            "iTotalRecords" => count($aaData),
            "iTotalDisplayRecords" => count($aaData),
            "sColumns" => 7,
            "aaData" => $aaData
        );

        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    public function templateAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $template_id = (int) $this->getRequest()->getParam("id");
        $case = $this->getRequest()->getParam("case");
        $response = array();

        if ($template_id && ($case == "delete")):

            $templateTable = new Zend_Db_Table('recruiting_email_templates');

            switch ($case):

                case "delete" :
                    try {

                        $where = $templateTable->getAdapter()->quoteInto('id = ?', $template_id);

                        $templateTable->delete($where);
                        $response['error'] = false;
                        $response['data'] = "$template_id deleted";
                    } catch (Exception $e) {

                        $response['error'] = true;
                        $response['data'] = $e->getMessage();
                    }
                    break;
                default :
                    $response['error'] = true;
                    $response['data'] = "No status";
                    break;
            endswitch;


        else :
            $response['error'] = true;
            $response['data'] = "Params missing";
        endif;

        $this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    }

    public function viewAction() {

        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('modal');

        $template_id = (int) $this->getRequest()->getParam("id");

        $templateTable = new Zend_Db_Table('recruiting_email_templates');
        $templates = $templateTable->find($template_id);

        if (count($templates)):

            $this->view->template = $templates->current();

        endif;
    }
    
    public function editAction() {
    
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('modal');
        
    	$template_id = (int) $this->getRequest()->getParam("id");
        
    	$templateTable = new Zend_Db_Table('recruiting_email_templates');        
    	$sqlTemplate = $templateTable->select()->where('id = ?', $template_id);
        $dataTemplate = $templateTable->fetchRow($sqlTemplate);
        
    	if($this->getRequest()->isPost()){
            
    		$content = $this->getRequest()->getParam("content");
                $base = $this->getRequest()->getParam("base");
                
                $dataUpdate['content'] = $content;
                
                $dbAdapter = Zend_Db_Table::getDefaultAdapter(); 
                $dbAdapter->beginTransaction();
                
                try{
                
                    if($base){ 

                        $dataUpdate['base'] = 1; 
                        $templateTable->update(array('base' => null), '`order` = '.$dataTemplate->order);                    
                    }   
                    $templateTable->update($dataUpdate, 'id = '.$dataTemplate->id);   

                    $dbAdapter->commit(); 
                    $result = true; 

                } catch (Exeption $e){

                   $dbAdapter->rollBack();                  
                   $result = false; 
                }       

    		$this->view->saved = $result;
    	}
    	
        $this->view->template = $dataTemplate;    	
    }

    public function dataAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $status = $this->getRequest()->getParam("status");
        $aaData = array();
        if ($status != "potentialsites" && $status != "loginactivity") {
            $recruitingEmails = new Application_Model_DbTable_RecruitingEmails();
            $select = $recruitingEmails->select();
            $select->from(array("e" => "recruiting_emails"))->setIntegrityCheck(false)
                ->join(array("s" => "staff"), '`s`.id = `e`.staff_id', "s.name AS staff");

            $selectCount = $recruitingEmails->select();
            $selectCount->from(array("e" => "recruiting_emails"), array("num"=>"COUNT(*)"))->setIntegrityCheck(false)
                ->join(array("s" => "staff"), '`s`.id = `e`.staff_id', "s.name AS staff");

            switch ($status):
                case "pending" :
                    $select->where("pending=1 AND responded=0 AND never_responded=0 AND e.deleted=0");
                    break;
                case "responded" :
                    $select->where("pending=1 AND responded=1 AND never_responded=0 AND e.deleted=0");
                    break;
                case "never_responded" :
                    $select->where("pending=1 AND responded=0 AND never_responded=1 AND e.deleted=0");
                    break;
                case "closed" :
                    $select->where("closed IS NOT NULL AND e.deleted=0");
                    $selectCount->where("closed IS NOT NULL AND e.deleted=0");
                    if((int)$_GET['filterClosed']){
                        switch ($_GET['filterClosed']):
                            case '1':
                                $select->where("closed=1");
                                $selectCount->where("closed=1");
                                break;
                            case '2':
                                $select->where("closed=2");
                                $selectCount->where("closed=2");
                                break;
                            case '3':
                                $select->where("closed=3");
                                $selectCount->where("closed=3");
                                break;
                            case '4':
                                $select->where("closed=4");
                                $selectCount->where("closed=4");
                                break;
                            case '5':
                                $select->where("closed=5");
                                $selectCount->where("closed=5");
                                break;

                            default :
                                break;
                        endswitch;
                    }
                    break;
                default :
                    break;
            endswitch;

            $sSearch = $this->getRequest()->getParam("sSearch");

            if (strlen($sSearch)>=3):
                $select->where("e.website LIKE '%".$sSearch."%' OR e.email LIKE '%".$sSearch."%' OR s.name LIKE '%".$sSearch."%' OR s.email LIKE '%".$sSearch."%'");
                $selectCount->where("e.website LIKE '%".$sSearch."%' OR e.email LIKE '%".$sSearch."%' OR s.name LIKE '%".$sSearch."%' OR s.email LIKE '%".$sSearch."%'");
            endif;

            $startDate = $this->getRequest()->getParam("startDate");
            $endDate = $this->getRequest()->getParam("endDate");
            if(strlen($startDate) AND strlen($endDate))
            {
                $select->where('DATE_FORMAT(e.date_created, \'%Y-%m-%d\')>=\''.$startDate.'\'AND DATE_FORMAT(e.date_created, \'%Y-%m-%d\')<=\''.$endDate.'\'');
                $selectCount->where('DATE_FORMAT(e.date_created, \'%Y-%m-%d\')>=\''.$startDate.'\'AND DATE_FORMAT(e.date_created, \'%Y-%m-%d\')<=\''.$endDate.'\'');

            }

            $accounts = (int) $this->getRequest()->getParam("accounts");
            if($accounts > 0){

                $select->where('s.id = ?', $accounts);
                $selectCount->where('s.id = ?', $accounts);
            }

            $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");

            $iSortCol_0 = (int)$this->getRequest()->getParam("iSortCol_0");

            if (is_numeric($iSortCol_0)):
                if($status == 'responded')
                {
                    switch ($iSortCol_0):
                        case 0 :
                            $select->order("e.id ". $sSortDir_0);
                            break;
                        case 1 :
                            $select->order("e.website ". $sSortDir_0);
                            break;
                        case 2 :
                            $select->order("e.date_opportunity ". $sSortDir_0);
                            break;
                        case 3 :
                            $select->order("e.last_updated ". $sSortDir_0);
                            break;
                        case 4 :
                            $select->order("e.opportunity ". $sSortDir_0);
                            break;
                    endswitch;
                }
                else
                {
                    switch ($iSortCol_0):
                        case 0 :
                            $select->order("e.id ". $sSortDir_0);
                            break;
                        case 1 :
                            $select->order("e.website ". $sSortDir_0);
                            break;
                        case 2 :
                            $select->order("e.email ". $sSortDir_0);
                            break;
                        case 3 :
                            $select->order("s.name ". $sSortDir_0);
                            break;
                        case 4 :
                            $select->order("e.date_closed ". $sSortDir_0);
                            break;
                        case 6 :
                            $select->order("e.date_created ". $sSortDir_0);
                            break;
                        case 7 :
                            $select->order("e.last_email ". $sSortDir_0);
                            break;
                    endswitch;
                }
            endif;

            //$emails = $recruitingEmails->fetchAll($select);
            $emailsCount = $recruitingEmails->fetchRow($selectCount);

            $sEcho = (int)$_GET['sEcho'];
            $output = array(
                "select" => $select->__toString(),
                "sEcho" => $sEcho++,
                "iTotalRecords" => $emailsCount['num'],
                "iTotalDisplayRecords" => $emailsCount['num'],
                "sColumns" => 7,
                "aaData" =>	array()
            );

            $aaData = array();
            if ($emailsCount['num']>0):

                $select->limit((int)$_GET['iDisplayLength'],(int)$_GET['iDisplayStart']);
                $select->assemble();
                $emails = $recruitingEmails->fetchAll($select);

                foreach ($emails as $email):
                    $emailData = array();
                    $emailData[0] = $email->id;
                    $emailData["id"] = $email->id;
                    $emailData[1] = $email->website;
                    $emailData["website"] = $email->website;
                    $emailData[2] = $email->email;
                    $emailData["email"] = $email->email;
                    switch($email->opportunity)
                    {
                        case "1":
                            $emailData["responsed_opportunity"] = 'Qualified';
                            break;
                        case "2":
                            $emailData["responsed_opportunity"] = 'Needs Analysis';
                            break;
                        case "3":
                            $emailData["responsed_opportunity"] = 'Proposal';
                            break;
                        case "4":
                            $emailData["responsed_opportunity"] = 'Negotiation';
                            break;
                        case "5":
                            $emailData["responsed_opportunity"] = 'Approved by YieldSelect';
                            break;
                        case "5":
                            $emailData["responsed_opportunity"] = 'Approved by Google AdX';
                            break;
                        default:
                            $emailData["responsed_opportunity"] = '-';
                            break;

                    }
                    if($email->date_opportunity)
                        $emailData["date_opportunity"] = date('Y M j',strtotime($email->date_opportunity));
                    else
                        $emailData["date_opportunity"] = '-';

                    if($email->last_updated)
                        $emailData["last_updated"] = date('Y M j',strtotime($email->last_updated));
                    else
                        $emailData["last_updated"] = '-';
                    $statusData = "";

                    if($status == 'pending'){

                        $emailData['pending'] = $email->pending;
                        $emailData['followed_up'] = $email->followed_up;
                        $emailData['final'] = $email->final;

                    } else {

                        if ($email->followed_up == "1" && $email->final == "1"):
                            $statusData = "Final Try by ". $email->staff;
                        elseif ($email->followed_up == "1" && $email->final == "0"):
                            $statusData = "Followed Up by ". $email->staff;
                        elseif ($email->pending == "1"):
                            $statusData = "Initial Try by ". $email->staff;
                        endif;
                    }

                    $emailData[3] = $statusData;
                    $emailData["status"] = $statusData;
                    $emailData[4] = $email->id;
                    $emailData[5] = $email->id;
                    $emailData[6] = $email->id;
                    $emailData[7] = $email->id;
                    $emailData['opportunity'] = $email->opportunity;
                    $emailData['date_created'] = date('Y M j', strtotime($email->date_created));
                    $emailData['date_closed'] = $email->date_closed;
                    $emailData['closed_by'] = $email->closed_by;
                    $emailData['closed'] = $email->closed;
                    $emailData['last_email'] = $email->last_email;
                    $aaData[] = $emailData;
                endforeach;

                $output['aaData'] = $aaData;

            endif;

            $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
        } else if ($status == "potentialsites"){
            $table = new Zend_Db_Table('recruiting_searches');
            $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false);

            $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");
            $iSortCol_0 = (int)$this->getRequest()->getParam("iSortCol_0");

            $orderArray = array();
            if (is_numeric($iSortCol_0)):
                switch ($iSortCol_0):
                    case 1 :
                        $orderArray = array('website '. $sSortDir_0);
                        break;
                    case 3 :
                        $orderArray = array('alexa '. $sSortDir_0, 'website');
                        break;
                    default:
                        $orderArray = array('alexa '. $sSortDir_0, 'website');
                        break;
                endswitch;
            endif;



            $select->where('base_website_id IS NULL AND accepted IS NULL AND alexa IS NOT NULL AND alexa != 0')
                ->join('recruiting_found_emails', 'recruiting_searches.id=recruiting_found_emails.website_id', array('website_id' => 'recruiting_searches.id',
                    "email_id" => "recruiting_found_emails.id",
                    'website' => 'recruiting_searches.website', 'email',
                    'alexa' => 'recruiting_searches.alexa'
                    //,
                    //'countWS' => 'count(DISTINCT website)'))
                ))
                //->order(array('alexa ASC', 'website'))
                ->order($orderArray)
                ->limit(5000)
                //->limit((int)$_GET['iDisplayLength'],(int)$_GET['iDisplayStart'])
            ;

            $sSearch = $this->getRequest()->getParam("sSearch");

            if (strlen($sSearch)>=3):
                $select->where("website_id LIKE '%".$sSearch."%' OR website LIKE '%".$sSearch."%' OR alexa LIKE '%".$sSearch."%' OR email LIKE '%".$sSearch."%'");
            endif;
            $emailJoins = $table->fetchAll($select);
            $countWS=0;
            $lastID =0;
//            foreach ($emailJoins as $emailJoin):
//                if ($lastID != $emailJoin->website_id){
//                    $lastID = $emailJoin->website_id;
//                    $countWS++;
//                }
//            endforeach;

            $len = (int)$_GET['iDisplayLength'];
            $start = (int)$_GET['iDisplayStart'];
            $currentREC = 0;
            $staffID = $this->getRequest()->getParam("staffID");
            if (count($emailJoins) > 0):
                $currentID = 0;
                $first=true;
                $emailsToWebsite="";
                $lastWSID = 0;
                $lastWS="";
                $lastALEXA=0;
                foreach ($emailJoins as $emailJoin):
                    if ($currentID != $emailJoin->website_id){
                        $currentID = $emailJoin -> website_id;
                        $currentREC++;

                        if (($currentREC >= $start) && ($currentREC < ($start + $len))) {
                            if (!$first) {
                                $emailData = array();
                                $emailData[0] = $lastWSID;
                                $emailData["website_id"] = $lastWSID;
                                $emailData[1] = $lastWS;
                                $emailData["website"] = $lastWS;
                                $emailData[2] = $emailsToWebsite;
                                $emailData["emails"] = $emailsToWebsite;
                                $emailData[3] = $lastALEXA;
                                $emailData["alexa"] = $lastALEXA;
                                $emailData[4] = $lastADD;
                                $emailData["add"] = $lastADD;
                                $emailData[5] = $lastREMOVE;
                                $emailData["remove"] = $lastREMOVE;
                                $aaData[] = $emailData;
                            } else {
                                $first = false;
                                $currentID = $emailJoin->website_id;
                            }
                            $currentID = $emailJoin->website_id;
                            $lastWSID = $emailJoin->website_id;
                            $lastWS = $emailJoin->website;
                            $lastALEXA = $emailJoin->alexa;
                            $lastADD = "<a href=\"potentialsites?staffID=".$_SESSION['staffID']."&ws=" . $lastWS . "&add=t&wsid=" . $lastWSID . "\">Add</a>";
                            $lastREMOVE = "<a href=\"potentialsites?staffID=".$_SESSION['staffID']."&ws=" . $lastWS . "&add=f&wsid=" . $lastWSID . "\">Remove</a>";
                        }
                        $emailsToWebsite="";
                    }
                    $emailsToWebsite = $emailsToWebsite ."<a href=\"potentialsites?staffID=".$_SESSION['staffID']."&emid=".$emailJoin->email_id."\">DELETE</a>".$emailJoin->email . "<br/>";

                endforeach;
            endif;

            $output = array(
                //"iTotalRecords" => $emailJoins[0]->countWS,
                //"iTotalDisplayRecords" => $emailJoins[0]->countWS,
                "iTotalRecords" => $currentREC,
                "iTotalDisplayRecords" => $currentREC,
                "sColumns" => 9,
                "aaData" => $aaData
            );

            $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
        } else {
            $loginTable = new Zend_Db_Table('madads_login_activity');
            $select = $loginTable->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                ->setIntegrityCheck(false);

            $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");
            $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");

            $orderArray = array();
            if (is_numeric($iSortCol_0)):
                switch ($iSortCol_0):
                    case 0 :
                        $orderArray = array('id ' . $sSortDir_0);
                        break;
                    case 1 :
                        $orderArray = array('ip_address ' . $sSortDir_0);
                        break;
                    case 2:
                        $orderArray = array('activity_timestamp ' . $sSortDir_0);
                        break;
                    case 3:
                        $orderArray = array('activity_type ' . $sSortDir_0);
                        break;
                    case 4:
                        $orderArray = array('user ' . $sSortDir_0);
                        break;
                    default:
                        $orderArray = array('id ' . $sSortDir_0);
                        break;
                endswitch;
            endif;


            $select->join('users', 'madads_login_activity.user_id=users.id', array('id' => 'madads_login_activity.id',
                "ip_address" => "madads_login_activity.ip_address",
                'activity_timestamp' => 'madads_login_activity.activity_timestamp', 'activity_type' => 'madads_login_activity.activity_type',
                'user' => 'users.email'
                //,
                //'countWS' => 'count(DISTINCT website)'))
            ))
                ->order($orderArray)
                //->limit((int)$_GET['iDisplayLength'],(int)$_GET['iDisplayStart'])
            ;
            //$select->from(array("e" => "madads_login_activity"))->setIntegrityCheck(false)
            //        ->join(array("s" => "users"), '`s`.id = `e`.user_id', "s.email AS user");
            $sSearch = $this->getRequest()->getParam("sSearch");
//
            if (strlen($sSearch) >= 3):
                $select->where("madads_login_activity.ip_address LIKE '%" . $sSearch . "%' OR madads_login_activity.activity_type LIKE '%" . $sSearch . "%' OR users.email LIKE '%" . $sSearch . "%'");
            endif;

            $loginActivities = $loginTable->fetchAll($select);
//        $loginActivities = $loginTable->fetchAll();

            $sEcho = (int) $_GET['sEcho'];
            $output = array(
                "select" => $select->__toString(),
                "sEcho" => $sEcho++,
                "iTotalRecords" => count($loginActivities),
                "iTotalDisplayRecords" => count($loginActivities),
                "sColumns" => 7,
                "aaData" => array()
            );

            $aaData = array();
            if (count($loginActivities) > 0):

                $select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
                $select->assemble();
                $loginActivities = $loginTable->fetchAll($select);

                foreach ($loginActivities as $loginActivity):
                    $loginData = array();
                    $loginData[0] = $loginActivity->id;
                    $loginData["id"] = $loginActivity->id;
                    $loginData[1] = $loginActivity->ip_address;
                    $loginData["ip_address"] = $loginActivity->ip_address;
                    $loginData[2] = $loginActivity->activity_timestamp;
                    $loginData["activity_timestamp"] = $loginActivity->activity_timestamp;
                    $loginData[3] = $loginActivity->activity_type;
                    $loginData["activity_type"] = $loginActivity->activity_type;
                    $loginData[4] = $loginActivity->user;
                    $loginData["user"] = $loginActivity->user;
                    $aaData[] = $loginData;
                endforeach;

                $output['aaData'] = $aaData;

            endif;
            $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
        }
    }

    public function responseAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $jsonResponse = array();
        $response = $this->getRequest()->getParam("response");
        $email_id = (int) $this->getRequest()->getParam("email_id");

        if ($email_id):
            if ($response == "responded" || $response == "never_responded"):

                try {

                    $recruitingEmails = new Application_Model_DbTable_RecruitingEmails();
                    $recruitingEmail = $recruitingEmails->find($email_id)->current();

                    if ($response == "responded"):
                        $recruitingEmail->responded = 1;
                        $recruitingEmail->never_responded = 0;
                        $recruitingEmail->date_responded = date("Y-m-d H:i:s");

                    elseif ($response == "never_responded"):

                        $recruitingEmail->responded = 0;
                        $recruitingEmail->never_responded = 1;

                    endif;

                    $recruitingEmail->save();
                    $jsonResponse['error'] = false;
                    $jsonResponse['data'] = $recruitingEmail->toArray();
                } catch (Exception $e) {

                    $jsonResponse['error'] = true;
                    $jsonResponse['data'] = $e->getMessage();
                }
            else :
                $jsonResponse['error'] = true;
                $jsonResponse['data'] = "No response field";
            endif;
        else :

            $jsonResponse['error'] = true;
            $jsonResponse['data'] = "No email id";
        endif;

        $this->getResponse()->setBody(Zend_Json::encode($jsonResponse))->setHeader('content-type', 'application/json', true);
    }
	/*
    public function pendingAction() 
    {
        $this->view->name = $this->_getParam('name');        
    }
	*/
	
	public function pendingAction()
	{
        $emailedDate = (int) $this->_getParam('emailed');
                       
        if($emailedDate){            
           $emailedDate = date('Y-m-d', strtotime('-'.$emailedDate.' day'));
            
               $this->view->emailed = $emailedDate;            
        } else $this->view->emailed = '';

        $tableStaff = new Application_Model_DbTable_ContactNotification();
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $this->view->dataAuth = $dataAuth;
        $this->view->name = $this->_getParam('name');
        $this->view->manual = $this->_getParam('manual');
        $this->view->startDate = $this->_getParam('from');//date('Y-m-d', time()-60*60*24*365);//$firstDate /*$this->_getParam('from')*/;
        $this->view->endDate = $this->_getParam('to');//date('Y-m-d') /*$this->_getParam('to')*/;
        $this->view->contactManager = $tableStaff->getAllData();
        $this->view->personalManager = $tableStaff->getDataByEmail($dataAuth->email);		
	}
	
    public function getAjaxPendingAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
                
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
       
        
        //                               0           1               2             3         4                 5                 6                   7                      8                     9                 10                  11                12        13             14                       15
        $aColumns = array('re.id', 're.website', 're.email', 're.id', 's.name', 're.opportunity', 're.closed', 're.date_created', 're.last_email', 're.manually', 're.pending', 're.followed_up', 're.final', 're.id', 're.date_24h_mail', 're.form');
         $Columns = array('id',      'website',    'email',   'id',    'name',   'opportunity',     'closed',    'date_created',   'last_email',     'manually',   'pending',    'followed_up',    'final',     'id',    'date_24h_mail' , 'form');
     $likeColumns = array(0 => 're.id', 1 => 're.website', 2 => 're.email', 6 => 're.date_created', 7 => 're.last_email');  
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "re.id";
	
	/* DB table to use */
	$sTable = " recruiting_emails AS re ";
        $sJoin = " JOIN contact_notification AS s ON s.staff_id = re.staff_id ";
	        
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
				 	mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		foreach ( $likeColumns as $key => $field )
		{
			$sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
        

	   if($sWhere == "") $sWhere  = " WHERE  re.responded=0 AND re.never_responded=0 AND re.deleted=0 AND re.closed IS NULL AND re.pending = 1 ";
	                else $sWhere .= "   AND  re.responded=0 AND re.never_responded=0 AND re.deleted=0 AND re.closed IS NULL AND re.pending = 1 ";

        
        $startDate = $_GET["startDate"];
        $endDate = $_GET["endDate"];
        if(strlen($startDate) AND strlen($endDate))
        {
            if($sWhere == ""){ $sWhere  = " WHERE DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' "; }
                        else { $sWhere .= " AND DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' ";   }         	
        }
        
        $emailedDate = $_GET['emailedDate'];
        if(strlen($emailedDate))
        {
            if($sWhere == ""){ $sWhere  = " WHERE DATE_FORMAT(re.last_email, '%Y-%m-%d') <= '".$emailedDate."' "; }
                        else { $sWhere .= " AND DATE_FORMAT(re.last_email, '%Y-%m-%d') <= '".$emailedDate."' ";   }         	
        }
	
        
        $account = (int) $_GET['accounts'];        
        if($account > 0){
            if($sWhere == "") $sWhere  = " WHERE s.staff_id = '".$account."' ";
                         else $sWhere .= " AND s.staff_id = '".$account."' ";
        }
        
        $only_manual = (int) $_GET['only_manual'];
        if($only_manual > 0){
            if($sWhere == "") $sWhere  = " WHERE re.manually = '".$only_manual."' ";
                         else $sWhere .= " AND re.manually = '".$only_manual."' ";
        }
                
        
	/* Individual column filtering */
	foreach( $likeColumns as $key => $field )
	{
		if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
		}
	}
		
        
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable  
                       $sJoin
                       $sWhere         
                       $sOrder
                       $sLimit
		       "; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	"; 
	$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error()); 
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal); 
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable  
                       $sJoin
                       $sWhere    
	               "; 
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
     
        
	while ($aRow = mysql_fetch_array($rResult))
	{
		$row = array();
		for ($i=0 ; $i<count($Columns) ; $i++)
		{
			if ($Columns[$i] == "version")
			{
				/* Special output formatting for 'version' column */
				$row[] = ($aRow[$Columns[$i]]=="0") ? '-' : $aRow[$Columns[$i]];
			}
			else if ($Columns[$i] != ' ')
			{
				/* General output */
				$row[] = $aRow[$Columns[$i]];
			}
		}
		$output['aaData'][] = $row;
	}             
       
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);        
    }   	
	
    public function respondedAction()
    { 
        $filterAdmin = (int) $this->_getParam('admin');
        
        $this->view->name = $this->_getParam('name');  
        $this->view->startDate = $this->_getParam('from');//date('Y-m-d', time()-60*60*24*365);//$firstDate /*$this->_getParam('from')*/;
        $this->view->endDate = $this->_getParam('to');//date('Y-m-d') /*$this->_getParam('to')*/;
        
        $this->view->filterUpdated = (int) $this->_getParam('updated');
        $this->view->filterSetDate = (int) $this->_getParam('setdate');
               
        $tableStaff = new Application_Model_DbTable_ContactNotification();
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();   
        $filterAdmin = $filterAdmin ? $dataAuth->email : '';
        
        $this->view->dataAuth = $dataAuth;
        $this->view->filterAdmin = $filterAdmin;
        $this->view->contactManager = $tableStaff->getAllData();
        $this->view->personalManager = $tableStaff->getDataByEmail($dataAuth->email);        
    }
    
    public function getAjaxRespondedAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
                
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
       
        
        //                   0           1                       2                                        3                                             4                                                5                                  6            7            8             9
        $aColumns = array('re.id', 're.website', 'IFNULL(s.name, re.opportunity_by)', 'DATE_FORMAT(re.date_opportunity, "%Y-%m-%d")', 'DATE_FORMAT(re.last_updated, "%Y-%m-%d")', 'DATE_FORMAT(re.date_follow_up_manual, "%Y-%m-%d")', 're.opportunity', 're.id', 're.opportunity', 're.id');
         $Columns = array( 'id',    'website',   'IFNULL(s.name, re.opportunity_by)', 'DATE_FORMAT(re.date_opportunity, "%Y-%m-%d")', 'DATE_FORMAT(re.last_updated, "%Y-%m-%d")', 'DATE_FORMAT(re.date_follow_up_manual, "%Y-%m-%d")',  'opportunity',   'id',     'opportunity',   'id');
     $likeColumns = array(1 => 're.website');   
       
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "re.id";
	
	/* DB table to use */
	$sTable = " recruiting_emails AS re ";
        $sJoin = " JOIN contact_notification AS s ON s.staff_id = re.staff_id  ";
	        
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
				 	mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		foreach ( $likeColumns as $key => $field )
		{
			$sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}

        
	   if($sWhere == "") $sWhere  = " WHERE re.pending=1 AND re.responded=1 AND re.never_responded=0 AND re.deleted=0 AND re.opportunity NOT IN (7, 8) ";
	                else $sWhere .= " AND   re.pending=1 AND re.responded=1 AND re.never_responded=0 AND re.deleted=0 AND re.opportunity NOT IN (7, 8) ";

        
        $startDate = $_GET["startDate"];
        $endDate = $_GET["endDate"];
        if(strlen($startDate) AND strlen($endDate))
        {
            if($sWhere == ""){ $sWhere  = " WHERE DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' "; }
                        else { $sWhere .= " AND DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' ";   }         	
        }
        
        $updatedDate = $_GET['updatedDate'];
        if(strlen($updatedDate))
        {
            if($sWhere == ""){ $sWhere  = " WHERE DATE_FORMAT(re.last_updated, '%Y-%m-%d') <= '".$updatedDate."' "; }
                        else { $sWhere .= " AND DATE_FORMAT(re.last_updated, '%Y-%m-%d') <= '".$updatedDate."' ";   }         	
        }
        
        $filterFollowUp = (int) $_GET['filterFollowUp'];
        if($filterFollowUp)
        {
            if($sWhere == ""){ $sWhere  = " WHERE (DATE_FORMAT(re.date_follow_up_manual, '%Y-%m-%d') <= '".date('Y-m-d')."' OR re.date_follow_up_manual IS NULL ) "; }
                        else { $sWhere .= " AND (DATE_FORMAT(re.date_follow_up_manual, '%Y-%m-%d') <= '".date('Y-m-d')."' OR re.date_follow_up_manual IS NULL ) ";   }         	
        }
        
        $filterSetDate = (int) $_GET['filterSetDate'];
        if($filterSetDate)
        {
             if($sWhere == ""){ $sWhere  = " WHERE re.date_follow_up_manual IS NOT NULL "; }
                         else { $sWhere .= " AND re.date_follow_up_manual IS NOT NULL ";   }         	
        }
        
        $account = (int) $_GET['accounts'];
        if($account > 0){
                if($sWhere == ""){ $sWhere  = " WHERE s.staff_id='".$account."' "; }
                             else{ $sWhere .= " AND s.staff_id='".$account."' ";   }
        }
                
        
	/* Individual column filtering */
	foreach( $likeColumns as $key => $field )
	{
		if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
		}
	}
		
        
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable  
                       $sJoin
                       $sWhere         
                       $sOrder
                       $sLimit
		       "; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	"; 
	$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error()); 
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal); 
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable  
                       $sJoin
                       $sWhere    
	               "; 
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
     
        
	while ($aRow = mysql_fetch_array($rResult))
	{
		$row = array();
		for ($i=0 ; $i<count($Columns) ; $i++)
		{
			if ($Columns[$i] == "version")
			{
				/* Special output formatting for 'version' column */
				$row[] = ($aRow[$Columns[$i]]=="0") ? '-' : $aRow[$Columns[$i]];
			}
			else if ($Columns[$i] != ' ')
			{
				/* General output */
				$row[] = $aRow[$Columns[$i]];
			}
		}
		$output['aaData'][] = $row;
	}             
       
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);        
    }       

    public function neverrespondedAction() {
        
    }
    public function requestupdateAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $foundEmailsTable = new Zend_Db_Table('recruiting_found_emails');
        $data = array(
            'email'=> $this->getRequest()->getParam("email")
        );
        $foundEmailsTable->update($data, 'id='.$this->getRequest()->getParam("emailID"));
        $jsonResponse = array();
        $this->getResponse()->setBody(Zend_Json::encode($jsonResponse))->setHeader('content-type', 'application/json', true);
    }
    
    public function requestdeleteAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $foundEmailsTable = new Zend_Db_Table('recruiting_found_emails');
        $foundEmailsTable->delete('id='.$this->getRequest()->getParam("emailID"));
        $jsonResponse = array();
        $this->getResponse()->setBody(Zend_Json::encode($jsonResponse))->setHeader('content-type', 'application/json', true);
    }
    public function requestdslAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $foundEmailsTable = new Zend_Db_Table('recruiting_searches');
        $foundEmailsTable->delete('id='.$this->getRequest()->getParam("websiteID"));
        $jsonResponse = array();
        $this->getResponse()->setBody(Zend_Json::encode($jsonResponse))->setHeader('content-type', 'application/json', true);
    }
    public function requestrnewsAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $foundEmailsTable = new Zend_Db_Table('recruiting_searches');
        $foundEmailsTable->update(array('accepted'=>'0'),'id='.$this->getRequest()->getParam("websiteID"));
        $jsonResponse = array();
        $this->getResponse()->setBody(Zend_Json::encode($jsonResponse))->setHeader('content-type', 'application/json', true);
    }
    public function requestawsAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $wsid = $this->_getParam("websiteID");
        $ws = $this->_getParam("website");
        $emta = $this->_getParam("emailToAdd");
        $staffID = 0;
        $staffID = $this->_getParam("staffID");
        if ($staffID != "") {
            $_SESSION['staffID'] = $staffID;
        }
        if (isset($_SESSION['staffID'])) {
            $staffID = $_SESSION['staffID'];
        } else {
            $_SESSION['staffID'] = 0;
        }
        $table = new Zend_Db_Table('recruiting_searches');
        $recruitingEmailsTable = new Zend_Db_Table('recruiting_emails');
        $foundEmailsTable = new Zend_Db_Table('recruiting_found_emails');
        $data = array('accepted' => '1');
        $wsclause = "id = '$wsid'";
        $table->update($data, $wsclause);
        $select = $foundEmailsTable->select();
        $select->where("website_id='$wsid'");
        $emailsToMigrate = $foundEmailsTable->fetchAll($select);
        foreach ($emailsToMigrate as $email) {
            if ($emta == 0 || $emta == $email->id){
            $data = array(
                'website' => "$ws",
                'email' => "$email->email",
                'staff_id' => "$staffID",
                'created_by' => Zend_Auth::getInstance()->getIdentity()->email,
                'date_created' => new Zend_Db_Expr('NOW()'),
                'date_updated' => new Zend_Db_Expr('NOW()')
            );
            try {
                $recruitingEmailsTable->insert($data);
            } catch (Exception $e) {
                
            }
            }
        }
        $jsonResponse = array();
        $this->getResponse()->setBody(Zend_Json::encode($jsonResponse))->setHeader('content-type', 'application/json', true);
    }
    public function requestrmwsAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $wsid = $this->_getParam("websiteID");
        $staffID = 0;
        $staffID = $this->_getParam("staffID");
        if ($staffID != "") {
            $_SESSION['staffID'] = $staffID;
        }
        if (isset($_SESSION['staffID'])) {
            $staffID = $_SESSION['staffID'];
        } else {
            $_SESSION['staffID'] = 0;
        }
        $table = new Zend_Db_Table('recruiting_searches');
        $data = array('accepted' => '0');
        $wsclause = "id = '$wsid'";
        $table->update($data, $wsclause);
        $jsonResponse = array();
        $this->getResponse()->setBody(Zend_Json::encode($jsonResponse))->setHeader('content-type', 'application/json', true);
    }
    public function emailsnotfoundAction(){
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $table = new Zend_Db_Table('recruiting_searches');
            $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                    ->setIntegrityCheck(false);
            
            $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");
            $iSortCol_0 = (int)$this->getRequest()->getParam("iSortCol_0");
            
            $orderArray = array();
            if (is_numeric($iSortCol_0)):
	            switch ($iSortCol_0):
		            case 1 :
		            	$orderArray = array('recruiting_searches.website '. $sSortDir_0);
		            	break;
                            case 3 :
		            	$orderArray = array('recruiting_searches.alexa '. $sSortDir_0);
		            	break;
                            default:
                                $orderArray = array('alexa '. $sSortDir_0);
		            	break;
            	endswitch;
            endif;
            $aaData=array();
            $subSelect = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                    ->setIntegrityCheck(false);
            $subSelect->columns('recruiting_searches.id')
                    ->join('recruiting_found_emails AS rfe','recruiting_searches.base_website_id IS NULL AND recruiting_searches.alexa!=0 AND recruiting_searches.id=rfe.website_id',array(
                'id'=>'recruiting_searches.id'
            ))
                 ->where("recruiting_searches.base_website_id IS NULL AND recruiting_searches.id=rfe.website_id");
            $rows = $table->fetchAll($subSelect);
            $ids = "(";
            $first = true;
            foreach ($rows as $rowItem){
                if (!$first){
                    $ids=$ids.",";
                }
                $ids=$ids.$rowItem->id;
                $first = false;
            }
            $ids=$ids.")";
            
            $select->join('recruiting_searches AS s2', 'recruiting_searches.id=s2.base_website_id', array('website_id' => 'recruiting_searches.id',
                        "base_website" => "recruiting_searches.website",
                        'full_website' => 's2.website',
                        'full_website_id'=>'s2.id',
                        'alexa' => 'recruiting_searches.alexa'
                        ))
                    ->where("(s2.website LIKE '%advertise%' OR s2.website LIKE '%contact%') AND recruiting_searches.alexa!=0 AND s2.website LIKE concat('%',recruiting_searches.website,'%') AND recruiting_searches.accepted IS NULL AND (recruiting_searches.id) NOT IN $ids")
                    //->order(array('alexa ASC', 'website'))
                    ->order($orderArray)
                    ->limit(5000)
                    //->limit((int)$_GET['iDisplayLength'],(int)$_GET['iDisplayStart'])
                    ;
            if (isset($_SESSION["minAlexa"])){
                if ($_SESSION["minAlexa"]!=""){
                    $select->where('recruiting_searches.alexa > '.$_SESSION["minAlexa"]);
                }
            }
            if (isset($_SESSION["maxAlexa"])){
                if ($_SESSION["maxAlexa"]!=""){
                    $select->where('recruiting_searches.alexa < '.$_SESSION["maxAlexa"]);
                }
            }
            $sSearch = $this->getRequest()->getParam("sSearch");
            
            if (strlen($sSearch)>=3):
            	$select->where("recruiting_searches.id LIKE '%".$sSearch."%' OR recruiting_searches.website LIKE '%".$sSearch."%' OR s2.website LIKE '%".$sSearch."%' OR recruiting_searches.alexa LIKE '%".$sSearch."%'");
            endif;
            $emailJoins = $table->fetchAll($select);
//            foreach ($emailJoins as $emailJoin):
//                if ($lastID != $emailJoin->website_id){
//                    $lastID = $emailJoin->website_id;
//                    $countWS++;
//                }
//            endforeach;
            
            
            $len = (int)$_GET['iDisplayLength'];
            $start = (int)$_GET['iDisplayStart'];
            $currentREC = 0;
            if (count($emailJoins) > 0):
                $currentID = 0;
                $first=true;
                $full="";
                $lastWSID = 0;
                $lastBase = "";
                $lastRmAll="";
            $lastAlexa = 0;
            foreach ($emailJoins as $emailJoin):
                if ($currentID != $emailJoin->website_id) {
                    $currentID = $emailJoin->website_id;
                    $currentREC++;
                    if (($currentREC >= $start) && ($currentREC < ($start + $len))) {
                        if (!$first) {
                            $emailData = array();
                            $emailData[0] = $lastWSID;
                            $emailData["website_id"] = $lastWSID;
                            $emailData[1] = $lastBase;
                            $emailData["base_website"] = $lastBase;
                            $emailData[2] = $full;
                            $emailData["full_website"] = $full;
                            $emailData[3] = $lastAlexa;
                            $emailData["alexa"] = $lastAlexa;
                            $emailData[4] = $lastRmAll;
                            $emailData["rmall"] = $lastRmAll;
                            $aaData[] = $emailData;
                            $full = "";
                        } else {
                            $first = false;
                            $currentID = $emailJoin->website_id;
                        }
                    }
                }
                $lastWSID = $emailJoin->website_id;
                $lastBase = "<div style=\"width: 120px; word-wrap: break-word;\">$emailJoin->base_website</div>";
                //$full = $full . $emailJoin->full_website . "<br/>";
                $full=$full."<div id=\"subLinkDiv$emailJoin->full_website_id\" style=\"width:330px;\">
                            <table><tr><td><div style=\"word-wrap: break-word; width: 230px\">$emailJoin->full_website</div></td>
                                    <td style=\"width: 55px;\">
                                    <button onclick=\"deleteSubLink($emailJoin->full_website_id)\">REMOVE</button> </td></tr></table></div>";
                $lastAlexa = $emailJoin->alexa;
                $currentID = $emailJoin->website_id;
                $lastRmAll = "<div id=\"rnews$emailJoin->website_id\"><button  onclick=\"rejectNewebsite($emailJoin->website_id)\">REJECT WEBSITE</button></div>";
            endforeach;
        endif;
            $output = array(
                //"iTotalRecords" => $emailJoins[0]->countWS,
                //"iTotalDisplayRecords" => $emailJoins[0]->countWS,
                "iTotalRecords" => $currentREC,
                "iTotalDisplayRecords" => $currentREC,
                "sColumns" => 6,
                "aaData" => $aaData
            );
            
            $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    
    public function closedAction()
    {
        $filterAdmin = (int) $this->_getParam('admin');
        
        $this->view->filterClosed = $this->_getParam('filterClosed');
        $this->view->name = $this->_getParam('name');
        $this->view->startDate = $this->_getParam('from');//date('Y-m-d', time()-60*60*24*365);//$firstDate /*$this->_getParam('from')*/;
        $this->view->endDate = $this->_getParam('to');//date('Y-m-d') /*$this->_getParam('to')*/;
        
        $tableStaff = new Application_Model_DbTable_ContactNotification();
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();   
        $filterAdmin = $filterAdmin ? $dataAuth->email : '';
        
        $this->view->dataAuth = $dataAuth;
        $this->view->filterAdmin = $filterAdmin;
        $this->view->contactManager = $tableStaff->getAllData();
        $this->view->personalManager = $tableStaff->getDataByEmail($dataAuth->email);      
    }
    
    public function getAjaxClosedAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
                
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
       
        
        //                               0          1                2             3                        4                                         6                         7                      8                9 
        $aColumns = array('re.id', 're.website', 're.email', 're.closed_by', 're.date_closed', 're.closed', 're.closed_notes', 're.never_contact', 're.opportunity','re.id' );
         $Columns = array('id',      'website',    'email',   'closed_by',    'date_closed',     'closed',    'closed_notes',    'never_contact', 'opportunity', 'id');
     $likeColumns = array(0 => 're.id', 1 => 're.website', 2 => 're.email', 3 => 're.closed_by', 4 => 're.date_closed');  
       
//     'closed'
//     'closed_notes'
//     'never_contact'
         
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "re.id";
	
	/* DB table to use */
	$sTable = " recruiting_emails AS re ";
        $sJoin = " JOIN contact_notification AS s ON s.staff_id = re.staff_id ";
	        
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
				 	mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		foreach ( $likeColumns as $key => $field )
		{
			$sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
        
        if($sWhere == "") $sWhere  = " WHERE re.closed IS NOT NULL AND re.deleted=0 AND re.pending!=5 ";
                     else $sWhere .= "   AND re.closed IS NOT NULL AND re.deleted=0 AND re.pending!=5 ";          
                        
        
        $startDate = $_GET["startDate"];
        $endDate = $_GET["endDate"];
        if(strlen($startDate) AND strlen($endDate))
        {
            if($sWhere == ""){ $sWhere  = " WHERE DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' "; }
                        else { $sWhere .= " AND DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' ";   }         	
        }
        
        $filterClosed = (int) $_GET['filterClosed'];        
        if($filterClosed > 0){
            if($sWhere == "") $sWhere  = " WHERE re.closed = '".$filterClosed."' ";
                         else $sWhere .= "   AND re.closed = '".$filterClosed."' ";
        }	
        
        $account = (int) $_GET['accounts'];        
        if($account > 0){
            if($sWhere == "") $sWhere  = " WHERE s.staff_id = '".$account."' ";
                         else $sWhere .= " AND s.staff_id = '".$account."' ";
        }
                
        
	/* Individual column filtering */
	foreach( $likeColumns as $key => $field )
	{
		if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
		}
	}
		
        
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable  
                       $sJoin
                       $sWhere         
                       $sOrder
                       $sLimit
		       "; 
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	"; 
	$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error()); 
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal); 
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable  
                       $sJoin
                       $sWhere    
	               "; 
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
     
        
	while ($aRow = mysql_fetch_array($rResult))
	{
		$row = array();
		for ($i=0 ; $i<count($Columns) ; $i++)
		{
			if ($Columns[$i] == "version")
			{
				/* Special output formatting for 'version' column */
				$row[] = ($aRow[$Columns[$i]]=="0") ? '-' : $aRow[$Columns[$i]];
			}
			else if ($Columns[$i] != ' ')
			{
				/* General output */
				$row[] = $aRow[$Columns[$i]];
			}
		}
		$output['aaData'][] = $row;
	}             
       
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);        
    }     
    
    public function setclosedAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();       
        
    	$data = array();
        
        $id = (int)$this->getRequest()->getPost('id');
        $action = (int)$this->getRequest()->getPost('action');
        
        $notes = $this->getRequest()->getPost('notes');
        $notes = $notes ? trim(strip_tags($notes)) : NULL;
        $never_contact = (int) $this->getRequest()->getPost('never_contact');       
        
        $auth = Zend_Auth::getInstance()->getIdentity();
        
    	if($id && $action){
            $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();
            $tableRecruiting->update(array('closed'=>$action, 
                                           'date_closed'=>date("Y-m-d"), 
                                           'closed_by'=>$auth->name,
                                           'closed_notes'=>$notes,
                                           'never_contact'=>$never_contact), "id='".$id."'");
            $data = array('status'=>'OK');
    	}else{
            $data = array('error'=>'ERROR!!! All fields are required.');
    	}

    	$json = Zend_Json::encode($data);
    	echo $json;                
    }
    
    public function statsAction()
    {
        //$this->view->year = $this->_getParam('year');
    	//$this->view->month = $this->_getParam('month');  
        $params = array();
        $params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
        $params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));
        
        $params["end_date"] = gmdate( 'm/d/Y',time());
        $params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));
        
        $this->view->report_params = $params;       
    }    
    
    public function getStatsAjaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();       
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );

        $str = preg_split('/[-]/', date("Y-m-d"));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, $str[1], $str[2] - 1 /* day */, $str[0]));

        $start_date = gmdate('m/d/Y',strtotime($_GET['start_date']));
        $end_date = gmdate('m/d/Y',strtotime($_GET['end_date']));

        $strDate = explode("/", $start_date);
        $start_date = $strDate[2].'-'.$strDate[0].'-'.$strDate[1];
        $enDate = explode("/", $end_date);
        $end_date = $enDate[2].'-'.$enDate[0].'-'.$enDate[1];

        //re.pending=1 AND re.responded=1 AND re.never_responded=0 AND re.deleted=0 AND re.opportunity NOT IN (7, 8)

        $aColumns = array(
            's.name',
            '(SELECT COUNT(es.staff_id) FROM recruiting_emails as es WHERE es.staff_id=s.staff_id AND es.deleted=0 AND es.closed IS NULL AND es.pending=1 AND es.responded=0 AND es.never_responded=0 AND es.deleted=0 AND DATE_FORMAT(date_created, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(date_created, \'%Y-%m-%d\')<=\''.$end_date.'\') as emails_sent',
            '(SELECT COUNT(le.id) FROM recruiting_emails as le WHERE le.staff_id=s.staff_id AND le.deleted=0 AND le.pending=1 AND le.responded=1 AND le.never_responded=0 AND le.deleted=0 AND le.closed IS NULL AND DATE_FORMAT(date_created, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(date_created, \'%Y-%m-%d\')<=\''.$end_date.'\') + (SELECT COUNT(cl.id) FROM recruiting_emails as cl WHERE cl.closed_by=s.name AND cl.deleted=0 AND cl.closed IS NOT NULL AND DATE_FORMAT(date_created, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(date_created, \'%Y-%m-%d\')<=\''.$end_date.'\') AS responses',
            '(SELECT COUNT(le.id) FROM recruiting_emails as le WHERE le.staff_id=s.staff_id AND le.deleted=0 AND le.pending=1 AND le.responded=1 AND le.never_responded=0 AND le.opportunity NOT IN (7, 8) AND DATE_FORMAT(date_created, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(date_created, \'%Y-%m-%d\')<=\''.$end_date.'\') as leads',
            '(SELECT COUNT(cl.id) FROM recruiting_emails as cl WHERE cl.staff_id=s.staff_id AND cl.deleted=0 AND cl.closed IS NOT NULL AND DATE_FORMAT(date_created, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(date_created, \'%Y-%m-%d\')<=\''.$end_date.'\') as closed',
            '(SELECT COUNT(cl.id) FROM recruiting_emails as cl WHERE cl.staff_id=s.staff_id AND cl.deleted=0 AND cl.closed=1 AND cl.pending!=5 AND DATE_FORMAT(date_created, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(date_created, \'%Y-%m-%d\')<=\''.$end_date.'\') as leads_won',
            '(SELECT SUM(rs.impressions) FROM referral r LEFT JOIN referral_stat AS rs ON r.id = rs.refID WHERE r.Email = s.mail AND DATE_FORMAT(rs.query_date, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(rs.query_date, \'%Y-%m-%d\')<=\''.$end_date.'\')',
            '(SELECT ROUND(SUM(rs.revenue),2) FROM referral r LEFT JOIN referral_stat AS rs ON r.id = rs.refID WHERE r.Email = s.mail AND DATE_FORMAT(rs.query_date, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(rs.query_date, \'%Y-%m-%d\')<=\''.$end_date.'\')',
            'IF((SELECT r.id FROM referral r WHERE r.Email = s.mail AND r.primary = 1 LIMIT 1),(SELECT r.id FROM referral r WHERE r.Email = s.mail AND r.primary = 1 LIMIT 1), (SELECT r.id FROM referral r WHERE r.Email = s.mail LIMIT 1) ) as refId',
            's.staff_id'
        );
        $Columns = array( 'name', 'emails_sent', 'responses', 'leads', 'closed', 'leads_won');
        $Columns[] = '(SELECT SUM(rs.impressions) FROM referral r LEFT JOIN referral_stat AS rs ON r.id = rs.refID WHERE r.Email = s.mail AND DATE_FORMAT(rs.query_date, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(rs.query_date, \'%Y-%m-%d\')<=\''.$end_date.'\')';
        $Columns[] = '(SELECT ROUND(SUM(rs.revenue),2) FROM referral r LEFT JOIN referral_stat AS rs ON r.id = rs.refID WHERE r.Email = s.mail AND DATE_FORMAT(rs.query_date, \'%Y-%m-%d\')>=\''.$start_date.'\'AND DATE_FORMAT(rs.query_date, \'%Y-%m-%d\')<=\''.$end_date.'\')';
        $Columns[] = 'refId';
        $Columns[] = 'staff_id';
        $likeColumns = array( 0 => 's.name');

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "s.id";

        /* DB table to use */
        $sTable = "contact_notification AS s";
        $sJoin = "";
        $sGroup = "";
        //$sJoin = " LEFT JOIN recruiting_emails AS es ON (es.staff_id = s.id AND es.pending=1 AND es.responded=0 AND es.never_responded=0 AND es.deleted=0) ";
        //$sJoin.= " LEFT JOIN recruiting_emails AS le ON (le.staff_id = s.id AND le.pending=1 AND le.responded=1 AND le.never_responded=0 AND le.deleted=0 AND le.closed IS NULL) ";
        //$sGroup = " GROUP BY s.id ";
        /*
         * Paging
         */
        $sLimit = "";
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
                mysql_real_escape_string( $_GET['iDisplayLength'] );
        }


        /*
         * Ordering
         */
        $sOrder = "";
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
                        mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            $sWhere = "WHERE (";
            foreach ( $likeColumns as $key => $field )
            {
                $sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }


        /* Individual column filtering */
        foreach( $likeColumns as $key => $field )
        {
            if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
                $sJoin
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		";
        $tmp = $sQuery;
        $rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());

        /* Data set length after filtering */
        $sQuery = "
		SELECT FOUND_ROWS()
	";
        $rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        $aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
        $iFilteredTotal = $aResultFilterTotal[0];

        /* Total data set length */
        $sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
                $sJoin
                $sWhere

	";
        $rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        $aResultTotal = mysql_fetch_array($rResultTotal);
        $iTotal = $aResultTotal[0];


        /*
         * Output
         */
        $output = array(
            "sql"=>$tmp,
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );


        while ( $aRow = mysql_fetch_array( $rResult ) )
        {
            $row = array();
            for ( $i=0 ; $i<count($Columns) ; $i++ )
            {
                if ( $Columns[$i] == "version" )
                {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[ $Columns[$i] ]=="0") ? '-' : $aRow[ $Columns[$i] ];
                }
                else if ( $Columns[$i] != ' ' )
                {
                    /* General output */
                    $row[] = $aRow[ $Columns[$i] ];
                }
            }
            $output['aaData'][] = $row;
        }

        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
    
    public function ajaxGetSubAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();       

        $tableSub = new Application_Model_DbTable_Sub();
        
        $dataSub = $tableSub->getAllData();
      
    	$this->getResponse()->setBody(Zend_Json::encode($dataSub))->setHeader('content-type', 'application/json', true);
    }    
    
    public function editEmailAction()
    {
        $this->_helper->layout()->disableLayout(); 
        
           $session = new Zend_Session_Namespace('Default');
        if($session->message){ $this->view->message = $session->message;
                                                unset($session->message);
        }
 
        $recruitingID = (int) $this->_getParam('recruiting_id');        
 
        $tableStaff = new Application_Model_DbTable_ContactNotification();     
        $tableMail = new Application_Model_DbTable_MailRecruiting();
        $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();         
 
        $dataAuth = Zend_Auth::getInstance()->getIdentity();

        $dataMail = $tableMail->getDataByRecruiting($recruitingID);    
        $dataRecruiting = $tableRecruiting->getDataByID($recruitingID); 
        $dataStaff = $tableStaff->getAllData();
        
        if($this->getRequest()->isPost() && filter_var($this->_getParam('email'), FILTER_VALIDATE_EMAIL)){
            
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                
            $sendEmail = (int) $this->_getParam('sendEmail');
            $staff_id = (int) $this->_getParam('staff'); 
            $subject = $this->_getParam('subject');
            $text = $this->_getParam('text');   
            $email = $this->_getParam('email');
            
            if($sendEmail){

                        $dataStaffID = array();
                foreach($dataStaff as $iter){ $dataStaffID[$iter['staff_id']] = $iter; }

                $body = $text.$dataStaffID[$staff_id]['signature'];

                $classMail = new Zend_Mail();
                $classMail->setFrom($dataStaffID[$staff_id]['mail'], $dataStaffID[$staff_id]['name']);
                $classMail->addTo($dataRecruiting['email'], $dataRecruiting['website']);
                $classMail->setSubject($subject);
                $classMail->setBodyHtml($body);
                $classMail->send();

                $dataStat['mail']    = $dataRecruiting['email'];
                $dataStat['subject'] = $subject;
                $dataStat['text']    = $body;
                $dataStat['author']  = $dataAuth->email;
                $dataStat['staff_id']    = $staff_id;
                $dataStat['recruiting_id'] = $dataRecruiting['id'];
                $dataStat['template_id'] = 0;
                $dataStat['order_id']    = 0;
                $dataStat['created']     = date("Y-m-d H:i:s");
                $tableMail->insert($dataStat);
            
            }
    	    	
            $updateRecruiting['email'] = $email;	
            $updateRecruiting['staff_id'] = $staff_id;	    	
            $updateRecruiting['updated_by'] = $dataAuth->email;
           
            $whereRecruiting = $dbAdapter->quoteInto('id = ?', $dataRecruiting['id']);
            $tableRecruiting->update($updateRecruiting, $whereRecruiting);          
            
            if($sendEmail){ $session->message = 'Data has been updated! And Email sent.';
                            $this->_redirect('/administrator/recruiting/edit-email/recruiting_id/'.$recruitingID); } 
                      else{ $this->view->close = true; } 
        }                
              
        $this->view->data = $dataMail;
        $this->view->staff = $dataStaff;    
        $this->view->recruiting = $dataRecruiting;
    } 
    
    public function manuallyNotifiAction()
    {
        $this->_helper->layout()->disableLayout();
        
           $session = new Zend_Session_Namespace('Default');
        if($session->message){ $this->view->message = $session->message;
                                                unset($session->message);
        }
        
        $orderID = (int) $this->_getParam('order_id');
        $recruitingID = (int) $this->_getParam('recruiting_id');        
        
        $tableSub = new Application_Model_DbTable_Sub();
        $tableStaff = new Application_Model_DbTable_ContactNotification();
        $tableReferral = new Application_Model_DbTable_Referral();
        $tableMail = new Application_Model_DbTable_MailRecruiting();
        $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();
        $tableTemplate = new Application_Model_DbTable_RecruitingTemplate();        
 
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        $dataTemplate = $tableTemplate->getDataByOrder($orderID);
        $dataMail = $tableMail->getDataByRecruiting($recruitingID);    
        $dataRecruiting = $tableRecruiting->getDataByID($recruitingID); 
        $dataStaff = $tableStaff->getAllData();
        $dataSub = $tableSub->getAllData();
        
        if($dataRecruiting['manually']){            
            
            $tmpTemplate[] = array('id' => '',
                                    'name' => '',
                                    'name_label' => 'No Template',
                                    'content' => '');

            foreach($dataTemplate as $iter){ $tmpTemplate[] = $iter; }
            
            $dataTemplate = $tmpTemplate;
        }  
        
        if($this->getRequest()->isPost()){
            
            $initial = $this->_getParam('initial');
            if(strlen($initial))
            {
                $recruiting_update_data = array('pending' => 0);
                $tableRecruiting->update($recruiting_update_data, 'id = '.$recruitingID);    
            }
            
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                
            $template_id = (int) $this->_getParam('template');
            $mark_only = (int) $this->_getParam('mark_only');
            $staff_id = (int) $this->_getParam('staff');
            $sub_id = (int) $this->_getParam('sub');
            $subject = $this->_getParam('subject');
            $text = $this->_getParam('text');  
            $messageSent = '';

            if($mark_only == 0)
            {
                $replaceTags = $tableRecruiting->getReplaceTags();
                $staffColumns = $tableRecruiting->getReplaceTags(true,"staff"); 

                        $dataStaffID = array();
                foreach($dataStaff as $iter){ $dataStaffID[$iter['staff_id']] = $iter; }

                         $staffValues = array();
                foreach($staffColumns as $staffColumn){ $staffValues[] = $dataStaffID[$staff_id][$staffColumn]; }

                        $emailValues = array();
                        $emailsColumns = $tableRecruiting->getReplaceTags(true,"recruiting_emails");        
                foreach($emailsColumns as $emailsColumn){ $emailValues[] = $dataRecruiting[$emailsColumn]; }
                $replaceValues = array_merge($staffValues, $emailValues);


                $subject = str_replace($replaceTags,$replaceValues,$subject);
                $body = str_replace($replaceTags,$replaceValues,$text); 

                   $dataReferral = $tableReferral->getPrimaryDataByEmail($dataStaffID[$staff_id]['mail']);
                if($dataReferral){ $body = str_replace('{staff_ref_id}', $dataReferral['id'], $body); }
                             else{ $body = str_replace('ref/{staff_ref_id}', '', $body); }
         
                             
                $classMail = new Zend_Mail();
                $classMail->setFrom($dataStaffID[$staff_id]['mail'], $dataStaffID[$staff_id]['name']);
                $classMail->addTo($dataRecruiting['email'], $dataRecruiting['website']);
                $classMail->setSubject($subject);
                $classMail->setBodyHtml($body);
                $classMail->send();

                $dataStat['mail']    = $dataRecruiting['email'];
                $dataStat['subject'] = $subject;
                $dataStat['text']    = $body;
                $dataStat['author']  = $dataAuth->email;
                $dataStat['staff_id']    = $staff_id;
                $dataStat['recruiting_id'] = $dataRecruiting['id'];
                $dataStat['template_id'] = $template_id;
                $dataStat['order_id']    = $orderID;
                $dataStat['created']     = date("Y-m-d H:i:s");
                $tableMail->insert($dataStat);
                
                $messageSent = ' and Email sent.';
            }

            switch($orderID){
                case 1 : $updateRecruiting['pending'] = 1; break; //initial try
                case 4 : $updateRecruiting['date_24h_mail'] = date('Y-m-d H:i:s'); break; //24 hour
                case 2 : $updateRecruiting['followed_up'] = 1; break; //follow up try
                case 3 : $updateRecruiting['final'] = 1; break; //final try
                default: break;
            }		    	

            $updateRecruiting['staff_id'] = $staff_id;
            $updateRecruiting['sub_id'] = $sub_id;		    	
            $updateRecruiting['date_updated'] = date("Y-m-d H:i:s");
            $updateRecruiting['updated_by'] = $dataAuth->email;
            $updateRecruiting['last_email'] = date("Y-m-d H:i:s");        
            $whereRecruiting = $dbAdapter->quoteInto('id = ?', $dataRecruiting['id']);
            $tableRecruiting->update($updateRecruiting, $whereRecruiting);             
     
            $session->message = 'Data has been updated!'.$messageSent;
            $this->_redirect('/administrator/recruiting/manually-notifi/order_id/'.$orderID.'/recruiting_id/'.$recruitingID);
        }
                
        $this->view->sub = $dataSub;                
        $this->view->data = $dataMail;
        $this->view->order = $orderID;
        $this->view->staff = $dataStaff;
        $this->view->template = $dataTemplate;
        $this->view->recruiting = $dataRecruiting;
        
    }     
    
    public function stageOpportunitiesAction()
    {      
        $this->_helper->layout()->disableLayout(); // disable layout
           
        $startOpportunity = (int) $this->_getParam('start');
        $id = (int) $this->_getParam('id');
        $reopen = (int) $this->_getParam('reopen');
        
        
        $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();
        
        $closeWindow = false;
        $dataRecruiting = $tableRecruiting->getDataByID($id);        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        if($startOpportunity){ $dataRecruiting['opportunity'] = $startOpportunity; }
        
        if($reopen){
            
            //$dataRecruiting['opportunity'] = 4;
            $dataRecruiting['opportunity'] = 5;
            $dataUpdate = array(
                    'opportunity_reg' => $this->_getParam('registred'),
                    'opportunity' => 5,
                    'last_updated' => date("Y-m-d H:i:s"),
                    'opportunity_by' => $dataAuth->name);
            $tableRecruiting->update($dataUpdate, 'id = '.$dataRecruiting['id']);
            //$closeWindow = true;
        }
        
        if($this->getRequest()->isPost()){
              
            switch($dataRecruiting['opportunity']){                
                
                case 0 : $dataUpdate = array('opportunity' => 1,
                                             'responded' => 1,
                                             'never_responded' => 0, 
                                             'date_opportunity'=>date("Y-m-d H:i:s"),
                                             'last_updated' => date("Y-m-d H:i:s"),
                                             'opportunity_by' => $dataAuth->name);
                    
                         $tableRecruiting->update($dataUpdate, 'id = '.$dataRecruiting['id']);
                         $dataRecruiting['opportunity'] = 1; 
                         $closeWindow = true;
                         break;
                case 1 : $dataUpdate = array(
                            'opportunity'  => 2,
                            'opportunity_avgCPM' => $this->_getParam('avgCPM'),
                            'opportunity_imp'    => $this->_getParam('impression'),
                            'opportunity_net'    => $this->_getParam('network'),
                            'opportunity_unit'   => $this->_getParam('unit'),
                            'last_updated' => date("Y-m-d H:i:s"),
                            'opportunity_by' => $dataAuth->name);
                    
                         $tableRecruiting->update($dataUpdate, 'id = '.$dataRecruiting['id']);
                         $dataRecruiting['opportunity_avgCPM'] = $dataUpdate['opportunity_avgCPM'];
                         $dataRecruiting['opportunity'] = 2;
                         $closeWindow = false;
                         break;
                case 2 : 
                case 3 :    
                         if($this->_getParam('emailed')){
                             $dataUpdate = array(
                                   'opportunity' => 4, 
                                   'opportunity_mail' => $this->_getParam('emailed'),
                                   'last_updated' => date("Y-m-d H:i:s"),
                                   'opportunity_by' => $dataAuth->name); 

                             $tableRecruiting->update($dataUpdate, 'id = '.$dataRecruiting['id']);
                             $dataRecruiting['opportunity'] = 4; 
                         }   $closeWindow = true;
                         break;
                case 4 : if($this->_getParam('registred')){                    
                
                             $dataRecruiting['opportunity'] = 5;
                             $dataUpdate = array(                                 
                                   'opportunity_reg' => $this->_getParam('registred'),
                                   'opportunity' => 5,
                                   'last_updated' => date("Y-m-d H:i:s"),
                                   'opportunity_by' => $dataAuth->name); 
                         } else {
                             
                             $dataUpdate = array('last_updated' => date("Y-m-d H:i:s"),
                                                 'opportunity_not_reg' => ($dataRecruiting['opportunity_not_reg'] + 1));
                             
                             $dataRecruiting['opportunity_not_reg'] = $dataUpdate['opportunity_not_reg'];
                         }
                         $tableRecruiting->update($dataUpdate, 'id = '.$dataRecruiting['id']);                          
                         $closeWindow = true;
                         break;
                         
                default : $closeWindow = false;
                          break;                
            }
        }
        
        $this->view->close = $closeWindow;
        $this->view->data = $dataRecruiting;  
    }       
    
    public function setManualAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(); 
        
        $value = (int) $this->_getParam('value');
        $recruitingID = (int) $this->_getParam('id');
        
        $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();
        $result = $tableRecruiting->update(array('manually' => $value), 'id = '.$recruitingID);
        
        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function deleteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();         
    
        $recruitingID = (int) $this->_getParam('recruiting_id');
        
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                
        $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $whereRecruiting = $dbAdapter->quoteInto('id = ?', $recruitingID);
        
        $dataUpdate = array('deleted' => 1,
                            'date_deleted' => date("Y-m-d H:i:s"),
                            'deleted_by' => $dataAuth->email);
        
        $result = $tableRecruiting->update($dataUpdate, $whereRecruiting);
        
        
        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }   
    
    public function followUpManualAction()
    {
        $this->_helper->layout()->disableLayout();
        
        $id = (int) $this->_getParam('id');
        $date = $this->_getParam('date');
        $date = $date ? date('Y-m-d', strtotime($date)) : NULL;
        
        $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();
                        
        if($this->getRequest()->isPost()){
            
            if($id && $date){

                $dbAdapter = Zend_Db_Table::getDefaultAdapter();                

                $whereRecruiting = $dbAdapter->quoteInto('id = ?', $id);

                $dataUpdate = array('date_follow_up_manual' => $date);

                $result = $tableRecruiting->update($dataUpdate, $whereRecruiting);

                $this->view->message = $result ? '<div class="message">Data was saved, Please close window !</div>' : '<div class="error">Request return error, Please try again !</div>';

            } else { $this->view->message = '<div class="error">Data not valid, Please try again !</div>'; } 
            
        } elseif($this->_getParam('clear')){
            
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();                

            $whereRecruiting = $dbAdapter->quoteInto('id = ?', $id);

            $dataUpdate = array('date_follow_up_manual' => NULL);

            $result = $tableRecruiting->update($dataUpdate, $whereRecruiting);
            
            $this->view->message = $result ? '<div class="message">Data was cleared, Please close window !</div>' : '<div class="error">Request return error, Please try again !</div>';
        }
        
        $this->view->data = $tableRecruiting->getDataByID($id);
    }
    
    public function contactAction() {
        
        set_time_limit (0);
        
        $this->_helper->layout()->disableLayout();
        
           $session = new Zend_Session_Namespace('Default');
        if($session->message){ $this->view->message = $session->message;
                                                unset($session->message);
        }
        
        $recruitingID = (int) $this->_getParam('id');
        
    	$tableRecruiting = new Application_Model_DbTable_RecruitingEmails(); 
        $tableMail = new Application_Model_DbTable_MailRecruiting();
        $tableStaff = new Application_Model_DbTable_ContactNotification();             
        
        $dataRecruiting = $tableRecruiting->getDataByID($recruitingID);  
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        $dataStaff = $tableStaff->getAllData();              
        
                $dataStaffID = array();
        foreach($dataStaff as $iter){ $dataStaffID[$iter['staff_id']] = $iter; }        

        if($this->getRequest()->isPost()){
            
            $status = (int) $this->_getParam('status');
            $staff_id = (int) $this->_getParam('staff');
            $client = (int) $this->_getParam('client');
            $bcc = (int) $this->_getParam('bcc');
            
            $to = trim(strip_tags($this->_getParam('to')));
            $subject = $this->_getParam('subject');
            $text = $this->_getParam('text');            
                             
                    $arrEmailvalid = array();
                    $arrEmail = preg_split("[,]", $to);
            foreach($arrEmail as $iterEmail){
                
                              $iterEmail = trim($iterEmail);
                if(filter_var($iterEmail, FILTER_VALIDATE_EMAIL)){ $arrEmailvalid[] = $iterEmail; }     
                
            } $to = count($arrEmailvalid) ? implode(",", $arrEmailvalid) : "";
   
            switch($status){
               case 1 :                 
                    
                    $text = $text.$dataStaffID[$staff_id]['signature'];
 
                    foreach($arrEmailvalid as $iterEmail){
                   
                        $classMail = new Zend_Mail ();
                        $classMail->setFrom($dataStaffID[$staff_id]['mail'], $dataStaffID[$staff_id]['name']);
                        $classMail->addTo($iterEmail, $dataRecruiting['website']);
                        $classMail->setSubject($subject);
                        $classMail->setBodyHtml($text);

                        if($bcc) $classMail->addBcc($dataStaffID[$staff_id]['mail']);

                        $classMail->send();  
                    }
                    
                    $session->message = 'Data has been save, and Email sent !';
                   break;            
               case 2 :
                    $session->message = 'Data has been save, without Email !';
                    $subject =  $client ? 'Note (Client\'s Response)' : 'Note';
                    $text = $text ? $text : ''; 
                   break;
               default : 
                    $session->message = 'Request return error, Please try again !';
                    $this->_redirect('/administrator/recruiting/contact/id/'.$recruitingID);
                   break;
            }
            
            $dataInsert = array(
                    'mail'    => $to,
                    'subject' => $subject,
                    'text'    => $text,
                    'author'  => $dataAuth->email,
                    'type'    => 1,
                    'recruiting_id' => $dataRecruiting['id'],
                    'staff_id' => $dataStaffID[$staff_id]['staff_id'],
                    'created'  => date('Y-m-d H:i:s')
            );
            $tableMail->insert($dataInsert);
            $tableRecruiting->update(array('last_updated'=>date("Y-m-d H:i:s")), "id='".$recruitingID."'");
            $this->_redirect('/administrator/recruiting/contact/id/'.$recruitingID);         
        } 
        
        $email = NULL;
        $subject = NULL;
        $dataContacted = $tableMail->getDataByRecruitingType($recruitingID, 1);        
        foreach($dataContacted as $key => $iter){            
             if($iter['subject'] != 'Note' &&
                $iter['subject'] != 'Note (Client\'s Response)'){
            
                     $subject = $iter['subject'];
                     $email = $iter['mail']; break;
             }            
        }        
        $subject = $subject ? $subject : 'Re: Unique Opportunity For '.$dataRecruiting['website']; 
        $email = $email ? $email : $dataRecruiting['email'];        
        
        $this->view->to = $email;
        $this->view->subject = $subject;
        $this->view->dataStaff = $dataStaffID;
        $this->view->dataRecruiting = $dataRecruiting;        
        $this->view->data = $tableMail->getDataByRecruiting($recruitingID);
    }   
    
    public function updateOpportunityAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
        $data = array();
        $id = (int)$this->getRequest()->getPost('id');
        $action = (int)$this->getRequest()->getPost('action');
        $note = $this->_getParam('note');
        $auth = Zend_Auth::getInstance()->getIdentity();
    
        if($id && $action){
            $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();
            $tableRecruiting->update(array('responded'=>1,
                    'never_responded'=>0,
                    'opportunity'=>$action,
                    'last_updated'=>date("Y-m-d H:i:s"),
                    'opportunity_note'=>$note ? $note : NULL,
                    ), "id='".$id."'");
    
            $data = array('status'=>'OK');
        }else{
            $data = array('error'=>'ERROR!!! All fields are required.');
        }
    
        $json = Zend_Json::encode($data);
        echo $json;
    }

    public function ajaxGetStatSubAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staffID = (int) $this->_getParam('id');

        $start_date = $this->_getParam('start_date');
        $start_date = date('Y-m-d', strtotime($start_date));

        $end_date = $this->_getParam('end_date');
        $end_date = date('Y-m-d', strtotime($end_date));

        $tableStaff = new Application_Model_DbTable_ContactNotification();
        $tableRecruiting = new Application_Model_DbTable_RecruitingEmails();

        $dataStaff = $tableStaff->getDataByStaffID($staffID);
        $dataStat = $tableRecruiting->getStatSub($staffID, $dataStaff['name'], $start_date, $end_date);

        $this->getResponse()->setBody(Zend_Json::encode($dataStat))->setHeader('content-type', 'application/json', true);

    }

    public function closedOpportunitiesAction()
    {
        $filterAdmin = (int) $this->_getParam('admin');

        $this->view->startDate = $this->_getParam('from');//date('Y-m-d', time()-60*60*24*365);//$firstDate /*$this->_getParam('from')*/;
        $this->view->endDate = $this->_getParam('to');//date('Y-m-d') /*$this->_getParam('to')*/;
        $this->view->manager = $this->_getParam('name');

        $tableStaff = new Application_Model_DbTable_ContactNotification();

        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        $filterAdmin = $filterAdmin ? $dataAuth->email : '';

        $this->view->dataAuth = $dataAuth;
        $this->view->filterAdmin = $filterAdmin;
        $this->view->contactManager = $tableStaff->getAllData();
        $this->view->personalManager = $tableStaff->getDataByEmail($dataAuth->email);
    }

    public function getAjaxClosedOpportunitiesAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        /* MySQL connection */
        $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

        $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

        mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );


        //                               0           1                2               3                                                4                                                     5                     6                           7                        8
        $aColumns = array('re.id', 're.website', 're.email', 're.opportunity_by', 'DATE_FORMAT(re.last_updated, "%Y-%m-%d")', 're.opportunity', 're.opportunity_note', 're.never_contact', 're.id' );
        $Columns = array('id',      'website',    'email',   'opportunity_by',   'DATE_FORMAT(re.last_updated, "%Y-%m-%d")',   'opportunity',    'opportunity_note',    'never_contact', 'id' );
        $likeColumns = array(0 => 're.id', 1 => 're.website', 2 => 're.email', 3 => 're.opportunity_by', 4 => 'DATE_FORMAT(re.date_opportunity, "%Y-%m-%d")');

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "re.id";

        /* DB table to use */
        $sTable = " recruiting_emails AS re ";
        $sJoin = " JOIN contact_notification AS s ON s.staff_id = re.staff_id ";

        /*
         * Paging
         */
        $sLimit = "";
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
                mysql_real_escape_string( $_GET['iDisplayLength'] );
        }


        /*
         * Ordering
         */
        $sOrder = "";
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
                        mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            $sWhere = "WHERE (";
            foreach ( $likeColumns as $key => $field )
            {
                $sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        if($sWhere == "") $sWhere  = " WHERE re.deleted=0 AND re.pending!=5 AND re.opportunity IN (7, 8) "; /*re.closed IS NOT NULL AND*/
        else $sWhere .= "   AND re.deleted=0 AND re.pending!=5 AND re.opportunity IN (7, 8) "; /*re.closed IS NOT NULL AND*/


        $startDate = $_GET["startDate"];
        $endDate = $_GET["endDate"];
        if(strlen($startDate) AND strlen($endDate))
        {
            if($sWhere == ""){ $sWhere  = " WHERE DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' "; }
            else { $sWhere .= " AND DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' ";   }
        }

        $filterOpportunity = (int) $_GET['filterOpportunity'];
        if($filterOpportunity > 0){
            if($sWhere == "") $sWhere  = " WHERE re.opportunity = '".$filterOpportunity."' ";
            else $sWhere .= "   AND re.opportunity = '".$filterOpportunity."' ";
        }

        $account = (int) $_GET['accounts'];
        if($account > 0){
            if($sWhere == "") $sWhere  = " WHERE s.staff_id = '".$account."' ";
            else $sWhere .= " AND s.staff_id = '".$account."' ";
        }


        /* Individual column filtering */
        foreach( $likeColumns as $key => $field )
        {
            if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
            }
        }


        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
                       $sJoin
                       $sWhere
                       $sOrder
                       $sLimit
		       ";
        $rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        /* Data set length after filtering */
        $sQuery = "
		SELECT FOUND_ROWS()
	";
        $rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        $aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
        $iFilteredTotal = $aResultFilterTotal[0];

        /* Total data set length */
        $sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
                       $sJoin
                       $sWhere
	               ";
        $rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        $aResultTotal = mysql_fetch_array($rResultTotal);
        $iTotal = $aResultTotal[0];


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );


        while ($aRow = mysql_fetch_array($rResult))
        {
            $row = array();
            for ($i=0 ; $i<count($Columns) ; $i++)
            {
                if ($Columns[$i] == "version")
                {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$Columns[$i]]=="0") ? '-' : $aRow[$Columns[$i]];
                }
                else if ($Columns[$i] != ' ')
                {
                    /* General output */
                    $row[] = $aRow[$Columns[$i]];
                }
            }
            $output['aaData'][] = $row;
        }

        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }

    public function wonAction()
    {
        $filterAdmin = (int) $this->_getParam('admin');

        $this->view->name = $this->_getParam('name');
        $this->view->startDate = $this->_getParam('from');//date('Y-m-d', time()-60*60*24*365);//$firstDate /*$this->_getParam('from')*/;
        $this->view->endDate = $this->_getParam('to');//date('Y-m-d') /*$this->_getParam('to')*/;

        $tableStaff = new Application_Model_DbTable_ContactNotification();

        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        $filterAdmin = $filterAdmin ? $dataAuth->email : '';

        $this->view->dataAuth = $dataAuth;
        $this->view->filterAdmin = $filterAdmin;
        $this->view->contactManager = $tableStaff->getAllData();
        $this->view->personalManager = $tableStaff->getDataByEmail($dataAuth->email);
    }

    public function getAjaxWonAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        /* MySQL connection */
        $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

        $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

        mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );


        //                   0          1             2            3                4                5              6                   7
        $aColumns = array('re.id', 're.website', 're.email', 're.closed_by', 're.date_closed', 're.closed', 're.closed_notes', 're.never_contact', 're.date_opportunity' );
        $Columns = array('id',      'website',    'email',   'closed_by',    'date_closed',     'closed',    'closed_notes',    'never_contact' , 'date_opportunity');
        $likeColumns = array(0 => 're.id', 1 => 're.website', 2 => 're.email', 3 => 're.closed_by', 4 => 're.date_closed');

        //     'closed'
        //     'closed_notes'
        //     'never_contact'

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "re.id";

        /* DB table to use */
        $sTable = " recruiting_emails AS re ";
        $sJoin = " JOIN contact_notification AS s ON s.staff_id = re.staff_id ";

        /*
         * Paging
         */
        $sLimit = "";
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
            $sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
                mysql_real_escape_string( $_GET['iDisplayLength'] );
        }


        /*
         * Ordering
         */
        $sOrder = "";
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
            {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
                        mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
            $sWhere = "WHERE (";
            foreach ( $likeColumns as $key => $field )
            {
                $sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }

        if($sWhere == "") $sWhere  = " WHERE  re.deleted=0 AND re.pending!=5 AND re.opportunity = 7 ";
        else $sWhere .= "   AND re.deleted=0 AND re.pending!=5 AND re.opportunity = 7 ";


        $startDate = $_GET["startDate"];
        $endDate = $_GET["endDate"];
        if(strlen($startDate) AND strlen($endDate))
        {
            if($sWhere == ""){ $sWhere  = " WHERE DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' "; }
            else { $sWhere .= " AND DATE_FORMAT(re.date_created, '%Y-%m-%d') >= '".$startDate."' AND DATE_FORMAT(re.date_created, '%Y-%m-%d') <= '".$endDate."' ";   }
        }

        $account = (int) $_GET['accounts'];
        if($account > 0){
            if($sWhere == "") $sWhere  = " WHERE s.staff_id = '".$account."' ";
            else $sWhere .= " AND s.staff_id = '".$account."' ";
        }


        /* Individual column filtering */
        foreach( $likeColumns as $key => $field )
        {
            if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = "WHERE ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
            }
        }


        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
    		FROM   $sTable
    		$sJoin
    		$sWhere
    		$sOrder
    		$sLimit
    		";
        $rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        $sql = $sQuery;
        /* Data set length after filtering */
        $sQuery = "
		SELECT FOUND_ROWS()
    		";
        $rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        $aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
        $iFilteredTotal = $aResultFilterTotal[0];

        /* Total data set length */
        $sQuery = "
		SELECT COUNT(".$sIndexColumn.")
    		    FROM   $sTable
    		    $sJoin
    		    $sWhere
    		    ";
        $rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        $aResultTotal = mysql_fetch_array($rResultTotal);
        $iTotal = $aResultTotal[0];


        /*
     * Output
     */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "sql" => $sql,
            "aaData" => array()
        );


        while ($aRow = mysql_fetch_array($rResult))
        {
            $row = array();
            for ($i=0 ; $i<count($Columns) ; $i++)
            {
                if ($Columns[$i] == "version")
                {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$Columns[$i]]=="0") ? '-' : $aRow[$Columns[$i]];
                }
                else if ($Columns[$i] != ' ')
                {
                    /* General output */
                    if($aColumns[$i] == 're.date_opportunity')
                        $row[] = date('Y-m-d',strtotime($aRow[$Columns[$i]]));
                    else
                        $row[] = $aRow[$Columns[$i]];

                }
            }
            $output['aaData'][] = $row;
        }

        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }
}
