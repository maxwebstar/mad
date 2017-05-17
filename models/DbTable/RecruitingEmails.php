<?php

class Application_Model_DbTable_RecruitingEmails extends Zend_Db_Table_Abstract
{
    protected $_name = 'recruiting_emails';
    
    public function getReplaceTags($columns=false, $table=NULL){
    	
		$staffTable = new Zend_Db_Table('staff');
		
		$metadata = $this->_db->describeTable('staff');
		$columnNames = array_keys($metadata);
		
		$meta = array("id","hidden","showorder","date_created","created_by","date_updated","updated_by","deleted","date_deleted","deleted_by","date_24h_mail","last_email");
		
		$staffCols = $staffTable->info(Zend_Db_Table_Abstract::COLS);
		$emailCols = $this->info(Zend_Db_Table_Abstract::COLS);

		$staffCols = array_diff($columnNames,$meta);
		$emailsCols = array_diff($emailCols,$meta);
		
		$return = array();
		
		if ($columns):
		
			if ($table == "staff" ):
				$return = $staffCols;
			elseif ($table =="recruiting_emails"):
				$return = $emailsCols;
			endif;
		
		else :
				
			function prependTableNameToColumnName(&$item, $key, $table){
				$item = "{". "$table"."_"."$item" . "}";
			}
			
			array_walk($staffCols,"prependTableNameToColumnName","staff");
			array_walk($emailsCols,"prependTableNameToColumnName","recruiting_emails");
			
			$return = array_merge($staffCols, $emailsCols);
			
		endif;

		return $return;
    }

    public function send($staff_id, $template_id, $sub_id){

    	$staffTable = new Zend_Db_Table('staff');    	
    	$templateTable = new Zend_Db_Table('recruiting_email_templates'); 
        
        $tableReferral = new Application_Model_DbTable_Referral();
        $tableMail = new Application_Model_DbTable_MailRecruiting();
        
        $staff = $staffTable->find($staff_id)->current();
        $template = $templateTable->find($template_id)->current();
        $dataReferral = $tableReferral->getPrimaryDataByEmail($staff['email']); 
    	 
    	$response = array();
    	$data = array();
    	
    	$select = $this->select();  
        $select->where($this->getWhereForSend($template->order, $staff_id));

    	$emails = $this->fetchAll($select);
    	
    	$name = $template->name;
    	$content = $template->content;
    	$data =0;
    	$emailsSent = array();    	

    	$replaceTags = $this->getReplaceTags();
    	$staffColumns = $this->getReplaceTags(true,"staff"); 

        $staffValues = array();
    	foreach ($staffColumns as $staffColumn):
	    	$staffValues[] = $staff[$staffColumn];
    	endforeach;

    	foreach ($emails as $email):

	    	$emailValues = array();
    		$emailsColumns = $this->getReplaceTags(true,"recruiting_emails");
  
    		foreach ($emailsColumns as $emailsColumn):
    			$emailValues[] = $email[$emailsColumn];
    		endforeach;
    		
    		$replaceValues = array_merge($staffValues, $emailValues);
 
    		$subject = str_replace($replaceTags,$replaceValues,$name);
                $body = str_replace($replaceTags,$replaceValues,$content); 
                
                if($dataReferral){ $body = str_replace('{staff_ref_id}', $dataReferral['id'], $body); }
                             else{ $body = str_replace('ref/{staff_ref_id}', '', $body); }
                    		
    		$mail = new Zend_Mail();
		    $mail->setBodyHtml($body);
		    $mail->setFrom($staff['email'], $staff['name']);
		    $mail->addTo($email['email'], $email['website']);
		    $mail->setSubject($subject);
		    
		    $emailSent = array();
	
		    try {
		    	$mail->send();
		    	
		    	switch ($template->order) :
			    	case 1 ://initial try
			    		$email->pending = 1;
			    		break;
                                case 4 ://24 hour
                                        $email->date_24h_mail = date('Y-m-d H:i:s');
                                        break;
			    	case 2 ://follow up try
			    		$email->followed_up = 1;
			    		break;
			    	case 3 ://final try
			    		$email->final = 1;
			    		break;
		    	endswitch;		    	
		    	
                        $email->sub_id = $sub_id;
		    	$email->staff_id = $staff_id;
		    	$email->date_updated = date("Y-m-d H:i:s");
		    	$email->updated_by = Zend_Auth::getInstance()->getIdentity()->email;
                        $email->last_email = date("Y-m-d H:i:s");
		    	$email->save();
		    	$data++;
                        
                        $dataStat['mail']    = $email['email'];
                        $dataStat['subject'] = $subject;
                        $dataStat['text']    = $body;
                        $dataStat['author']  = Zend_Auth::getInstance()->getIdentity()->email;
                        $dataStat['staff_id']    = $staff_id;
                        $dataStat['recruiting_id'] = $email['id'];
                        $dataStat['template_id'] = $template_id;
                        $dataStat['order_id']    = $template->order;
                        $dataStat['created']     = date("Y-m-d H:i:s");
                        
                        $tableMail->insert($dataStat);
		    	
		    	$emailSent["error"] = false;
		    	$emailSent["data"] = date("Y-m-d H:i:s");
		    } catch (Exception $e){
		    	$emailSent["error"] = true;
		    	$emailSent["data"] = $e->getMessage();
		    }
		
		    $emailsSent[$email['id']] = $emailSent;

    	endforeach;
    	
    	$response['error'] = false;
    	$response['data'] = $data;
    	$response['emails'] = $emailsSent;
    	 
    	return $response;
    }

    public function import($data,
                           $staffID,
                           $importOnly = false,
                           $skipInitial= false,
                           $manually = false,
                           $temlateInitial = NULL,
                           $temlateHour = NULL,
                           $temlateFollow = NULL,
                           $temlateFinal = NULL,
                           $sentInitial = NULL,
                           $sentHour = NULL,
                           $sentFollow = NULL,
                           $sentFinal = NULL,
                           $SubID = 0,
                           $immediatelyInitial = 0){

        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        $staff_model = new Application_Model_DbTable_ContactNotification();
        $staff_email_data = $staff_model->getDataByStaffID($staffID);
        if(count($staff_email_data))
        {
            $staff_email = $staff_email_data['mail'];
        }
        else
            $staff_email = $dataAuth->email;
        $data = explode("\n",$data);

        $response = array();
        $resultUpdate = array();

        if (is_array($data) && count($data)>0):

            $temlateInitial = $temlateInitial ? $temlateInitial : NULL;
            $temlateHour = $temlateHour ? $temlateHour : NULL;
            $temlateFollow = $temlateFollow ? $temlateFollow : NULL;
            $temlateFinal = $temlateFinal ? $temlateFinal : NULL;

            $sentInitial = $this->validateDate($sentInitial, 'F d, Y g:i A') ? $sentInitial : NULL;
            $sentHour = $this->validateDate($sentHour, 'F d, Y g:i A') ? $sentHour : NULL;
            $sentFollow = $this->validateDate($sentFollow, 'F d, Y g:i A') ? $sentFollow : NULL;
            $sentFinal = $this->validateDate($sentFinal, 'F d, Y g:i A') ? $sentFinal : NULL;

            $sentInitial = $sentInitial ? date("Y-m-d H:i", strtotime($sentInitial)) : NULL;
            $sentHour = $sentHour ? date("Y-m-d H:i", strtotime($sentHour)) : NULL;
            $sentFollow = $sentFollow ? date("Y-m-d H:i", strtotime($sentFollow)) : NULL;
            $sentFinal = $sentFinal ? date("Y-m-d H:i", strtotime($sentFinal)) : NULL;

            if (preg_match("/website|email/",$data[0])){ unset($data[0]); }

            $count = 0;

            foreach ($data as $datum):

                if (strlen($datum)>0 && strpos($datum,",")){

                    list($website, $email) = explode(",",$datum);
                    if(strtolower(trim($website)) == 'website' OR strtolower(trim($email)) == 'email')
                        continue;

                    $email = trim($email);
                    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
                    {
                        $form = trim(str_replace('"', '', $email));
                        $form = str_replace('http://', '', $form);
                        $form = str_replace('https://', '', $form);
                        $email = '';
                    }
                    else
                        $form = false;

                    //if (!$importOnly && !filter_var($email, FILTER_VALIDATE_EMAIL)){ continue; }

                    $website = ucfirst(preg_replace("/(\/|http:|https:|www\.|\")/", "", trim($website)));

                    $sql = $this->_db->select()
                        ->from($this->_name, array('id', 'email', 'pending', 'form'))
                        ->where("LOWER(website) = LOWER(?)", $website);
                    /*->where("LOWER(email) = LOWER(?)",$email);*/

                    $result = $this->_db->query($sql)->fetch();

                    if (empty($result['id'])){

                        $resdata = array();

                        $row = $this->createRow();
                        $row->website = $website;
                        if(strlen($form))
                            $row->form = $form;
                        $row->email = $email;
                        $row->staff_id = $staffID;

                        if ($skipInitial):
                            $row->pending = 1;
                            $row->date_updated = date("Y-m-d H:i:s");
                        else:
                            $row->pending = 0;
                        endif;

                        if ($manually):
                            $row->manually = 1;
                        else:
                            $row->manually = 0;
                        endif;

                        if(strlen($form))
                        {
                            $row->manually = 1;
                            $row->pending = 1;
                        }

                        if ($importOnly):
                            $row->pending = 1;
                            $row->date_24h_mail = '1970-01-01 00:00:00';
                            $row->followed_up = 1;
                            $row->final = 1;
                            $row->date_updated = date("Y-m-d H:i:s");
                        else:
                            $row->pending = $row->pending ? $row->pending : 0;
                            $row->followed_up = 0;
                            $row->final = 0;
                        endif;

                        $row->template_initial = $temlateInitial;
                        $row->template_hour_24 = $temlateHour;
                        $row->template_follow_up = $temlateFollow;
                        $row->template_final = $temlateFinal;
                        $row->sent_initial = $sentInitial;
                        $row->sent_hour_24 = $sentHour;
                        $row->sent_follow_up = $sentFollow;
                        $row->sent_final = $sentFinal;
                        $row->immediately_initial = $immediatelyInitial;
                        $row->sub_id = $SubID;
                        $row->date_created = date("Y-m-d H:i:s");
                        $row->created_by = $staff_email;
                        $row->save();

                        $count++;

                    } elseif((empty($result['email']) && empty($result['form'])) || $result['pending'] == 5) {

                        if ($skipInitial){ $dataUpdate['pending'] = 1;
                            $dataUpdate['date_updated'] = date("Y-m-d H:i:s"); }
                        else             { $dataUpdate['pending'] = 0; }

                        if ($manually){ $dataUpdate['manually'] = 1; }
                        else          { $dataUpdate['manually'] = 0; }

                        if ($importOnly){
                            $dataUpdate['pending'] = 1;
                            $dataUpdate['date_24h_mail'] = '1970-01-01 00:00:00';
                            $dataUpdate['followed_up'] = 1;
                            $dataUpdate['final'] = 1;
                            $dataUpdate['date_updated'] = date("Y-m-d H:i:s");
                        }else{
                            $dataUpdate['pending'] = $dataUpdate['pending'] ? $dataUpdate['pending'] : 0;
                            $dataUpdate['followed_up'] = 0;
                            $dataUpdate['final'] = 0;
                        }

                        if(strlen($form))
                        {
                            $dataUpdate['manually'] = 1;
                            $dataUpdate['pending'] = 1;
                            $dataUpdate['form'] = $form;
                        }

                        $dataUpdate['email'] = $email;
                        $dataUpdate['responded'] = 0;
                        $dataUpdate['never_responded'] = 0;
                        $dataUpdate['deleted'] = 0;
                        $dataUpdate['closed'] = NULL;
                        $dataUpdate['date_created'] = date("Y-m-d H:i:s");
                        $dataUpdate['update_email'] = date("Y-m-d H:i:s");
                        $dataUpdate['updated_by'] = $dataAuth->email;
                        $dataUpdate['staff_id'] = $staffID;
                        $dataUpdate['template_initial'] = $temlateInitial;
                        $dataUpdate['template_hour_24'] = $temlateHour;
                        $dataUpdate['template_follow_up'] = $temlateFollow;
                        $dataUpdate['template_final'] = $temlateFinal;
                        $dataUpdate['sent_initial'] = $sentInitial;
                        $dataUpdate['sent_hour_24'] = $sentHour;
                        $dataUpdate['sent_follow_up'] = $sentFollow;
                        $dataUpdate['sent_final'] = $sentFinal;
                        $dataUpdate['immediately_initial'] = $immediatelyInitial;
                        $dataUpdate['sub_id'] = $SubID;
                        $this->update($dataUpdate, 'id = '.$result['id']);

                        $resultUpdate[] = $result['id'];
                        $count++;
                    }
                }

            endforeach;

            $response['error'] = false;
            $response['data'] = $count;
            $response['update'] = $resultUpdate;

        else :

            $response['error'] = true;
            $response['data'] = "No data";

        endif;

        return $response;
    }

    public function getNumByOrder($order_id, $staff_id)
    {
        $where = $this->getWhereForSend($order_id, $staff_id);
        
        $sql = $this->_db->select()
                    ->from($this->_name, array('COUNT(id) AS num'))
                    ->where($where);
        
        $data = $this->_db->query($sql)->fetch();
        
        return $data['num'];
    }
    
    public function getWhereForSend($order_id, $staff_id)
    {
        $result = "id=0";
        
        switch ($order_id){
    		case 1 ://initial try
    			$result = "deleted=0 AND closed IS NULL AND staff_id=$staff_id AND pending=0 AND followed_up=0 AND final=0 AND responded=0 AND LENGTH(email) > 0 ";
    			break;
                case 4 ://24 hour
                        $result = "deleted=0 AND closed IS NULL AND staff_id=$staff_id AND pending=1 AND followed_up=0 AND final=0 AND responded=0 AND date_24h_mail IS NULL AND LENGTH(email) > 0";
                        break;
    		case 2 ://follow up try
    			$result = "deleted=0 AND closed IS NULL AND staff_id=$staff_id AND pending=1 AND followed_up=0 AND final=0 AND responded=0 AND LENGTH(email) > 0";
    			break;
    		case 3 ://final try
    			$result = "deleted=0 AND closed IS NULL AND staff_id=$staff_id AND pending=1 AND followed_up=1 AND final=0 AND responded=0 AND LENGTH(email) > 0";
    			break;
        }
        
        return $result;
    }   
    
    public function getDataByID($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('id = ?', $id);
        
        return $this->_db->query($sql)->fetch();
    }

    public function getStatSub($staff_id, $staff_name, $start_date, $end_date)
    {
        $sql = "SELECT

                    s.name,

                    (SELECT COUNT(es.id)
                        FROM recruiting_emails as es
                        WHERE es.staff_id='".$staff_id."'
                          AND es.sub_id=s.id
                          AND es.pending=1
                          AND es.responded=0
                          AND es.never_responded=0
                          AND es.deleted=0
                          AND DATE_FORMAT(date_created, '%Y-%m-%d')>='".$start_date."'
                          AND DATE_FORMAT(date_created, '%Y-%m-%d')<='".$end_date."') +
                     (SELECT COUNT(le.id)
                        FROM recruiting_emails as le
                       WHERE le.staff_id='".$staff_id."'
                         AND le.sub_id=s.id
                         AND le.pending=1
                         AND le.responded=1
                         AND le.never_responded=0
                         AND le.deleted=0
                         AND le.closed IS NULL
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')>='".$start_date."'
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')<='".$end_date."') +
                     (SELECT COUNT(cl.id)
                        FROM recruiting_emails as cl
                       WHERE cl.closed_by='".$staff_name."'
                         AND cl.closed IS NOT NULL
                         AND cl.sub_id=s.id
                         AND cl.deleted=0
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')>='".$start_date."'
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')<='".$end_date."') as emails_sent,

                    (SELECT COUNT(le.id)
                        FROM recruiting_emails as le
                       WHERE le.staff_id='".$staff_id."'
                         AND le.sub_id=s.id
                         AND le.pending=1
                         AND le.responded=1
                         AND le.never_responded=0
                         AND le.deleted=0
                         AND le.closed IS NULL
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')>='".$start_date."'
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')<='".$end_date."') +
                     (SELECT COUNT(cl.id)
                        FROM recruiting_emails as cl
                       WHERE cl.closed_by='".$staff_name."'
                         AND cl.closed IS NOT NULL
                         AND cl.sub_id=s.id
                         AND cl.deleted=0
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')>='".$start_date."'
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')<='".$end_date."') AS responses,

                    (SELECT COUNT(le.id)
                        FROM recruiting_emails as le
                       WHERE le.staff_id='".$staff_id."'
                         AND le.sub_id=s.id
                         AND le.pending=1
                         AND le.responded=1
                         AND le.never_responded=0
                         AND le.deleted=0
                         AND le.closed IS NULL
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')>='".$start_date."'
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')<='".$end_date."') as leads,

                    (SELECT COUNT(cl.id)
                        FROM recruiting_emails as cl
                       WHERE cl.closed_by='".$staff_name."'
                         AND cl.closed IS NOT NULL
                         AND cl.sub_id=s.id
                         AND cl.deleted=0
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')>='".$start_date."'
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')<='".$end_date."') as closed,

                    (SELECT COUNT(cl.id)
                        FROM recruiting_emails as cl
                       WHERE cl.closed_by='".$staff_name."'
                         AND cl.closed=1
                         AND cl.sub_id=s.id
                         AND cl.deleted=0
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')>='".$start_date."'
                         AND DATE_FORMAT(date_created, '%Y-%m-%d')<='".$end_date."') as leads_won

                 FROM sub AS s";


        return  $this->_db->query($sql)->fetchAll();

    }

    public function validateDate($date,$format='Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}