<?php


class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract
{
    protected $_name = 'users';
    protected $_rowClass='Application_Model_DbTable_Row_User';

    public function save($data)
    {
        $this->_name = 'users';

        $siteUrl = $data['url'];
        if(substr($siteUrl, -1)=='/')
            $siteUrl = substr($siteUrl, 0, -1);


        $result = array(
                        'ip'=>$_SERVER['REMOTE_ADDR'],
                        'referral_id' => $data['referral_id'] ? $data['referral_id'] : NULL,
                        'password'=>md5(md5($data['password']).md5($data['salt'])),
                        'salt'=>$data['salt'],
                        'created'=>date("Y-m-d H:i:s"),
                        'date_eligible'=>$data['users_waiting']!=1 ? date("Y-m-d H:i:s") : NULL,
                        'company'=>$data['company'] ? $data['company'] : NULL,
                        'name'=>$data['name'] ? $data['name'] : NULL,
                        'email'=>$data['email'],
                        'phone'=>$data['phone'] ? $data['phone'] : NULL,
                        'zone'=>$data['zone'] ? $data['zone'] : NULL,
                        'country'=>$data['country'] ? $data['country'] : NULL,
                        'state'=>$data['state'] ? $data['state'] : NULL,
                        'ssn'=>$data['ssn'] ?  $data['ssn'] : NULL,
                        'server'=>$data['server'] ? $data['server'] : NULL,
                        'url'=>$siteUrl,
                        'title'=>$data['title'] ? $data['title'] : NULL,
                        'description'=>$data['description'] ? $data['description'] : NULL,
                        'keywords'=>$data['keywords'] ? $data['keywords'] : NULL,
                        'category'=>$data['category'] ? $data['category'] : NULL,
                        'privacy'=>$data['privacy'] ? $data['privacy'] : NULL,
                        'type'=>$data['type'] ? $data['type'] : 1,
                        'daily'=>$data['daily'] ? $data['daily'] : NULL,
                        'active'=>0,
                        'role'=>'user',
                        'tumblrAds'=>$data['tumblrAds'] ? $data['tumblrAds'] : NULL,
                        'followers'=>$data['followers'] ? $data['followers'] : NULL,
                        'alexa_start'=>$data['alexaRank'] ? $data['alexaRank'] : NULL,
        		'alexaRank'=>$data['alexaRank'] ? $data['alexaRank'] : NULL,
        		'alexaRankUS'=>$data['alexaRankUS'] ? $data['alexaRankUS'] : NULL,
        		'alexa_country'=>$data['alexa_country'] ? $data['alexa_country'] : NULL,        		
        		'users_waiting'=>$data['users_waiting'] ? $data['users_waiting'] : NULL,        	       
        		'alexaRank_update'=>$data['alexaRank_update'] ? $data['alexaRank_update'] : NULL,
                        'notification_control_admin' => 1,
                        'inviteAdx' => 1,
                        'account_manager_id' => $data['account_manager_id'],
                        'update_pass'=>1,
                        'date_update'=>date("Y-m-d"),
                        'desired_types'=>$data['desired_types']
                         );

        return $this->insert($result);
    }

    public function getUserByEmail($email)
    {
        $this->_name = 'users';

        return $this->fetchRow("email = '$email'");
    }

    public function getUserById($id)
    {
        $this->_name = 'users';

        return $this->fetchRow("id = '$id'");
    }

    public function getUserXml($userID)
    {
        $this->_name = 'stats';

        $data = $this->fetchRow("PubID = $userID");

        if($data){
            return $data->toArray();
        }else{
            return FALSE;
        }

    }

    public function generareNewPassConfirm($email, $confirm)
    {
        $this->_name = 'users';

        $result = array(
                        'forgot'=>$confirm,
                        );

        $where = $this->getAdapter()->quoteInto('email  = ?', $email);
        $this->update($result, $where);
    }

    public function updatePasswordConfirm($email, $confirm)
    {
        $this->_name = 'users';

        $result = array(
                        'update_hash'=>$confirm,
                        );

        $where = $this->getAdapter()->quoteInto('email  = ?', $email);
        $this->update($result, $where);
    }


    public function getUserByPassConfirm($confirm){
        $this->_name = 'users';

        $data = $this->fetchRow("forgot = '$confirm'");
        if($data){
            return $data->toArray();
        }else{
            return FALSE;
        }
    }

    public function getUserByPassUpdate($confirm){
        $this->_name = 'users';

        $data = $this->fetchRow("update_hash = '$confirm'");
        if($data){
            return $data->toArray();
        }else{
            return FALSE;
        }
    }

    public function changePassword($id, $pass, $salt)
    {
        $this->_name = 'users';

        $result = array(
                        'password'=>$pass,
                        'salt'=>$salt,
                        'forgot'=>NULL,
                        'update_hash'=>NULL,
                        'date_update'=>date("Y-m-d"),
                        'update_pass'=>1
                        );

        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        $this->update($result, $where);
    }

    public function saveContacts($id, $data)
    {
        $this->_name = 'users';

        $result = array(
                        'company'=>$data['company'],
                        'name'=>$data['name'],
                        'email'=>$data['email'],
                        'phone'=>$data['phone'],
                        'notification_control_user'=>$data['notification_control_user'] == 1 ? 1 : NULL
                        );

        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        $this->update($result, $where);
    }

    public function querySelect($table, $id='id')
    {
        $this->_name = $table;
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'key'=>$this->_name . '.' . $id,
                                                            'value'=>$this->_name . '.name',
                                                        ))
                                ->order('name ASC');
            $result = $this->getAdapter()->fetchAll($select);
            return $result;
    }
    
    public function querySelectPaymentAmount($wire_enabled = false)
    {
    	$this->_name = 'payment';
    	$select = $this->_db->select()
    						->from($this->_name,array('key' => 'id',
    												  'value' => 'name'));
    	if($wire_enabled)
    		$select->where('id > 3');
    	$str = $select->__toString();
    	$result =$this->_db->fetchAll($select);
    	return $result;	
    }

    public function querySelectSites($table)
    {
        $this->_name = $table;

            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'key'=>$this->_name . '.id',
                                                            'value'=>$this->_name . '.name',
                                                            'company'=>$this->_name . '.company',
                                                        ))
                               ;
            $result = $this->getAdapter()->fetchAll($select);

            return $result;
    }


    public function getResult($table, $id, $idField='id', $nameField='name')
    {
        $this->_name = $table;
            if($id){
                $select = $this->_db->select()
                                    ->from($this->_name, array(
                                                                'value'=>$this->_name . '.' . $nameField,
                                                            ))
                                    ->where($idField. ' = ' . $id)
                                   ;
                $result = $this->getAdapter()->fetchRow($select);

                return $result['value'];
            }else{
                return false;
            }
    }


    public function changePayments($id, $data)
    {
        $this->_name = 'users';
        $result = array(
                        'name'=>$data['name'] ? $data['name'] : NULL,
                        'payType'=>$data['payType'] ? $data['payType'] : NULL,
                        'street1'=>$data['street1'] ? $data['street1'] : NULL,
                        'street2'=>$data['street2'] ? $data['street2'] : NULL,
                        'city'=>$data['city'] ? $data['city'] : NULL,
                        'state'=>$data['state'] ? $data['state'] : NULL,
                        'zip'=>$data['zip'] ? $data['zip'] : NULL,
                        'country'=>$data['country'] ? $data['country'] : NULL,
                        'paymentAmout'=>$data['paymentAmout'] ? $data['paymentAmout'] : NULL,
                        'ssn'=>$data['ssn'] ? $data['ssn'] : NULL,
                        'ein'=>$data['ein'] ? $data['ein'] : NULL,
                        'paymentBy'=>$data['paymentBy'] ? $data['paymentBy'] : NULL,
                        'paypalmail'=>$data['paypalmail'] ? $data['paypalmail'] : NULL,
                        'bank'=>$data['bank'] ? $data['bank'] : NULL,
                        'accName'=>$data['accName'] ? $data['accName'] : NULL,
                        'bankName'=>$data['bankName'] ? $data['bankName'] : NULL,
                        'accType'=>$data['accType'] ? $data['accType'] : NULL,
                        'accNumber'=>  $data['accNumber'] ? $data['accNumber'] : NULL,
                        'confirmAccNumber'=>$data['confirmAccNumber'] ? $data['confirmAccNumber'] : NULL,
                        'routNumber'=>$data['routNumber'] ? $data['routNumber'] : NULL,
                        'confirmRoutNumber'=>$data['confirmRoutNumber'] ? $data['confirmRoutNumber'] : NULL,
                        'bankAdress'=>$data['bankAdress'] ? $data['bankAdress'] : NULL,
                        'bankName2'=>$data['bankName2'] ? $data['bankName2'] : NULL,
                        'accName2'=>  $data['accName2'] ? $data['accName2'] : NULL,
                        'accNumber2'=>  $data['accNumber2'] ? $data['accNumber2'] : NULL,
                        'swift'=>$data['swift'] ? $data['swift'] : NULL,
                        'iban'=>$data['iban'] ? $data['iban'] : NULL
                        );

        if($data['paymentAmout_update']) $result['paymentAmout_update'] = $data['paymentAmout_update'];

        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        $this->update($result, $where);
    }

    public function setPaymentInfoPending($id, $data)
    {
        if(intval($id))
        {
            $result = array(
                'PubID'=> $id,
                'name'=>$data['name'] ? $data['name'] : NULL,
                'payType'=>$data['payType'] ? $data['payType'] : NULL,
                'street1'=>$data['street1'] ? $data['street1'] : NULL,
                'street2'=>$data['street2'] ? $data['street2'] : NULL,
                'city'=>$data['city'] ? $data['city'] : NULL,
                'state'=>$data['state'] ? $data['state'] : NULL,
                'zip'=>$data['zip'] ? $data['zip'] : NULL,
                'country'=>$data['country'] ? $data['country'] : NULL,
                'paymentAmout'=>$data['paymentAmout'] ? $data['paymentAmout'] : NULL,
                'ssn'=>$data['ssn'] ? $data['ssn'] : 'NULL',
                'ein'=>$data['einReal'] ? $data['einReal'] : 'NULL',
                'paymentBy'=>$data['paymentBy'] ? $data['paymentBy'] : 'NULL',
                'paypalmail'=>$data['paypalmail'] ? $data['paypalmail'] : 'NULL',
                'bank'=>$data['bank'] ? $data['bank'] : NULL,
                'accName'=>$data['accNameReal'] ? $data['accNameReal'] : NULL,
                'bankName'=>$data['bankName'] ? $data['bankName'] : NULL,
                'accType'=>$data['accType'] ? $data['accType'] : NULL,
                'accNumber'=>  $data['accNumberReal'] ? $data['accNumberReal'] : NULL,
                'confirmAccNumber'=>$data['confirmAccNumberReal'] ? $data['confirmAccNumberReal'] : NULL,
                'routNumber'=>$data['routNumberReal'] ? $data['routNumberReal'] : NULL,
                'confirmRoutNumber'=>$data['confirmRoutNumberReal'] ? $data['confirmRoutNumberReal'] : NULL,
                'bankAdress'=>$data['bankAdress'] ? $data['bankAdress'] : NULL,
                'bankName2'=>$data['bankName2'] ? $data['bankName2'] : NULL,
                'accName2'=>  $data['accName2'] ? $data['accName2'] : NULL,
                'accNumber2'=>  $data['accNumber2Real'] ? $data['accNumber2Real'] : NULL,
                'swift'=>$data['swift'] ? $data['swift'] : NULL,
                'iban'=>$data['iban'] ? $data['iban'] : NULL,
                'created' => date("Y-m-d H:i:s")
            );
            $select = $this->_db->select()->from(array('users'),array('email'))
            					->where('id = ?', $id)
            					->limit(1);
            $str = $select->__toString();
            $current_user = $this->_db->fetchRow($select);
            
            $this->_name = 'user_new_payment_info';
            $where = $this->getAdapter()->quoteInto('PubID = ?', $id);
            if($this->fetchRow('SELECT `id` FROM user_new_payment_info WHERE PubID = '.$id)){
                $this->update($result, $where);
            }else{
                
                    $layout = Zend_Layout::getMvcInstance();
                 if($layout->PaymentError == 1){

                     unset($result['PubID']);
                     unset($result['created']);
                     
                     $tableUser = new Application_Model_DbTable_Users();
                     $tableUser->update($result, array('id = ?' => $id));                     
                     
                     $pluginRedirect = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                     $pluginRedirect->gotoSimple('index', 'report', 'default');
                     
                 }else{            

                        $this->insert($result);

                        $payment_changes = new Application_Model_DbTable_User_NewPaymentInfoChanges();
                        $pending_model = new Application_Model_DbTable_User_NewPaymentInfo();
                        $changed_info = $pending_model->getData($id);
                        //Sending mail
                        $mail = new Zend_Mail();
                        $body = '<p>Hello,<br/>
                                 This email is to notify you that you have changes pending to your payment profile.</p>
                                 Please see the changes below:</p>
                                 Name: '.$changed_info['new']['name'].'<br/>
                                 Payment Type: '.$changed_info['new']['payType'].'<br/>
                                 Street 1: '.$changed_info['new']['street1'].'<br/>
                                 Street 2: '.$changed_info['new']['street2'].'<br/>
                                 City: '.$changed_info['new']['city'].'<br/>
                                 State: '.$changed_info['new']['state'].'<br/>
                                 Payment Amount: '.$changed_info['new']['paymentAmout'].'<br/>
                                 Country: '.$changed_info['new']['country'].'<br/>
                                 Payment Method: '.$changed_info['new']['paymentBy'].'<br/>
                                 PayPal Email: '.($changed_info['new']['paypalmail'] ? $changed_info['new']['paypalmail'] : '-').'<br/>
                                 Bank Name: '.($changed_info['new']['bankName'] ? $changed_info['new']['bankName'] : '-').'<br/>
                                 Account Type: '.($changed_info['new']['accType'] ? $changed_info['new']['accType'] : '-').'<br/>
                                 Account Number: '.($changed_info['new']['accNumber'] ? $changed_info['new']['accNumber'] : '-').'<br/>
                                 Routing Number: '.($changed_info['new']['routNumber'] ? $changed_info['new']['routNumber'] : '-').'<br/>
                                 <p>If you did not authorize this update, please respond to this email immediately.</p>
                                 <p>Regards,<br/>
                                 The MadAds Media Publisher Team</p>';
                        $mail->setBodyHtml($body);
                        $mail->setFrom('support@madadsmedia.com', 'MadAdsMedia.com');
                        $mail->addTo($current_user['email']);
                        //$mail->addCc(array('billing@madadsmedia.com', 'support@madadsmedia.com'));
                        $mail->setSubject('Pending Changes to Your MadAdsMedia.com Payment');
                        $mail->send();
                        //
                 }            
            }
        }
    }

    public function getNewUsers()
    {
        $this->_name = 'users';

        return $this->fetchAll("active = 0 AND reject = 0");
    }

    public function getNewUsersNoWaiting()
    {
        $this->_name = 'users';

        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('active = 0 AND reject = 0')
                    ->where('alexaRank<800000 AND alexaRank<>0 AND alexaRank IS NOT NULL');

        return $this->_db->query($sql)->fetchAll();
    }

    public function getNewUsersWaiting()
    {
        $this->_name = 'users';

        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('active = 0 AND reject = 0')
                    ->where('alexaRank>=800000 OR alexaRank=0 OR alexaRank IS NULL');

        return $this->_db->query($sql)->fetchAll();
    }

    public function countNewUsersWaiting()
    {
            $this->_name = "users";

            $select = $this->_db->select()
                                ->from('users', array(
                                                            'count'=>'COUNT(users.id)'
                                                        ))
                               ->where("users.active = 0 AND users.reject = 0")
                                ->where('users_waiting=1');
            $result = $this->getAdapter()->fetchRow($select);
            return $result;
    }

    public function countNewUsersNoWaiting()
    {
            $this->_name = "users";

            $select = $this->_db->select()
                                ->from('users', array(
                                                            'count'=>'COUNT(users.id)'
                                                        ))
                    ->where('active = 0 AND reject = 0')
                    ->where('users_waiting=0 OR users_waiting IS NULL');
            $result = $this->getAdapter()->fetchRow($select);
            return $result;
    }

    public function countNewUsers2()
    {
            $this->_name = "users";

            $select = $this->_db->select()
                                ->from('users', array(
                                                            'count'=>'COUNT(users.id)'
                                                        ))
                    ->where('active = 0 AND reject = 0')
                    ->where('users_waiting=2');
            $result = $this->getAdapter()->fetchRow($select);
            return $result;
    }

    public function countNewUsers()
    {
            $this->_name = "users";

            $select = $this->_db->select()
                                ->from('users', array(
                                                            'count'=>'COUNT(users.id)'
                                                        ))
                               ->where("users.active = 0 AND users.reject = 0")
                               ;
            $result = $this->getAdapter()->fetchRow($select);
            return $result;
    }

    public function getDenyUsers()
    {
        $this->_name = 'users';

        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('reject = 1');

        return $this->_db->query($sql)->fetchAll();
    }

    public function getApprovUsers()
    {
        $this->_name = 'users';

        return $this->fetchAll("active = 1");
    }

    public function getApproveUsersWithSites()
    {
            $this->_name = "users";
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'id'=>'users.id',
                                                            'name'=>'users.name',
                                                            'email'=>'users.email',
                                                            'created'=>'users.created',
                                							'company'=>'users.company',
                                                            'sites'=>new Zend_Db_Expr("GROUP_CONCAT( DISTINCT  cast(concat( sites.SiteID, ':', sites.SiteName ) AS char ) SEPARATOR ', ' )"),
                                                            'enable_wire_transfer'=>'users.enable_wire_transfer'
                                                        ))
                               ->joinLeft('sites', 'sites.PubID=users.id', array())
                               ->where('users.active = 1')
                               ->group('users.id');
            $result = $this->getAdapter()->fetchAll($select);

            return $result;
    }

    public function getUserAllInfoById($id)
    {
            $this->_name = "users";
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'id'=>'users.id',
                                                            'referral_id' => 'users.referral_id',
                                                            'company' => 'users.company',
                                                            'street1' => 'users.street1',
                                                            'street2' => 'users.street2',
                                                            'state' => 'state.name',
                                                            'city' => 'users.city',
                                                            'zip' => 'users.zip',
                                                            'payType' => 'users.payType',                                							'paymentBy'=>'users.paymentBy',
                                                            'paypalmail' => 'users.paypalmail',
                                                            'bank' => 'users.bank',
                                                            'accName' => 'users.accName',
                                                            'bankName' => 'users.bankName',
                                                            'accType' => 'users.accType',
                                                            'accNumber' => 'users.accNumber',
                                                            'confirmAccNumber' => 'users.confirmAccNumber',
                                                            'routNumber' => 'users.routNumber',
                                                            'confirmRoutNumber' => 'users.confirmRoutNumber',
                                                            'name' => 'users.name',
                                                            'email' => 'users.email',
                                                            'phone' => 'users.phone',
                                                            'timezone' => 'timezone.name',
                                                            'country' => 'country.name',
                                                            'ssn' => 'users.ssn',
                                                            'server' => 'service.name',
                                                            'url' => 'users.url',
                                                            'title' => 'users.title',
                                                            'description' => 'users.description',
                                                            'keywords' => 'users.keywords',
                                                            'category' => 'category.name',
                                                            'privacy' => 'users.privacy',
                                                            'type' => 'users.type',
                                                            'daily' => 'users.daily',
                                                            'followers' => 'users.followers',
					                                		'alexaRank' => 'users.alexaRank',
                                                            'alexaRankUS' => 'users.alexaRankUS',
					                                		'alexaRank_update' => 'users.alexaRank_update',
                                                            'enable_wire_transfer' => 'users.enable_wire_transfer',
                                                            'bankName2' => 'users.bankName2',
                                                            'bankAdress' => 'users.bankAdress',
                                                            'accName2' => 'users.accName2',
                                                            'accNumber2' => 'users.accNumber2',
                                                            'swift' => 'users.swift',
                                                            'iban' => 'users.iban',
                                			    			'active' => 'users.active',
                                                            'notification_control_admin'=>'users.notification_control_admin',
                                    			    		'reg_AdExchage'=>'users.reg_AdExchage',
                                                            'inviteURL' => 'users.inviteURL',
                                                            'inviteRequest' => 'users.inviteRequest',
                                                            'inviteAdx' => 'users.inviteAdx',
                                                            'note' => 'users.note',
                                                            'auto_min_cpm' => 'users.auto_min_cpm',
                                                            'approved_by' => 'users.approved_by',
                                                            'denied_by' => 'users.denied_by',
                                                            'account_manager_id' => 'users.account_manager_id',
                                                            'lock_am' =>'users.lock_am',
                                							'referral_system' => 'users.referral_system',
                                                            'date_confirm' => 'users.date_confirm',
                                                            'desired_types'=>'users.desired_types'
                                                        ))
                               ->joinLeft('timezone', 'timezone.id=users.zone', array())
                               ->joinLeft('country', 'country.id=users.country', array())
                               ->joinLeft('state', 'state.id=users.state', array())
                               ->joinLeft('service', 'service.service=users.server', array())
                               ->joinLeft('category', 'category.id=users.category', array())
                               ->joinLeft('referral','referral.id = users.referral_id', array('referral.name AS referral_name'))
                               ->where('users.id = ' . $id)
                               ;
            $result = $this->getAdapter()->fetchRow($select);

            return $result;
    }

    public function deleteUser($id)
    {
        $this->_name = 'users';
        $this->delete(array("id = '$id'"));
    }

    public function confirmUser($id, $dataForm, $active, $reject)
    {
        $this->_name = 'users';

        $result = array(
                        'active'=>$active,
                        'reject'=>$reject,
                        'enable_wire_transfer'=>$dataForm['enable_wire_transfer']==1 ? 1 : NULL,
                        'notification_control_admin'=>$dataForm['notification_control_admin']==1 ? NULL : 1,
                        'notification_control_user'=>1,
                        'inviteURL' => $dataForm['inviteURL'],
                        'inviteAdx' => $dataForm['inviteAdx']==1 ? NULL : 1,
                        'inviteRequest' => $dataForm['inviteURL'],
                        'auto_min_cpm' => $dataForm['auto_min_cpm'],
                        'approved_by'=> $dataForm['approved_by'],
                        'referral_id'=> $dataForm['referral_id']>0 ? $dataForm['referral_id'] : NULL,
                        'account_manager_id'=> $dataForm['account_manager_id'] ? $dataForm['account_manager_id'] : NULL,
                        'date_confirm' => date('Y-m-d H:i:s'),
                        'desired_types' => $dataForm['desired_types']
                        );

        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        $this->update($result, $where);
    }

    public function rejectUser($id, $email, $note=null)
    {
        $this->_name = 'users';

        $result = array(
                        'reject'=>1,
                        'active'=>0,
                        'note'=>$note ? $note : NULL,
                        'denied_by'=>$email,
                        'deny_date'=>date("Y-m-d H:i:s")
                        );

        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        $this->update($result, $where);
    }

    public function insertPublisher($id, $email)
    {
        $this->_name = 'publisher';
        $this->_primary = 'ID';

        $result = array(
                        'ID'=>$id,
                        'RevShare'=>70,
                        'I5bkCPM'=>'.85',
                        'Email'=>$email,
                        );

        return $this->insert($result);
    }

    public function getUserInfo($id)
    {
            $this->_name = "users";
            $select = $this->_db->select()
                                ->from($this->_name)
                               ->where('id = ' . $id)
                               ;
            $result = $this->getAdapter()->fetchRow($select);

            return $result;
    }

    public function notified($id)
    {
        $this->_name = 'sites';

        $result = array(
                        'notified'=>1
                        );

        $where = $this->getAdapter()->quoteInto('SiteID = ?', $id);
        $this->update($result, $where);
    }


    public function getFinalReport($userID, $site, $start_date, $end_date, $ad_size, $disable_grouping)
    {
        if($site)
            $whereSite = 'sites.SiteID = ' . $site;
        else
            $whereSite = '1=1';

        if($start_date && $end_date)
            $whereDate = "DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')<='$end_date'";
        else
            $whereDate = '1=1';

        if($ad_size)
            $whereSize = 'madads_dfp_table.AdSize = ' . $ad_size;
        else
            $whereSize = '1=1';

        $select = $this->_db->select()
                            ->from('madads_dfp_table', array(
                                                        'Date'=>'madads_dfp_table.query_date',
                                                        'Impressions'=>'SUM( (IFNULL(madads_dfp_table.impressions, 0)+IFNULL(madads_rubicon_table.impressions, 0)) )',
                                                        'Revenue'=>'SUM( (IFNULL(madads_dfp_table.revenue, 0)+IFNULL(madads_rubicon_table.revenue, 0)) )',
                                                        'RevShare'=>'publisher.RevShare'
                                                    ))
                           ->joinLeft('sites', 'madads_dfp_table.SiteID=sites.SiteID', array())
                           ->joinLeft('madads_rubicon_table', '(madads_dfp_table.query_date=madads_rubicon_table.query_date AND madads_dfp_table.AdSize=madads_rubicon_table.AdSize AND madads_rubicon_table.SiteID=sites.SiteID)', array())
                           ->joinLeft('publisher', 'publisher.ID=sites.PubID', array())
                           ->where("sites.PubID = '$userID' AND madads_dfp_table.order_name='AdSense' AND madads_rubicon_table.query_date IS NOT NULL AND madads_dfp_table.query_date IS NOT NULL")
                           ->group(array("madads_rubicon_table.query_date", "madads_dfp_table.query_date"))
                           ->order(array("madads_dfp_table.query_date DESC", "madads_rubicon_table.query_date DESC"))

                           ->where($whereSite)
                           ->where($whereDate)
                           ->where($whereSize)
                           ;
        $result = $this->getAdapter()->fetchAll($select);

        $resultArray = array();
        if($result){
            foreach($result as $item){
                $resultArray[$item['Date']] = $item;
                $resultArray[$item['Date']]['estimated'] = '';
            }
        }

        return $resultArray;
    }

    public function getEstimatedReport($userID, $site, $start_date, $end_date, $ad_size, $disable_grouping)
    {
        if($site)
            $whereSite = 'sites.SiteID = ' . $site;
        else
            $whereSite = '1=1';

        if($start_date && $end_date)
            $whereDate = "DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')<='$end_date'";
        else
            $whereDate = '1=1';

        if($ad_size)
            $whereSize = 'madads_dfp_table.AdSize = ' . $ad_size;
        else
            $whereSize = '1=1';

        $select = $this->_db->select()
                            ->from('madads_dfp_table', array(
                                                        'Date'=>'madads_dfp_table.query_date',
                                                        'Impressions'=>'SUM(madads_dfp_table.impressions)',
                                                        'Revenue'=>'SUM(madads_dfp_table.revenue)',
                                                        'RevShare'=>'publisher.RevShare'
                                                    ))
                           ->joinLeft('sites', 'madads_dfp_table.SiteID=sites.SiteID', array())
                           ->joinLeft('publisher', 'publisher.ID=sites.PubID', array())
                           ->group("madads_dfp_table.query_date")
                           ->order("madads_dfp_table.query_date DESC")
                           ->where("sites.PubID = '$userID' AND (madads_dfp_table.order_name='AdSense' OR madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)')")
                           ->where($whereSite)
                           ->where($whereDate)
                           ->where($whereSize)
                           ;
        $result = $this->getAdapter()->fetchAll($select);

        $resultArray = array();
        if($result){
            foreach($result as $item){
                $resultArray[$item['Date']] = $item;
                $resultArray[$item['Date']]['estimated'] = 1;
            }
        }

        return $resultArray;
    }

    public function getDfpReportByService($userID, $site, $start_date, $end_date, $ad_size, $disable_grouping, $service)
    {
        if($site)
            $whereSite = 'sites.SiteID = ' . $site;
        else
            $whereSite = '1=1';

        if($start_date && $end_date)
            $whereDate = "DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')<='$end_date'";
        else
            $whereDate = '1=1';

        if($ad_size)
            $whereSize = 'madads_dfp_table.AdSize = ' . $ad_size;
        else
            $whereSize = '1=1';

        $select = $this->_db->select()
                            ->from('madads_dfp_table', array(
                                                        'Date'=>'madads_dfp_table.query_date',
                                                        'Impressions'=>'SUM(madads_dfp_table.impressions)',
                                                        'Revenue'=>'SUM(madads_dfp_table.revenue)',
                                                        'RevShare'=>'publisher.RevShare'
                                                    ))
                           ->joinLeft('sites', 'madads_dfp_table.SiteID=sites.SiteID', array())
                           ->joinLeft('publisher', 'publisher.ID=sites.PubID', array())
                           ->group("madads_dfp_table.query_date")
                           ->order("madads_dfp_table.query_date DESC")
                           ->where("sites.PubID = '$userID' AND madads_dfp_table.order_name='$service'")
                           ->where($whereSite)
                           ->where($whereDate)
                           ->where($whereSize)
                           ;
        $result = $this->getAdapter()->fetchAll($select);

        $resultArray = array();
        if($result){
            foreach($result as $item){
                $resultArray[$item['Date']] = $item;
            }
        }

        return $resultArray;
    }

    public function getRubiconReport($userID, $site, $start_date, $end_date, $ad_size, $disable_grouping)
    {
        if($site)
            $whereSite = 'sites.SiteID = ' . $site;
        else
            $whereSite = '1=1';

        if($start_date && $end_date)
            $whereDate = "DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')<='$end_date'";
        else
            $whereDate = '1=1';

        if($ad_size)
            $whereSize = 'madads_rubicon_table.AdSize = ' . $ad_size;
        else
            $whereSize = '1=1';

        $select = $this->_db->select()
                            ->from('madads_rubicon_table', array(
                                                        'Date'=>'madads_rubicon_table.query_date',
                                                        'Impressions'=>'SUM(madads_rubicon_table.impressions)',
                                                        'Revenue'=>'SUM(madads_rubicon_table.revenue)',
                                                        'RevShare'=>'publisher.RevShare'
                                                    ))
                           ->joinLeft('sites', 'madads_rubicon_table.SiteID=sites.SiteID', array())
                           ->joinLeft('publisher', 'publisher.ID=sites.PubID', array())
                           ->group("madads_rubicon_table.query_date")
                           ->order("madads_rubicon_table.query_date DESC")
                           ->where("sites.PubID = '$userID'")
                           ->where($whereSite)
                           ->where($whereDate)
                           ->where($whereSize)
                           ;
        $result = $this->getAdapter()->fetchAll($select);

        $resultArray = array();
        if($result){
            foreach($result as $item){
                $resultArray[$item['Date']] = $item;
            }
        }

        return $resultArray;
    }

    public function getEstimatedRubiDates()
    {

        $select = $this->_db->select()
                            ->from('madads_rubicon_table', array(
                                                        'DateRub'=>'madads_rubicon_table.query_date'
                                                    ))
                           ->group(array("madads_rubicon_table.query_date"))
                           ->order(array("madads_rubicon_table.query_date DESC"))
                           ->limit(7)
                           ;
        $result = $this->getAdapter()->fetchAll($select);
        $resultArray = array();
        if($result){
            foreach($result as $item){            	$date = $item['DateRub'];
                $resultArray[$date] = $item['DateRub'];
            }
        }
        return $resultArray;
    }    public function getEstimatedAdsDates()
    {

    	$select = $this->_db->select()
    	->from('madads_dfp_table', array(
    			'DateRub'=>'madads_dfp_table.query_date'
    	))
    	->group(array("madads_dfp_table.query_date"))
    	->order(array("madads_dfp_table.query_date DESC"))
    	->limit(7)
    	;

    	$result = $this->getAdapter()->fetchAll($select);

    	$resultArray = array();
    	if($result){
    		foreach($result as $item){
    			$date = $item['DateRub'];
    			$resultArray[$date] = $item['DateRub'];
    		}
    	}

    	return $resultArray;
    }

    public function getNewEstimatedReport($userID, $site, $start_date, $end_date, $ad_size, $disable_grouping)
    {
        if($site)
            $whereSite = 'sites.SiteID = ' . $site;
        else
            $whereSite = '1=1';

        if($start_date && $end_date)
            $whereDate = "DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')<='$end_date'";
        else
            $whereDate = '1=1';

        if($ad_size)
            $whereSize = 'madads_dfp_table.AdSize = ' . $ad_size;
        else
            $whereSize = '1=1';

        $select = $this->_db->select()
                            ->from('madads_dfp_table', array(
                                                        'Date'=>'madads_dfp_table.query_date',
                                                        'Impressions'=>'SUM( IFNULL(madads_dfp_table.impressions, 0) )',
                                                        'Revenue'=>'SUM( IFNULL(madads_dfp_table.revenue, 0) )',
                                                        'RevShare'=>'publisher.RevShare'
                                                    ))
                           ->joinLeft('sites', 'madads_dfp_table.SiteID=sites.SiteID', array())
                           ->joinLeft('publisher', 'publisher.ID=sites.PubID', array())
                           ->where("sites.PubID = '$userID' AND (madads_dfp_table.order_name='AdSense' OR madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)')")
                           ->where("madads_dfp_table.AdSize IS NOT NULL AND madads_dfp_table.SiteID IS NOT NULL")
                           ->group(array("madads_dfp_table.query_date"))
                           ->order(array("madads_dfp_table.query_date DESC"))

                           ->where($whereSite)
                           ->where($whereDate)
                           ->where($whereSize)
                           ;
        $result = $this->getAdapter()->fetchAll($select);

        $resultArray = array();
        if($result){
            foreach($result as $item){
                $resultArray[$item['Date']] = $item;
            }
        }

        return $resultArray;
    }

    public function getNewFinalRubiconReport($userID, $site, $start_date, $end_date, $ad_size, $disable_grouping)
    {
        if($site)
            $whereSite = 'sites.SiteID = ' . $site;
        else
            $whereSite = '1=1';

        if($start_date && $end_date)
            $whereDate = "DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')<='$end_date'";
        else
            $whereDate = '1=1';

        if($ad_size)
            $whereSize = 'madads_rubicon_table.AdSize = ' . $ad_size;
        else
            $whereSize = '1=1';

        $select = $this->_db->select()
                            ->from('madads_rubicon_table', array(
                                                        'Date'=>'madads_rubicon_table.query_date',
                                                        'Impressions'=>'SUM( IFNULL(madads_rubicon_table.impressions, 0) )',
                                                        'Revenue'=>'SUM( IFNULL(madads_rubicon_table.revenue, 0) )',
                                                        'RevShare'=>'publisher.RevShare'
                                                    ))
                           ->joinLeft('sites', 'madads_rubicon_table.SiteID=sites.SiteID', array())
                           ->joinLeft('publisher', 'publisher.ID=sites.PubID', array())
                           ->where("sites.PubID = '$userID'")
                           ->where("madads_rubicon_table.AdSize IS NOT NULL AND madads_rubicon_table.SiteID IS NOT NULL")
                           ->group(array("madads_rubicon_table.query_date"))
                           ->order(array("madads_rubicon_table.query_date DESC"))

                           ->where($whereSite)
                           ->where($whereDate)
                           ->where($whereSize)
                           ;
        $result = $this->getAdapter()->fetchAll($select);

        $resultArray = array();
        if($result){
            foreach($result as $item){
                $resultArray[$item['Date']] = $item;
            }
        }

        return $resultArray;
    }

    public function getNewFinalAdsensReport($userID, $site, $start_date, $end_date, $ad_size, $disable_grouping)
    {
        if($site)
            $whereSite = 'sites.SiteID = ' . $site;
        else
            $whereSite = '1=1';

        if($start_date && $end_date)
            $whereDate = "DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')<='$end_date'";
        else
            $whereDate = '1=1';

        if($ad_size)
            $whereSize = 'madads_dfp_table.AdSize = ' . $ad_size;
        else
            $whereSize = '1=1';

        $select = $this->_db->select()
                            ->from('madads_dfp_table', array(
                                                        'Date'=>'madads_dfp_table.query_date',
                                                        'Impressions'=>'SUM( IFNULL(madads_dfp_table.impressions, 0) )',
                                                        'Revenue'=>'SUM( IFNULL(madads_dfp_table.revenue, 0) )',
                                                        'RevShare'=>'publisher.RevShare'
                                                    ))
                           ->joinLeft('sites', 'madads_dfp_table.SiteID=sites.SiteID', array())
                           ->joinLeft('publisher', 'publisher.ID=sites.PubID', array())
                           ->where("sites.PubID = '$userID' AND madads_dfp_table.order_name='AdSense'")
                           ->where("madads_dfp_table.AdSize IS NOT NULL AND madads_dfp_table.SiteID IS NOT NULL")
                           ->group(array("madads_dfp_table.query_date"))
                           ->order(array("madads_dfp_table.query_date DESC"))

                           ->where($whereSite)
                           ->where($whereDate)
                           ->where($whereSize)
                           ;
        $result = $this->getAdapter()->fetchAll($select);

        $resultArray = array();
        if($result){
            foreach($result as $item){
                $resultArray[$item['Date']] = $item;
            }
        }

        return $resultArray;
    }

    public function getPaidImpressionReprt($userID, $site, $start_date, $end_date, $ad_size, $disable_grouping)
    {
        if($site)
            $whereSite = 'sites.SiteID = ' . $site;
        else
            $whereSite = '1=1';

        if($start_date && $end_date)
            $whereDate = "DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')<='$end_date'";
        else
            $whereDate = '1=1';

        if($ad_size)
            $whereSize = 'madads_rubicon_table.AdSize = ' . $ad_size;
        else
            $whereSize = '1=1';

        $select = $this->_db->select()
                            ->from('madads_rubicon_table', array(
                                                        'Date'=>'madads_rubicon_table.query_date',
                                                        'Impressions'=>'SUM( IFNULL(madads_rubicon_table.allocated_impressions, 0) )',
                                                        'Revenue'=>'SUM( IFNULL(madads_rubicon_table.revenue, 0) )',
                                                        'RevShare'=>'publisher.RevShare'
                                                    ))
                           ->joinLeft('sites', 'madads_rubicon_table.SiteID=sites.SiteID', array())
                           ->joinLeft('publisher', 'publisher.ID=sites.PubID', array())
                           ->where("sites.PubID = '$userID'")
                           ->group(array("madads_rubicon_table.query_date"))
                           ->order(array("madads_rubicon_table.query_date DESC"))

                           ->where($whereSite)
                           ->where($whereDate)
                           ->where($whereSize)
                           ;
        $result = $this->getAdapter()->fetchAll($select);

        $resultArray = array();
        if($result){
            foreach($result as $item){
                $resultArray[$item['Date']] = $item;
            }
        }

        return $resultArray;
    }

    public function getPaymentsDueReport($year, $month, $filter)
    {
    		switch ($filter){
    			case 'paid':
    				$where = "payments_due.paid=1";
    				break;
    			case 'unpaid':
    				$where = "payments_due.paid IS NULL";
    				break;
    			case 'minimum':
    				$where = "payments_due.revenue<payments_due.paymentMinimum";
    				break;
                        case 'unpaid_minimum':
                                $where = "payments_due.revenue>payments_due.paymentMinimum AND payments_due.paid IS NULL";
                                break;
    			default:
    				$where="1=1";
    				break;

    		}

            $this->_name = "users";
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'id'=>'users.id',
                                                            'name'=>'users.name',
                                                            'email'=>'users.email',
                                                            'created'=>'users.created',
                                                            'sites'=>new Zend_Db_Expr("GROUP_CONCAT( DISTINCT  cast(concat( sites.SiteID, ':', sites.SiteName ) AS char ) SEPARATOR ', ' )"),
                                                            'payType'=>'users.payType',
                                                            'street1'=>'users.street1',
                                							'street2'=>'users.street2',
                                                            'country'=>'users.country',
                                                            'state'=>'users.state',
                                                            'city'=>'users.city',
                                                            'zip'=>'users.zip',
                                                            'paymentAmout'=>'users.paymentAmout',
                                                            'paymentBy'=>'users.paymentBy',
                                                            'paypalmail'=>'users.paypalmail',
                                                            'bank'=>'users.bank',
                                                            'bankName'=>'users.bankName',
                                                            'bankName2'=>'users.bankName2',
                                                            'accType'=>'users.accType',
                                                            'accNumber'=>'users.accNumber',
                                                            'accNumber2'=>'users.accNumber2',
                                                            'confirmAccNumber'=>'users.confirmAccNumber',
                                                            'routNumber'=>'users.routNumber',
                                                            'confirmRoutNumber'=>'users.confirmRoutNumber',
                                                            'bankAdress'=>'users.bankAdress',
                                                            'accName'=>'users.accName',
                                                            'accName2'=>'users.accName2',
                                                            'swift'=>'users.swift',
                                                            'iban'=>'users.iban',
                                                            'paymentsNote'=>'payments_due.note',
                                                            'paymentsComment'=>'payments_due.comment'
                                                        ))
                               ->joinLeft('sites', 'sites.PubID=users.id', array())
                               ->joinLeft('payments_due', 'payments_due.PubID=users.id', array(
                                    'revenue'=>'payments_due.revenue',
                                     'paymentProf'=>'payments_due.paymentProf',
                                     'paymentMinimum'=>'payments_due.paymentMinimum',
                                     'carried'=>'payments_due.carried',
                                     'total'=>'payments_due.total'
                               ))
                                ->joinLeft('pdf_entity', 'pdf_entity.PubID=users.id', array(
                                        'w9'=>'pdf_entity.PubID'
                                ))
                               ->where('users.active = 1')
                               ->where("DATE_FORMAT(payments_due.date, '%Y-%c')='$year-$month'")
                               ->where($where)
                               ->group('users.id');
            $result = $this->getAdapter()->fetchAll($select);

            return $result;
    }

    public function getUserPaymentDue($userID)
    {
    	$this->_name = "users";
    	$select = $this->_db->select()
    	->from($this->_name, array(
    			'id'=>'users.id',
    			'paymentAmout'=>'users.paymentAmout'
    	))
    	->joinLeft('sites', 'sites.PubID=users.id', array())
    	->joinLeft('payments_due', 'payments_due.PubID=users.id', array(
    			'revenue'=>'payments_due.revenue',
    			'paymentProf'=>'payments_due.paymentProf',
    			'paymentMinimum'=>'payments_due.paymentMinimum',
    			'carried'=>'payments_due.carried',
    			'total'=>'payments_due.total',
                        'paymentsComment'=>'payments_due.comment',
                        'paymentsNote'=>'payments_due.note',
    			'date'=>"DATE_FORMAT(payments_due.date, '%Y-%m-%d')"
    	))
    	->where("users.id = '$userID'")
    	->where("payments_due.paid IS NULL")
        ->where("payments_due.total>payments_due.paymentMinimum")
    	->order("payments_due.date ASC")
    	->limit(1)
    	;

    	$result = $this->getAdapter()->fetchRow($select);

    	return $result;
    }


    public function getUserPaymentDueByYear($userID, $year)
    {

	if($year!==null){
		$whereYear ="payments_due.year='$year'";
	}else{
		$whereYear="1=1";
	}
    	$this->_name = "users";
    	$select = $this->_db->select()
    	->from($this->_name, array(
    			'id'=>'users.id',
    			'paymentAmout'=>'users.paymentAmout'
    	))
    	->joinLeft('sites', 'sites.PubID=users.id', array())
    	->joinLeft('payments_due', 'payments_due.PubID=users.id', array(
    			'revenue'=>'payments_due.revenue',
    			'paymentProf'=>'payments_due.paymentProf',
    			'paymentMinimum'=>'payments_due.paymentMinimum',
    			'carried'=>'payments_due.carried',
    			'total'=>'payments_due.total',
                        'paymentsComment'=>'payments_due.comment',
                        'paymentsNote'=>'payments_due.note',
    			'date'=>"DATE_FORMAT(payments_due.date, '%Y-%m-%d')",
                'paid'=>"payments_due.paid"
    	))
    	->where("users.id = '$userID'")
    	->where($whereYear)
    	->order("payments_due.date DESC")
    	->group('payments_due.date');
    	;

    	$result = $this->getAdapter()->fetchAll($select);

    	return $result;
    }

    public function getUserPaymentDueByYearBeta($userID, $year)
    {

	if($year!==null){
		$whereYear ="payments_due_2.year='$year'";
	}else{
		$whereYear="1=1";
	}
    	$this->_name = "users";
    	$select = $this->_db->select()
    	->from($this->_name, array(
    			'id'=>'users.id',
    			'paymentAmout'=>'users.paymentAmout'
    	))
    	->joinLeft('sites', 'sites.PubID=users.id', array())
    	->joinLeft('payments_due_2', 'payments_due_2.PubID=users.id', array(
    			'revenue'=>'payments_due_2.revenue',
    			'paymentProf'=>'payments_due_2.paymentProf',
    			'paymentMinimum'=>'payments_due_2.paymentMinimum',
    			'carried'=>'payments_due_2.carried',
    			'total'=>'payments_due_2.total',
                'paymentsComment'=>'payments_due_2.comment',
                'paymentsNote'=>'payments_due_2.note',
                'paid'=>'payments_due_2.paid',
                'year'=>'payments_due_2.year',
                'month'=>'payments_due_2.month',                
    			'date'=>"DATE_FORMAT(payments_due_2.date, '%Y-%m-%d')"
    	))
    	->where("users.id = '$userID'")
    	->where($whereYear)
    	->order(array("payments_due_2.year DESC", "payments_due_2.month DESC"))
    	->group(array("payments_due_2.year", "payments_due_2.month"));
    	;

    	$result = $this->getAdapter()->fetchAll($select);

    	return $result;
    }

    public function getFlorPricing($userID, $site)
    {
    	if($site)
    		$whereSite = 'sites_floor_price.SiteID = ' . $site;
    	else
    		$whereSite = '1=1';

    	$select = $this->_db->select()
    	->from('sites_floor_price', array(
    			'Date'=>'sites_floor_price.date',
    			'Price'=>'SUM( IFNULL(sites_floor_price.price, 0) )'
    	))
    	->where("sites_floor_price.PubID = '$userID'")
    	->group(array("sites_floor_price.date"))
    	->order(array("sites_floor_price.date ASC"))

    	->where($whereSite)
    	;
    	$result = $this->getAdapter()->fetchAll($select);

    	$resultArray = array();
    	if($result){
    		foreach($result as $item){
    			$resultArray[$item['Date']] = $item;
    		}
    	}

    	return $resultArray;
    }

    /*
    public function getNewUserReport($userID, $site, $start_date, $end_date, $ad_size)
    {

    	if($site)
    		$whereSite = 'sites.SiteID = ' . $site;
    	else
    		$whereSite = '1=1';

    	if($start_date && $end_date){
    		$whereDateRub = "DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_rubicon_table.query_date, '%m/%d/%Y')<='$end_date'";
    		$whereDateDfp = "DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_dfp_table.query_date, '%m/%d/%Y')<='$end_date'";
    		$whereDateEst = "DATE_FORMAT(madads_estimated.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(madads_estimated.query_date, '%m/%d/%Y')<='$end_date'";
    	}
    	else{
    		$whereDateRub = '1=1';
    		$whereDateDfp = '1=1';
    		$whereDateEst = '1=1';
    	}
    	if($ad_size){
    		$whereSizeRub = 'madads_rubicon_table.AdSize = ' . $ad_size;
    		$whereSizeDfp = 'madads_dfp_table.AdSize = ' . $ad_size;
    		$whereSizeEst = 'madads_estimated.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSizeRub = '1=1';
    		$whereSizeDfp = '1=1';
    		$whereSizeEst = '1=1';
    	}

    	$null = new Zend_Db_Expr("NULL");
    	$zero = new Zend_Db_Expr("0");

    	$selectRubicon = $this->_db->select()
    					->from('madads_rubicon_table', array(
    							'PubID'=>'sites.PubID',
    							'Date'=>'madads_rubicon_table.query_date',
    							'impressionRubicon'=>'SUM(IF(madads_rubicon_table.AdSize=6, madads_rubicon_table.allocated_impressions, madads_rubicon_table.impressions))',
    							'impressionAdsense'=>$zero,
    							'impressionEstimRubicon'=>$zero,
    							'impressionEstim'=>$zero,
    							'allocated'=>'SUM(madads_rubicon_table.allocated_impressions)',
    							'revenueRubicon'=>'SUM( if(sites_floor_price.price IS NULL, ROUND((publisher.RevShare/100.0)*madads_rubicon_table.revenue, 2), ROUND( (((((publisher.RevShare/100.0)*madads_rubicon_table.revenue)*1000/allocated_impressions)-sites_floor_price.price)*sites_floor_price.percent+sites_floor_price.price)*(allocated_impressions/1000) ,2 )))',
    							'revenueAdsense'=>$zero,
    							'revenueEstimRubicon'=>$zero,
    							'revenueEstim'=>$zero
    							))
    					->joinLeft('sites', 'sites.SiteID=madads_rubicon_table.SiteID',array())
    					->joinLeft('sites_floor_price', "madads_rubicon_table.SiteID=sites_floor_price.SiteID AND DATE_FORMAT(madads_rubicon_table.query_date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d')", array())
    					->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
    					->where("sites.PubID = $userID")
    					->where($whereSite)
    					->where($whereDateRub)
    					->where($whereSizeRub)
    					->group("madads_rubicon_table.query_date")
    							;

		$selectAdsense = $this->_db->select()
					->from('madads_dfp_table', array(
							'PubID'=>'sites.PubID',
							'Date'=>'madads_dfp_table.query_date',
							'impressionRubicon'=>$zero,
							'impressionAdsense'=>'SUM(madads_dfp_table.impressions)',
							'impressionEstimRubicon'=>$zero,
							'impressionEstim'=>$zero,
							'allocated'=>$null,
							'revenueRubicon'=>$zero,
							'revenueAdsense'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_dfp_table.`revenue`, 2))',
							'revenueEstimRubicon'=>$zero,
							'revenueEstim'=>$zero
							))
					->joinLeft('sites', 'sites.SiteID=madads_dfp_table.SiteID', array())
					->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
					->where("sites.PubID = $userID")
					->where($whereSite)
					->where($whereDateDfp)
					->where($whereSizeDfp)
					->where("madads_dfp_table.order_name='AdSense'")
					->group("madads_dfp_table.query_date")
							;

		$selectEstimRubicon = $this->_db->select()
							->from('madads_dfp_table', array(
									'PubID'=>'sites.PubID',
									'Date'=>'madads_dfp_table.query_date',
									'impressionRubicon'=>$zero,
									'impressionAdsense'=>$zero,
									'impressionEstimRubicon'=>'SUM(madads_dfp_table.impressions)',
									'impressionEstim'=>$zero,
									'allocated'=>$null,
									'revenueRubicon'=>$zero,
									'revenueAdsense'=>$zero,
									'revenueEstimRubicon'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_dfp_table.`revenue`, 2))',
									'revenueEstim'=>$zero
							))
							->joinLeft('sites', 'sites.SiteID=madads_dfp_table.SiteID', array())
							->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
							->where("sites.PubID = $userID")
							->where($whereSite)
							->where($whereDateDfp)
							->where($whereSizeDfp)
							->where("madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)'")
									->group("madads_dfp_table.query_date")
									;


		$selectEstim = $this->_db->select()
									->from('madads_estimated', array(
											'PubID'=>'sites.PubID',
											'Date'=>'madads_estimated.query_date',
											'impressionRubicon'=>$zero,
											'impressionAdsense'=>$zero,
											'impressionEstimRubicon'=>$zero,
											'impressionEstim'=>'SUM(madads_estimated.impressions)',
											'allocated'=>$null,
											'revenueRubicon'=>$zero,
											'revenueAdsense'=>$zero,
											'revenueEstimRubicon'=>$zero,
											'revenueEstim'=>$zero
									))
									->joinLeft('sites', 'sites.SiteID=madads_estimated.SiteID', array())
									->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
									->where("sites.PubID = $userID")
									->where($whereSite)
									->where($whereDateEst)
									->where($whereSizeEst)
									->group("madads_estimated.query_date")
									;


		$selectUnion = $this->_db->select()
						->from(array('res'=>$this->_db->select()->union(array($selectRubicon, $selectAdsense, $selectEstimRubicon, $selectEstim), Zend_Db_Select::SQL_UNION_ALL)), array(
								'PubID',
								'Date',
								'impressions'=>'SUM(impressionAdsense) + IF(SUM(impressionRubicon)>0, SUM(impressionRubicon), IF(SUM(impressionEstimRubicon)>0, SUM(impressionEstimRubicon), SUM(impressionEstim)))',
								'allocated_impressions'=>'SUM(allocated)',
								'revenue'=>'if((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), ROUND(SUM(revenueEstim)+SUM(revenueEstimRubicon)+SUM(revenueAdsense), 2), ROUND(SUM(revenueRubicon)+SUM(revenueAdsense), 2))',
								'estimated'=>"IF(DATE_FORMAT(Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)"
								))
						->group("Date")
						->order("Date DESC")
						;

		return $this->getAdapter()->fetchAll($selectUnion);

    }
    */

    public function getNewUserReport($userID, $site, $start_date, $end_date, $ad_size)
    {

    	if($site)
    		$whereSite = 'sites.SiteID = ' . $site;
    	else
    		$whereSite = '1=1';

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

    	$null = new Zend_Db_Expr("NULL");
    	$zero = new Zend_Db_Expr("0");

    	$selectRubicon = $this->_db->select()
    	->from('madads_rubicon_table', array(
    			'PubID'=>'sites.PubID',
    			'SiteID'=>'sites.SiteID',
    			'floor_pricing'=>'sites.floor_pricing',
    			'AdSize'=>'madads_rubicon_table.AdSize',
    			'Date'=>'madads_rubicon_table.query_date',
    			'impressionRubicon'=>'SUM(IF(madads_rubicon_table.AdSize=6, madads_rubicon_table.allocated_impressions, madads_rubicon_table.impressions))',
    			'impressionAdsense'=>$zero,
    			'impressionEstimRubicon'=>$zero,
    			'impressionEstim'=>$zero,
    			'impressionNofill'=>$zero,
    			'allocated'=>'SUM(madads_rubicon_table.allocated_impressions)',
    			'revenueRubicon'=>'SUM( if(sites_floor_price.price IS NULL, ROUND((publisher.RevShare/100.0)*madads_rubicon_table.revenue, 2), ROUND( (((((publisher.RevShare/100.0)*madads_rubicon_table.revenue)*1000/allocated_impressions)-sites_floor_price.price)*sites_floor_price.percent+sites_floor_price.price)*(allocated_impressions/1000) ,2 )))',
    			'revenueAdsense'=>$zero,
    			'revenueEstimRubicon'=>$zero,
    			'revenueEstim'=>$zero
    	))
    	->joinLeft('sites', 'sites.SiteID=madads_rubicon_table.SiteID',array())
    	->joinLeft('sites_floor_price', "madads_rubicon_table.SiteID=sites_floor_price.SiteID AND DATE_FORMAT(madads_rubicon_table.query_date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d')", array())
    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
    	->where("sites.PubID = $userID")
    	->where($whereSite)
    	->where($whereDateRub)
    	->where($whereSizeRub)
    	->group(array("madads_rubicon_table.SiteID","madads_rubicon_table.AdSize","madads_rubicon_table.query_date"))
    	;

    	$selectAdsense = $this->_db->select()
    	->from('madads_dfp_table', array(
    			'PubID'=>'sites.PubID',
    			'SiteID'=>'sites.SiteID',
    			'floor_pricing'=>'sites.floor_pricing',
    			'AdSize'=>'madads_dfp_table.AdSize',
    			'Date'=>'madads_dfp_table.query_date',
    			'impressionRubicon'=>$zero,
    			'impressionAdsense'=>'SUM(madads_dfp_table.impressions)',
    			'impressionEstimRubicon'=>$zero,
    			'impressionEstim'=>$zero,
    			'impressionNofill'=>$zero,
    			'allocated'=>$null,
    			'revenueRubicon'=>$zero,
    			'revenueAdsense'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_dfp_table.`revenue`, 2))',
    			'revenueEstimRubicon'=>$zero,
    			'revenueEstim'=>$zero
    	))
    	->joinLeft('sites', 'sites.SiteID=madads_dfp_table.SiteID', array())
    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
    	->where("sites.PubID = $userID")
    	->where($whereSite)
    	->where($whereDateDfp)
    	->where($whereSizeDfp)
    	->where("madads_dfp_table.order_name='AdSense'")
    			->group(array("madads_dfp_table.SiteID","madads_dfp_table.AdSize","madads_dfp_table.query_date"))
    			;

    			$selectEstimRubicon = $this->_db->select()
    			->from('madads_dfp_table', array(
    					'PubID'=>'sites.PubID',
    					'SiteID'=>'sites.SiteID',
    					'floor_pricing'=>'sites.floor_pricing',
    					'AdSize'=>'madads_dfp_table.AdSize',
    					'Date'=>'madads_dfp_table.query_date',
    					'impressionRubicon'=>$zero,
    					'impressionAdsense'=>$zero,
    					'impressionEstimRubicon'=>'SUM(madads_dfp_table.impressions)',
    					'impressionEstim'=>$zero,
    					'impressionNofill'=>$zero,
    					'allocated'=>$null,
    					'revenueRubicon'=>$zero,
    					'revenueAdsense'=>$zero,
    					'revenueEstimRubicon'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_dfp_table.`revenue`, 2))',
    					'revenueEstim'=>$zero
    					))
    							->joinLeft('sites', 'sites.SiteID=madads_dfp_table.SiteID', array())
    							->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
    					->where("sites.PubID = $userID")
    					->where($whereSite)
    					->where($whereDateDfp)
    							->where($whereSizeDfp)
    							->where("madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)'")
    							->group(array("madads_dfp_table.SiteID","madads_dfp_table.AdSize","madads_dfp_table.query_date"))
    							;


    							$selectEstim = $this->_db->select()
    							->from('madads_estimated', array(
    	'PubID'=>'sites.PubID',
    									'SiteID'=>'sites.SiteID',
    									'floor_pricing'=>'sites.floor_pricing',
    									'AdSize'=>'madads_estimated.AdSize',
    	'Date'=>'madads_estimated.query_date',
    	'impressionRubicon'=>$zero,
    	'impressionAdsense'=>$zero,
    	'impressionEstimRubicon'=>$zero,
    	'impressionEstim'=>'SUM(madads_estimated.impressions)',
		'impressionNofill'=>$zero,
    	'allocated'=>$null,
    	'revenueRubicon'=>$zero,
    	'revenueAdsense'=>$zero,
    	'revenueEstimRubicon'=>$zero,
    	'revenueEstim'=>$zero
    	))
    	->joinLeft('sites', 'sites.SiteID=madads_estimated.SiteID', array())
    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
    	->where("sites.PubID = $userID")
    	->where($whereSite)
    	->where($whereDateEst)
    	->where($whereSizeEst)
    	->group(array("madads_estimated.SiteID","madads_estimated.AdSize","madads_estimated.query_date"))
    	;


    	$selectFill = $this->_db->select()
    	->from('madads_nonfilled', array(
    			'PubID'=>'sites.PubID',
    			'SiteID'=>'sites.SiteID',
    			'floor_pricing'=>'sites.floor_pricing',
    			'AdSize'=>'madads_nonfilled.AdSize',
    			'Date'=>'madads_nonfilled.query_date',
    			'impressionRubicon'=>$zero,
    			'impressionAdsense'=>$zero,
    			'impressionEstimRubicon'=>$zero,
    			'impressionEstim'=>$zero,
    			'impressionNofill'=>'SUM(madads_nonfilled.impressions)',
    			'allocated'=>$null,
    			'revenueRubicon'=>$zero,
    			'revenueAdsense'=>$zero,
    			'revenueEstimRubicon'=>$zero,
    			'revenueEstim'=>$zero
    	))
    	->joinLeft('sites', 'sites.SiteID=madads_nonfilled.SiteID', array())
    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
    	->where("sites.PubID = $userID")
    	->where($whereSite)
    	->where($whereDateFill)
    	->where($whereSizeFill)
    	->group(array("madads_nonfilled.SiteID","madads_nonfilled.AdSize","madads_nonfilled.query_date"))
    	;

    	$selectUnion = $this->_db->select()
    	->from(array('res'=>$this->_db->select()->union(array($selectRubicon, $selectAdsense, $selectEstimRubicon, $selectEstim, $selectFill), Zend_Db_Select::SQL_UNION_ALL)), array(
    			'PubID'=>'res.PubID',
    			'SiteID'=>'res.SiteID',
    			'floor_pricing'=>'res.floor_pricing',
    			'AdSize'=>'res.AdSize',
    			'Date'=>'res.Date',
    			'impressions'=>'SUM(impressionAdsense) + IF(SUM(impressionRubicon)>0, SUM(impressionRubicon), IF(SUM(impressionEstimRubicon)>0, SUM(impressionEstimRubicon), SUM(impressionEstim)))',
    			'allocated_impressions'=>'if(floor_pricing=1 AND SUM(allocated) IS NULL, SUM(impressionAdsense) + IF(SUM(impressionRubicon)>0, SUM(impressionRubicon), IF(SUM(impressionEstimRubicon)>0, SUM(impressionEstimRubicon), SUM(impressionEstim)))-SUM(impressionNofill), SUM(allocated))',
    			'revenue'=>'if((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), ROUND(SUM(revenueEstim)+SUM(revenueEstimRubicon)+SUM(revenueAdsense), 2), ROUND(SUM(revenueRubicon)+SUM(revenueAdsense), 2))',
    			'estimated'=>"IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)"
    			))
    	->joinLeft('sites_floor_price', "(sites_floor_price.SiteID=res.SiteID AND DATE_FORMAT(res.`Date`, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d') )", array())

    					->group(array("res.Date"))
    					->order("res.Date DESC")
    							;

    							return $this->getAdapter()->fetchAll($selectUnion);

    }

    public function getNewUserReportVersion_2($site, $start_date, $end_date, $ad_size, $allSites)
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

    	$null = new Zend_Db_Expr("NULL");
    	$zero = new Zend_Db_Expr("0");

    	$selectRubicon = $this->_db->select()
    	->from('madads_rubicon_table', array(
    			'SiteID'=>'madads_rubicon_table.SiteID',
    			'AdSize'=>'madads_rubicon_table.AdSize',
    			'Date'=>'madads_rubicon_table.query_date',
    			'impressionRubicon'=>'SUM( IF(madads_rubicon_table.impressions IS NULL, 0, madads_rubicon_table.impressions) )',
    			'impressionAdsense'=>$zero,
    			'impressionEstimRubicon'=>$zero,
    			'impressionAdExchange'=>$zero,
    			'impressionEstim'=>$zero,
    			'impressionNofill'=>$zero,
    			'allocated'=>'SUM( IF(madads_rubicon_table.allocated_impressions IS NULL, 0, madads_rubicon_table.allocated_impressions) )',
    			'revenueRubicon'=>'SUM( IF(madads_rubicon_table.revenue IS NULL, 0, madads_rubicon_table.revenue) )',
    			'revenueAdsense'=>$zero,
    			'revenueEstimRubicon'=>$zero,
    			'revenueAdExchange'=>$zero,
    			'revenueEstim'=>$zero
    	))
    	->where("madads_rubicon_table.SiteID IN (?)", $site)
    	->where($whereDateRub)
    	->where($whereSizeRub)
    	->group(array("madads_rubicon_table.SiteID","madads_rubicon_table.AdSize","madads_rubicon_table.query_date"))
    	;

    	$selectAdsense = $this->_db->select()
    	->from('madads_dfp_table', array(
    			'SiteID'=>'madads_dfp_table.SiteID',
    			'AdSize'=>'madads_dfp_table.AdSize',
    			'Date'=>'madads_dfp_table.query_date',
    			'impressionRubicon'=>$zero,
    			'impressionAdsense'=>'SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) )',
    			'impressionEstimRubicon'=>$zero,
    			'impressionAdExchange'=>$zero,
    			'impressionEstim'=>$zero,
    			'impressionNofill'=>$zero,
    			'allocated'=>$null,
    			'revenueRubicon'=>$zero,
    			'revenueAdsense'=>'SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) )',
    			'revenueEstimRubicon'=>$zero,
    			'revenueAdExchange'=>$zero,
    			'revenueEstim'=>$zero
    	))
    	->where("madads_dfp_table.SiteID IN (?)", $site)
    	->where($whereDateDfp)
    	->where($whereSizeDfp)
    	->where("madads_dfp_table.order_name='AdSense'")
    	->group(array("madads_dfp_table.SiteID","madads_dfp_table.AdSize","madads_dfp_table.query_date"))
    	;

    	$selectEstimRubicon = $this->_db->select()
    	->from('madads_dfp_table', array(
    			'SiteID'=>'madads_dfp_table.SiteID',
    			'AdSize'=>'madads_dfp_table.AdSize',
    			'Date'=>'madads_dfp_table.query_date',
    			'impressionRubicon'=>$zero,
    			'impressionAdsense'=>$zero,
    			'impressionEstimRubicon'=>'SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) )',
    			'impressionAdExchange'=>$zero,
    			'impressionEstim'=>$zero,
    			'impressionNofill'=>$zero,
    			'allocated'=>$null,
    			'revenueRubicon'=>$zero,
    			'revenueAdsense'=>$zero,
    			'revenueEstimRubicon'=>'SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) )',
    			'revenueAdExchange'=>$zero,
    			'revenueEstim'=>$zero
    	))
    	->where("madads_dfp_table.SiteID IN (?)", $site)
    	->where($whereDateDfp)
    	->where($whereSizeDfp)
    	->where("madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)'")
    	->group(array("madads_dfp_table.SiteID","madads_dfp_table.AdSize","madads_dfp_table.query_date"))
    	;

    	$selectAdExchange = $this->_db->select()
    	->from('madads_dfp_table', array(
    			'SiteID'=>'madads_dfp_table.SiteID',
    			'AdSize'=>'madads_dfp_table.AdSize',
    			'Date'=>'madads_dfp_table.query_date',
    			'impressionRubicon'=>$zero,
    			'impressionAdsense'=>$zero,
    			'impressionEstimRubicon'=>$zero,
    			'impressionAdExchange'=>'SUM( IF(madads_dfp_table.impressions IS NULL, 0, madads_dfp_table.impressions) )',
    			'impressionEstim'=>$zero,
    			'impressionNofill'=>$zero,
    			'allocated'=>$null,
    			'revenueRubicon'=>$zero,
    			'revenueAdsense'=>$zero,
    			'revenueEstimRubicon'=>$zero,
    			'revenueAdExchange'=>'SUM( IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue) )',
    			'revenueEstim'=>$zero
    	))
    	->where("madads_dfp_table.SiteID IN (?)", $site)
    	->where($whereDateDfp)
    	->where($whereSizeDfp)
    	->where("madads_dfp_table.order_name='MAM-Google-AdExchange'")
    	->group(array("madads_dfp_table.SiteID","madads_dfp_table.AdSize","madads_dfp_table.query_date"))
    	;

    	$selectEstim = $this->_db->select()
    	->from('madads_estimated', array(
    			'SiteID'=>'madads_estimated.SiteID',
    			'AdSize'=>'madads_estimated.AdSize',
    			'Date'=>'madads_estimated.query_date',
    			'impressionRubicon'=>$zero,
    			'impressionAdsense'=>$zero,
    			'impressionEstimRubicon'=>$zero,
    			'impressionAdExchange'=>$zero,
    			'impressionEstim'=>'SUM( IF(madads_estimated.impressions IS NULL, 0, madads_estimated.impressions) )',
    			'impressionNofill'=>$zero,
    			'allocated'=>$null,
    			'revenueRubicon'=>$zero,
    			'revenueAdsense'=>$zero,
    			'revenueEstimRubicon'=>$zero,
    			'revenueAdExchange'=>$zero,
    			'revenueEstim'=>$zero
    	))
    	->where("madads_estimated.SiteID IN (?)", $site)
    	->where($whereDateEst)
    	->where($whereSizeEst)
    	->group(array("madads_estimated.SiteID","madads_estimated.AdSize","madads_estimated.query_date"))
    	;


    	$selectFill = $this->_db->select()
    	->from('madads_nonfilled', array(
    			'SiteID'=>'madads_nonfilled.SiteID',
    			'AdSize'=>'madads_nonfilled.AdSize',
    			'Date'=>'madads_nonfilled.query_date',
    			'impressionRubicon'=>$zero,
    			'impressionAdsense'=>$zero,
    			'impressionEstimRubicon'=>$zero,
    			'impressionAdExchange'=>$zero,
    			'impressionEstim'=>$zero,
    			'impressionNofill'=>'SUM( IF(madads_nonfilled.impressions IS NULL, 0, madads_nonfilled.impressions) )',
    			'allocated'=>$null,
    			'revenueRubicon'=>$zero,
    			'revenueAdsense'=>$zero,
    			'revenueEstimRubicon'=>$zero,
    			'revenueAdExchange'=>$zero,
    			'revenueEstim'=>$zero
    	))
    	->where("madads_nonfilled.SiteID IN (?)", $site)
    	->where($whereDateFill)
    	->where($whereSizeFill)
    	->group(array("madads_nonfilled.SiteID","madads_nonfilled.AdSize","madads_nonfilled.query_date"))
    	;
        if($allSites===true){
            $selectUnion = $this->_db->select()
            ->from(array('res'=>$this->_db->select()->union(array($selectRubicon, $selectAdsense, $selectEstimRubicon, $selectAdExchange, $selectEstim, $selectFill), Zend_Db_Select::SQL_UNION_ALL)), array(
                            'Date'=>'res.Date',
                            'estimated'=>"IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)",
                            'impressionRubicon'=>'res.impressionRubicon',
                            'impressionAdExchange'=>'res.impressionAdExchange',
                            'impressionEstimS'=>'res.impressionEstim',
                            'impressions'=>"
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
                                            END
                                                    ",
                            'paid_impressions'=>"
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
                                            END
                                                    ",
                            'revenueRubicon'=>'res.revenueRubicon',
                            'revenueAdExchange'=>'res.revenueAdExchange',
                            'revenueEstim'=>$zero,
                            'revenue'=>"
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
                                            END
                                                    "
            ))
            ->joinLeft('sites', "sites.SiteID=res.SiteID", array())
            ->joinLeft('tags', "tags.site_id=res.SiteID", array())
            ->joinLeft('users_revshare', "(sites.PubID=users_revshare.PubID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(users_revshare.date, '%Y-%m-%d'))", array())
            ->joinLeft('sites_floor_price', "(sites_floor_price.SiteID=sites.SiteID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d'))", array())

            ->group(array("res.Date"))
            ->order("res.Date DESC")
            ;
        }else{
            $selectUnion = $this->_db->select()
            ->from(array('res'=>$this->_db->select()->union(array($selectRubicon, $selectAdsense, $selectEstimRubicon, $selectAdExchange, $selectEstim, $selectFill), Zend_Db_Select::SQL_UNION_ALL)), array(
                            'Date'=>'res.Date',
                            'estimated'=>"IF(DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(NOW(), '%Y-%m-%d') - INTERVAL 7 DAY, IF((SUM(impressionEstimRubicon)>0 AND SUM(impressionRubicon)=0) OR (SUM(impressionEstimRubicon)=0 AND SUM(impressionRubicon)=0 AND SUM(impressionAdsense)=0), 1, 0), 0)",
                            'impressionRubicon'=>'res.impressionRubicon',
                            'impressionAdExchange'=>'res.impressionAdExchange',
                            'impressionEstimS'=>'res.impressionEstim',
                            'impressions'=>"
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
                                                            /*pay*/			IF(sites.cpm IS NOT NULL, impressionEstim,
                                                            /*flor*/		IF(sites.floor_pricing=1, impressionRubicon,
                                                            /*rubicon*/		IF(tags.type=2, impressionRubicon,
                                    /*google admanager*/		IF(tags.type=1, impressionAdsense+impressionRubicon+impressionAdExchange,
                                    /*google admanager new*/	IF(tags.type=4, impressionAdsense+impressionRubicon+impressionAdExchange,
                                                            /*others*/		impressionAdsense+impressionRubicon )))))
                                                    ))
                                            END
                                                    ",
                            'paid_impressions'=>"
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
                                            END
                                                    ",
                            'revenueRubicon'=>'res.revenueRubicon',
                            'revenueAdExchange'=>'res.revenueAdExchange',
                            'revenueEstim'=>$zero,
                            'revenue'=>"
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
                                            END
                                                    "
            ))
            ->joinLeft('sites', "sites.SiteID=res.SiteID", array())
            ->joinLeft('tags', "tags.site_id=res.SiteID", array())
            ->joinLeft('users_revshare', "(sites.PubID=users_revshare.PubID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(users_revshare.date, '%Y-%m-%d'))", array())
            ->joinLeft('sites_floor_price', "(sites_floor_price.SiteID=sites.SiteID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d'))", array())

            ->group(array("res.Date"))
            ->order("res.Date DESC")
            ;
        }
    	return $this->getAdapter()->fetchAll($selectUnion);

    }

    public function getNewUserReportVersion_3($site, $start_date, $end_date, $ad_size)
    {
    	if($ad_size){
    		$whereSize = 'users_reports_final.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSize = '1=1';
    	}


            $this->_name = "users_reports_final";
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'query_date'=>'users_reports_final.query_date',
                                                            'paid_impressions'=>'SUM(users_reports_final.paid_impressions)',
                                                            'impressions'=>'SUM(users_reports_final.impressions)',
                                                            'revenue'=>'ROUND(SUM(users_reports_final.revenue),2)',
                                                            'estimated'=>'users_reports_final.estimated'
                                                        ))
                               ->where("DATE_FORMAT(users_reports_final.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(users_reports_final.query_date, '%m/%d/%Y')<='$end_date'")
                               ->where($whereSize)
                               ->where("users_reports_final.SiteID IN (?)", $site)
                               ->group(array("users_reports_final.query_date"))
                               ->order("users_reports_final.query_date DESC");
            $result = $this->getAdapter()->fetchAll($select);

            return $result;
    }

    public function getNewUserReportShowDemandVersion_3($site, $start_date, $end_date, $ad_size)
    {
    	if($ad_size){
    		$whereSize = 'users_reports_final.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSize = '1=1';
    	}


            $this->_name = "users_reports_final";
            $select = $this->_db->select()
                                ->from($this->_name, array(
                                                            'query_date'=>'users_reports_final.query_date',
                                                            'impressions_estimated'=>'users_reports_final.impressions_estimated',
                                                            'impressions_nonfilled'=>'users_reports_final.impressions_nonfilled',
                                                            'impressions_AdExcchange'=>'users_reports_final.impressions_AdExcchange',
                                                            'revenue_AdExcchange'=>'users_reports_final.revenue_AdExcchange',
                                                            'impressions_Adsense'=>'users_reports_final.impressions_Adsense',
                                                            'revenue_Adsense'=>'users_reports_final.revenue_Adsense',
                                                            'impressions_Rubicon'=>'users_reports_final.impressions_Rubicon',
                                                            'revenue_Rubicon'=>'users_reports_final.revenue_Rubicon',
                                                            'impressions_RubiconComics'=>'users_reports_final.impressions_RubiconComics',
                                                            'revenue_RubiconComics'=>'users_reports_final.revenue_RubiconComics',
                                                            'impressions_RubiconCsv'=>'users_reports_final.impressions_RubiconCsv',
                                                            'paid_impressions_RubiconCsv'=>'users_reports_final.paid_impressions_RubiconCsv',
                                                            'revenue_RubiconCsv'=>'users_reports_final.revenue_RubiconCsv',
                                                            'paid_impressions'=>'SUM(users_reports_final.paid_impressions)',
                                                            'impressions'=>'SUM(users_reports_final.impressions)',
                                                            'revenue'=>'ROUND(SUM(users_reports_final.revenue),2)',
                                                            'estimated'=>'users_reports_final.estimated'
                                                        ))
                               ->where("DATE_FORMAT(users_reports_final.query_date, '%m/%d/%Y')>='$start_date' AND DATE_FORMAT(users_reports_final.query_date, '%m/%d/%Y')<='$end_date'")
                               ->where($whereSize)
                               ->where("users_reports_final.SiteID IN (?)", $site)
                               ->group(array("users_reports_final.query_date"))
                               ->order("users_reports_final.query_date DESC");
            $result = $this->getAdapter()->fetchAll($select);

            return $result;
    }

    public function getNewUserEstimatedReport($userID, $site, $ad_size, array $dates)
    {
    	if($site)
    		$whereSite = 'sites.SiteID = ' . $site;
    	else
    		$whereSite = '1=1';


    	if($ad_size){
    		$whereSizeDfp = 'madads_dfp_table.AdSize = ' . $ad_size;
    	}
    	else{
    		$whereSizeDfp = '1=1';
    	}

    	$null = new Zend_Db_Expr("NULL");

    	$selectDfp = $this->_db->select()
    	->from('madads_dfp_table', array(
    			'PubID'=>'sites.PubID',
    			'Date'=>'madads_dfp_table.query_date',
    			'impressions'=>'SUM(madads_dfp_table.impressions)',
    			'allocated'=>$null,
    			'revenue'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_dfp_table.revenue, 2))'
    	))
    	->joinLeft('sites', 'sites.SiteID=madads_dfp_table.SiteID', array())
    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
    	->where("sites.PubID = $userID")
    	->where($whereSite)
    	->where($whereSizeDfp)
    	->where("madads_dfp_table.order_name='AdSense' OR madads_dfp_table.order_name='MAM-Rubicon (Profile Customization)'")
    	->where('madads_dfp_table.query_date IN(?)', $dates)
    	->group("madads_dfp_table.query_date")
    			;

    	return $this->getAdapter()->fetchAll($selectDfp);
    }

    public function generateNetworkStats($year, $month)
    {
    	$date = "$month/$year";

    	$selectRubicon = $this->_db->select()
    	->from('madads_rubicon_table', array(
    			'PubID'=>'sites.PubID',
    			'SiteID'=>'madads_rubicon_table.SiteID',
    			'AdSize'=>'madads_rubicon_table.AdSize',
    			'Date'=>'madads_rubicon_table.query_date',
    			'revenue'=>'SUM( ROUND((publisher.RevShare/100.0)*madads_rubicon_table.revenue, 2) - ROUND( (((((publisher.RevShare/100.0)*madads_rubicon_table.revenue)*1000/allocated_impressions)-sites_floor_price.price)*sites_floor_price.percent+sites_floor_price.price)*(allocated_impressions/1000) ,2 ) )'
    	))
    	->joinLeft('sites', 'sites.SiteID=madads_rubicon_table.SiteID',array())
    	->joinLeft('sites_floor_price', "madads_rubicon_table.SiteID=sites_floor_price.SiteID AND DATE_FORMAT(madads_rubicon_table.query_date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d')", array())
    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
    	->where("sites.floor_pricing = 1")
    	->where("DATE_FORMAT(madads_rubicon_table.query_date, '%c/%Y')='$date'")
    	->where("revenue IS NOT NULL")
    	->group(array("madads_rubicon_table.SiteID", "madads_rubicon_table.AdSize", "madads_rubicon_table.query_date"))
    	->having("revenue IS NOT NULL AND revenue>0")
    	;
    	return $this->getAdapter()->fetchAll($selectRubicon);
    }

    public function clearNetworkStats($year, $month)
    {
    	$date = "$month/$year";
    	$this->_name = 'network_stats';
    	$this->delete(array("DATE_FORMAT(network_stats.Date, '%c/%Y')='$date'"));
    }

    public function insertNetworkStats(array $data)
    {
    	$this->_name = 'network_stats';
    	$this->_primary = 'PubID';

    	$result = array(
    			'PubID'=>$data['PubID'],
    			'SiteID'=>$data['SiteID'],
    			'AdSize'=>$data['AdSize'],
    			'Date'=>$data['Date'],
    			'revenue'=>$data['revenue']
    	);

    	$this->insert($result);

    }

    public function getNetworkStats($year, $month)
    {
    	$date = "$month/$year";

    	$selectRubicon = $this->_db->select()
    	->from('network_stats', array(
    			'Date'=>'network_stats.Date',
    			'revenue'=>'ROUND(SUM(network_stats.revenue), 2)'
    	))
    	->where("DATE_FORMAT(network_stats.Date, '%c/%Y')='$date'")
    	->group(array("network_stats.Date"))
    	;
    	return $this->getAdapter()->fetchAll($selectRubicon);

    }

	public function getUsersFlorPricing($userID, $site, $start_date, $end_date)
	{

    	if($site)
    		$whereSite = 'sites_floor_price.SiteID = ' . $site;
    	else
    		$whereSite = '1=1';

    	$selectPricing = $this->_db->select()
    	->from('sites_floor_price', array(
    			'price'=>'ROUND(SUM(sites_floor_price.price), 2)'
    	))
		->where("sites_floor_price.PubID = '$userID'")
		->where($whereSite)
    	->where("'$start_date'>=DATE_FORMAT(sites_floor_price.date, '%m/%d/%Y')")
    	->group(array("sites_floor_price.SiteID", "sites_floor_price.PubID"))
    	;
    	return $this->getAdapter()->fetchAll($selectPricing);

	}

	public function getSitesPayEcpm($userID, $site)
	{
		if($site)
			$whereSite = 'sites.SiteID = ' . $site;
		else
			$whereSite = '1=1';

		$selectPricing = $this->_db->select()
		->from('sites', array(
				'ecpm'=>'ROUND(SUM(sites.cpm), 2)'
		))
		->where("sites.PubID = '$userID'")
		->where($whereSite)
		->group(array("sites.SiteID"))
		;
		return $this->getAdapter()->fetchRow($selectPricing);
	}

//	public function getNetworkStatsAdsense($year, $month)
//	{
//		$date = "$year-$month";
//
//		$selectAdsense = $this->_db->select()
//		->from('madads_dfp_table', array(
//				'Date'=>'madads_dfp_table.query_date',
//				'revenue'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_dfp_table.`revenue`, 2))',
//		))
//		->joinLeft('sites', 'sites.SiteID=madads_dfp_table.SiteID', array())
//		->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
//		->where("DATE_FORMAT(madads_dfp_table.query_date, '%Y-%c')='$date'")
//		->where("madads_dfp_table.order_name='AdSense'")
//		->group(array("madads_dfp_table.query_date"))
//		;
//
//		return $this->getAdapter()->fetchAll($selectAdsense);
//	}
//
//	public function getNetworkStatsRubicon($year, $month)
//	{
//		$date = "$year-$month";
//
//		$selectRub = $this->_db->select()
//		->from('madads_rubicon_table', array(
//				'Date'=>'madads_rubicon_table.query_date',
//				'revenue'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_rubicon_table.`revenue`, 2))',
//		))
//		->joinLeft('sites', 'sites.SiteID=madads_rubicon_table.SiteID', array())
//		->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
//		->where("DATE_FORMAT(madads_rubicon_table.query_date, '%Y-%c')='$date'")
//		->group(array("madads_rubicon_table.query_date"))
//		;
//
//		return $this->getAdapter()->fetchAll($selectRub);
//	}
//
//	public function getNetworkStatsAll($year, $month)
//	{
//		$date = "$year-$month";
//
//    	$selectFlor = $this->_db->select()
//    	->from('madads_rubicon_table', array(
//    			'Date'=>'madads_rubicon_table.query_date',
//    			'revenue'=>'SUM( if(sites_floor_price.price IS NULL, 0, ROUND( (((((publisher.RevShare/100.0)*madads_rubicon_table.revenue)*1000/allocated_impressions)-sites_floor_price.price)*sites_floor_price.percent+sites_floor_price.price)*(allocated_impressions/1000) ,2 )))',
//    	))
//    	->joinLeft('sites', 'sites.SiteID=madads_rubicon_table.SiteID',array())
//    	->joinLeft('sites_floor_price', "madads_rubicon_table.SiteID=sites_floor_price.SiteID AND DATE_FORMAT(madads_rubicon_table.query_date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d')", array())
//    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
//    	->where("DATE_FORMAT(madads_rubicon_table.query_date, '%Y-%c')='$date'")
//    	->group(array("madads_rubicon_table.query_date"))
//    	;
//
//    	$selectRub = $this->_db->select()
//    	->from('madads_rubicon_table', array(
//    			'Date'=>'madads_rubicon_table.query_date',
//    			'revenue'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_rubicon_table.`revenue`, 2))',
//    	))
//    	->joinLeft('sites', 'sites.SiteID=madads_rubicon_table.SiteID', array())
//    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
//    	->where("DATE_FORMAT(madads_rubicon_table.query_date, '%Y-%c')='$date'")
//    	->group(array("madads_rubicon_table.query_date"))
//    	;
//
//    	$selectAdsense = $this->_db->select()
//    	->from('madads_dfp_table', array(
//    			'Date'=>'madads_dfp_table.query_date',
//    			'revenue'=>'SUM(ROUND((publisher.RevShare/100.0)*madads_dfp_table.`revenue`, 2))',
//    	))
//    	->joinLeft('sites', 'sites.SiteID=madads_dfp_table.SiteID', array())
//    	->joinLeft('publisher', 'sites.PubID=publisher.ID', array())
//    	->where("DATE_FORMAT(madads_dfp_table.query_date, '%Y-%c')='$date'")
//    	->where("madads_dfp_table.order_name='AdSense'")
//    	->group(array("madads_dfp_table.query_date"))
//    	;
//
//    	$selectUnion = $this->_db->select()
//    	->from(array('res'=>$this->_db->select()->union(array($selectFlor, $selectRub, $selectAdsense), Zend_Db_Select::SQL_UNION_ALL)), array(
//    			'Date'=>'res.Date',
//    			'revenue'=>'ROUND(SUM(revenue), 2)',
//    			))
//    					->group(array("res.Date"))
//    					->order("res.Date DESC")
//    							;
//
//    							return $this->getAdapter()->fetchAll($selectUnion);
//	}

	public function deleteShare($pubID, $date)
	{
		$this->_name = 'users_revshare';
		$this->_primary = 'PubID';
		$this->delete(array("PubID = '$pubID'"));
	}

	public function saveShare($pubID, $date, $price)
	{
		$this->_name = 'users_revshare';
		$this->_primary = 'PubID';

		$result = array(
				'PubID'=>$pubID,
				'date' => $date,
				'RevShare'=>$price
		);

		return $this->insert($result);
	}

	public function getUserShare($id)
	{
		$this->_name = 'users_revshare';
		$this->_primary = 'PubID';

		$select = $this->_db->select()
		->from('users_revshare', array(
				'date'=>'users_revshare.date',
				'price'=>'users_revshare.RevShare',
		))
		->where("users_revshare.PubID='$id'")
		;

		return $this->getAdapter()->fetchAll($select);
	}

        public function getNetworkStatsV2($year, $month, $rubicon_14)
        {
               $sql = null;
               $start = date('Y-m-d', strtotime($year.'-'.$month));
               $str = preg_split('/[-]/', $start);
               $end = date("Y-m-d", mktime(0, 0, 0, $str[1] + 1 /* month */, $str[2], $str[0]));

            if($rubicon_14 == false){

               $sql = "SELECT

                            res.Date,

                                ROUND(SUM( res.revenueAdsense + res.revenueAdExchange ), 2) AS GrossAdExchange,
                                ROUND(SUM( IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue ) * 0.86 ), 2) AS GrossRubicon,
                                ROUND(SUM( res.revenueAdsense + res.revenueAdExchange + IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue ) * 0.86 ), 2) AS GrossAll,

                                ROUND(SUM( (res.revenueAdsense + res.revenueAdExchange) * (users_revshare.RevShare/100.0) ), 2) AS PaidAdExchange,
                                ROUND(SUM( IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue * (users_revshare.RevShare/100.0)) ), 2) AS PaidRubicon,
                                ROUND(SUM( (res.revenueAdsense + res.revenueAdExchange) * (users_revshare.RevShare/100.0) + IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue * (users_revshare.RevShare/100.0))  ), 2) AS PaidAll,

                                ROUND(SUM( (res.revenueAdsense + res.revenueAdExchange) - ((res.revenueAdsense + res.revenueAdExchange) * (users_revshare.RevShare/100.0)) ), 2) AS ProfitAdExchange,
                                ROUND(SUM( IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue ) * 0.86 - ( IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue * (users_revshare.RevShare/100.0)) ) ), 2)	AS ProfitRubicon,
                                ROUND(SUM( (res.revenueAdsense + res.revenueAdExchange + IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue ) * 0.86) - ( (res.revenueAdsense + res.revenueAdExchange) * (users_revshare.RevShare/100.0)  +  IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue * (users_revshare.RevShare/100.0) ) ) ), 2) AS ProfitAll

                        FROM ( SELECT madads_dfp_table.SiteID AS SiteID,
                                      madads_dfp_table.query_date AS Date,

                                   SUM(IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue)) AS revenueAdsense,
                                   0 AS revenueAdExchange,
                                   0 AS revenue,
                                   0 AS allocated

                                   FROM madads_dfp_table

                                   WHERE madads_dfp_table.query_date >= '".$start."'
                                     AND madads_dfp_table.query_date < '".$end."'
                                     AND madads_dfp_table.order_name = 'Adsense'

                                   GROUP BY madads_dfp_table.query_date, madads_dfp_table.SiteID

                                 UNION ALL

                               SELECT madads_dfp_table.SiteID AS SiteID,
                                          madads_dfp_table.query_date AS Date,

                                   0 AS revenueAdsense,
                                   SUM(IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue)) AS revenueAdExchange,
                                   0 AS revenue,
                                   0 AS allocated

                                   FROM madads_dfp_table

                                   WHERE madads_dfp_table.query_date >= '".$start."'
                                     AND madads_dfp_table.query_date < '".$end."'
                                     AND madads_dfp_table.order_name = 'MAM-Google-AdExchange'

                                   GROUP BY madads_dfp_table.query_date, madads_dfp_table.SiteID

                                 UNION ALL

                               SELECT madads_rubicon_table.SiteID AS SiteID,
                                          madads_rubicon_table.query_date  AS Date,

                                   0 AS revenueAdsense,
                                   0 AS revenueAdExchange,
                                   SUM(IF(madads_rubicon_table.revenue IS NULL, 0, madads_rubicon_table.revenue)) AS revenue,
                                   SUM( IF(madads_rubicon_table.allocated_impressions IS NULL, 0, madads_rubicon_table.allocated_impressions) ) AS allocated

                               FROM madads_rubicon_table

                                   WHERE madads_rubicon_table.query_date >= '".$start."'
                                     AND madads_rubicon_table.query_date < '".$end."'

                               GROUP BY madads_rubicon_table.query_date, madads_rubicon_table.SiteID

                        ) AS res

                        JOIN sites ON sites.SiteID = res.SiteID
                        JOIN users_revshare ON (users_revshare.PubID = sites.PubID AND users_revshare.date <= res.Date)
                   LEFT JOIN sites_floor_price ON (sites_floor_price.SiteID=sites.SiteID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d'))

                        GROUP BY res.Date";

            } else {

                $sql = "SELECT

                            res.Date,

                                ROUND(SUM( res.revenueAdsense + res.revenueAdExchange ), 2) AS GrossAdExchange,
                                ROUND(SUM( IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue ) ), 2) AS GrossRubicon,
                                ROUND(SUM( res.revenueAdsense + res.revenueAdExchange + IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue ) ), 2) AS GrossAll,

                                ROUND(SUM( (res.revenueAdsense + res.revenueAdExchange) * (users_revshare.RevShare/100.0) ), 2) AS PaidAdExchange,
                                ROUND(SUM( IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue * (users_revshare.RevShare/100.0)) ), 2) AS PaidRubicon,
                                ROUND(SUM( (res.revenueAdsense + res.revenueAdExchange) * (users_revshare.RevShare/100.0) + IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue * (users_revshare.RevShare/100.0))  ), 2) AS PaidAll,

                                ROUND(SUM( (res.revenueAdsense + res.revenueAdExchange) - ((res.revenueAdsense + res.revenueAdExchange) * (users_revshare.RevShare/100.0)) ), 2) AS ProfitAdExchange,
                                ROUND(SUM( IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue ) - ( IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue * (users_revshare.RevShare/100.0) )  ) ), 2)	AS ProfitRubicon,
                                ROUND(SUM( (res.revenueAdsense + res.revenueAdExchange + IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue )) - ((res.revenueAdsense + res.revenueAdExchange) * (users_revshare.RevShare/100.0) + IF(sites.floor_pricing=1, (((((users_revshare.RevShare/100.0)*res.revenue)*1000/allocated)-IF(sites_floor_price.price IS NULL, 1, sites_floor_price.price))*IF(sites_floor_price.percent IS NULL, 0.001, sites_floor_price.percent)+IF(sites_floor_price.price IS NULL, 0, sites_floor_price.price))*(allocated/1000), res.revenue * (users_revshare.RevShare/100.0) ) ) ), 2) AS ProfitAll

                        FROM ( SELECT madads_dfp_table.SiteID AS SiteID,
                                      madads_dfp_table.query_date AS Date,

                                   SUM(IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue)) AS revenueAdsense,
                                   0 AS revenueAdExchange,
                                   0 AS revenue,
                                   0 AS allocated

                                   FROM madads_dfp_table

                                   WHERE madads_dfp_table.query_date >= '".$start."'
                                     AND madads_dfp_table.query_date < '".$end."'
                                     AND madads_dfp_table.order_name = 'Adsense'

                                   GROUP BY madads_dfp_table.query_date, madads_dfp_table.SiteID

                                 UNION ALL

                               SELECT madads_dfp_table.SiteID AS SiteID,
                                          madads_dfp_table.query_date AS Date,

                                   0 AS revenueAdsense,
                                   SUM(IF(madads_dfp_table.revenue IS NULL, 0, madads_dfp_table.revenue)) AS revenueAdExchange,
                                   0 AS revenue,
                                   0 AS allocated

                                   FROM madads_dfp_table

                                   WHERE madads_dfp_table.query_date >= '".$start."'
                                     AND madads_dfp_table.query_date < '".$end."'
                                     AND madads_dfp_table.order_name = 'MAM-Google-AdExchange'

                                   GROUP BY madads_dfp_table.query_date, madads_dfp_table.SiteID

                                 UNION ALL

                               SELECT madads_rubicon_table.SiteID AS SiteID,
                                          madads_rubicon_table.query_date  AS Date,

                                   0 AS revenueAdsense,
                                   0 AS revenueAdExchange,
                                   SUM(IF(madads_rubicon_table.revenue IS NULL, 0, madads_rubicon_table.revenue)) AS revenue,
                                   SUM( IF(madads_rubicon_table.allocated_impressions IS NULL, 0, madads_rubicon_table.allocated_impressions) ) AS allocated

                               FROM madads_rubicon_table

                                   WHERE madads_rubicon_table.query_date >= '".$start."'
                                     AND madads_rubicon_table.query_date < '".$end."'

                               GROUP BY madads_rubicon_table.query_date, madads_rubicon_table.SiteID

                        ) AS res

                        JOIN sites ON sites.SiteID = res.SiteID
                        JOIN users_revshare ON (users_revshare.PubID = sites.PubID AND users_revshare.date <= res.Date)
                   LEFT JOIN sites_floor_price ON (sites_floor_price.SiteID=sites.SiteID AND DATE_FORMAT(res.Date, '%Y-%m-%d')>=DATE_FORMAT(sites_floor_price.date, '%Y-%m-%d'))

                        GROUP BY res.Date";

            }

               return $this->getAdapter()->fetchAll($sql);


        }

        public function getNetWorkStatNew($year, $month/*, $rubicon_15*/)
        {
            $sql = null;
            $start = date('Y-m-d', strtotime($year.'-'.$month));
            $str = preg_split('/[-]/', $start);
            $end = date("Y-m-d", mktime(0, 0, 0, $str[1] + 1 /* month */, $str[2], $str[0]));

            /*if($rubicon_15){*/

              $sql = "SELECT

                            rf.query_date AS Date,
                            
                            ROUND(SUM(( IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv) / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossRubicon,  
                            ROUND(SUM(( rf.revenue_pubmatic / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossPubm,
                            ROUND(SUM( IF(rf.type=8, rf.revenue / IF(rf.RevShare,(rf.RevShare/100),1),0) ),2) AS GrossPubmEstim,
                            ROUND(SUM(( rf.revenue_amazon / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossAmazon,
                            ROUND(SUM(( rf.revenue_pulse / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossPulsePoint,
                            ROUND(SUM(( rf.revenue_pop / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossPop,
                            ROUND(SUM(( rf.revenue_sekindo / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossSekindo,                            
                            ROUND(SUM(( rf.revenue_aol / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossAol,
                            ROUND(SUM(( rf.revenue_aol_o / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossAol_o,
                            ROUND(SUM(( rf.revenue_brt / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossBrt,

                            ROUND(SUM(( rf.revenue_manual / IF(rf.RevShare,(rf.RevShare/100),1) )),2) AS GrossRevMan,
                              
                            ROUND(SUM(IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv)),2) AS PaidRubicon,
                            ROUND(SUM(rf.revenue_pubmatic),2) AS PaidPubm,
                            ROUND(SUM(IF(rf.type=8,rf.revenue,0)),2) AS PaidPubmEstim,  
                            ROUND(SUM(rf.revenue_manual),2) AS PaidRevMan,
                            ROUND(SUM(rf.revenue_amazon),2) AS PaidAmazon,
                            ROUND(SUM(rf.revenue_pulse),2) AS PaidPulsePoint,
                            ROUND(SUM(rf.revenue_pop),2) AS PaidPop,
                            ROUND(SUM(rf.revenue_sekindo),2) AS PaidSekindo,
                            ROUND(SUM(rf.revenue_aol),2) AS PaidAol,
                            ROUND(SUM(rf.revenue_aol_o),2) AS PaidAol_o,
                            ROUND(SUM(rf.revenue_brt),2) AS PaidBrt,
                                                    
                            SUM(rf.impressions) AS Impressions,                            
                            ROUND(SUM(IF(rf.estimated=1, rf.impressions_Rubicon, rf.impressions_RubiconCsv)),0) AS impressions_Rubicon,
                            SUM(rf.impressions_pubmatic) AS impressions_pubmatic,
                            SUM(rf.impressions_amazon) AS impressions_amazon,
                            SUM(rf.impressions_pulse) AS impressions_pulse,
                            SUM(rf.impressions_pop) AS impressions_pop,
                            SUM(rf.impressions_sekindo) AS impressions_sekindo,
                            SUM(rf.impressions_aol) AS impressions_aol,
                            SUM(rf.impressions_aol_o) AS impressions_aol_o,
                            SUM(rf.impressions_brt) AS impressions_brt,

                            SUM(rf.paid_impressions_RubiconCsv) AS paid_impressions_RubiconCsv,
                            SUM(rf.paid_impressions_pubmatic) AS paid_impressions_pubmatic,
                            SUM(rf.paid_impressions_amazon) AS paid_impressions_amazon,
                            SUM(rf.paid_impressions_pulse) AS paid_impressions_pulse,
                            SUM(rf.paid_impressions_pop) AS paid_impressions_pop,
                            SUM(rf.paid_impressions_sekindo) AS paid_impressions_sekindo,
                            SUM(rf.paid_impressions_aol) AS paid_impressions_aol,
                            SUM(rf.paid_impressions_aol_o) AS paid_impressions_aol_o,
                            SUM(rf.paid_impressions_brt) AS paid_impressions_brt,
                                                                                    
                            ROUND(SUM( (IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv) / IF(rf.RevShare,(rf.RevShare/100),1)) - IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv) ),2) AS ProfitRubicon,
                            ROUND(SUM( ( rf.revenue_pubmatic / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_pubmatic ),2) AS ProfitPubm,
                            ROUND(SUM( ( rf.revenue_manual / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_manual ),2) AS ProfitRevMan,
                            ROUND(SUM( IF(rf.type=8, ( rf.revenue / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue,0) ),2) AS ProfitPubmEstim,
                            ROUND(SUM( ( rf.revenue_amazon / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_amazon ),2) AS ProfitAmazon,
                            ROUND(SUM( ( rf.revenue_pulse / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_pulse ),2) AS ProfitPulsePoint,
                            ROUND(SUM( ( rf.revenue_pop / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_pop ),2) AS ProfitPop,
                            ROUND(SUM( ( rf.revenue_sekindo / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_sekindo ),2) AS ProfitSekindo,
                            ROUND(SUM( ( rf.revenue_aol / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_aol ),2) AS ProfitAol,
                            ROUND(SUM( ( rf.revenue_aol_o / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_aol_o ),2) AS ProfitAol_o,
                            ROUND(SUM( ( rf.revenue_brt / IF(rf.RevShare,(rf.RevShare/100),1)) - rf.revenue_brt ),2) AS ProfitBrt

                       FROM users_reports_final AS rf
                       JOIN users AS u ON u.id = rf.PubID

                       WHERE DATE_FORMAT(rf.query_date, '%Y-%c')='$year-$month' AND u.active = 1

                       GROUP BY rf.query_date
                       ORDER BY rf.query_date ASC";

            /*}else{

               $sql = "SELECT

                            rf.query_date AS Date,

                            ROUND(SUM((IFNULL(rf.revenue_Adsense, 0) / IF(rf.RevShare,(rf.RevShare/100),1) ) + (rf.revenue_AdExcchange / IF(rf.RevShare,(rf.RevShare/100),1)) ),2) AS GrossAdExchange,
                            ROUND(SUM((IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv) / IF(rf.RevShare,(rf.RevShare/100), 1)) * 0.85),2) AS GrossRubicon,
                            ROUND(SUM( ((IFNULL(rf.revenue_Adsense, 0) / IF(rf.RevShare,(rf.RevShare/100),1)) + (rf.revenue_AdExcchange / IF(rf.RevShare,(rf.RevShare/100),1)) + (IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv) / IF(rf.RevShare,(rf.RevShare/100),1))) * 0.85 ),2) AS GrossAll,

                            ROUND(SUM(IFNULL(rf.revenue_Adsense, 0) + rf.revenue_AdExcchange),2) AS PaidAdExchange,
                            ROUND(SUM(IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv)),2) AS PaidRubicon,
                            ROUND(SUM(IFNULL(rf.revenue_Adsense, 0) + rf.revenue_AdExcchange + IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv)),2) AS PaidAll,

                            ROUND(SUM( (( IFNULL(rf.revenue_Adsense, 0) / IF(rf.RevShare,(rf.RevShare/100),1)) + (rf.revenue_AdExcchange / IF(rf.RevShare,(rf.RevShare/100),1))) - (IFNULL(rf.revenue_Adsense, 0) + rf.revenue_AdExcchange) ),2) AS ProfitAdExchange,
                            ROUND(SUM( (( IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv) / IF(rf.RevShare,(rf.RevShare/100),1)) * 0.85 ) - IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv) ),2) AS ProfitRubicon,
                            ROUND(SUM( ((( IFNULL(rf.revenue_Adsense, 0) / IF(rf.RevShare,(rf.RevShare/100),1)) + (rf.revenue_AdExcchange / IF(rf.RevShare,(rf.RevShare/100),1)) + (IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv) / IF(rf.RevShare,(rf.RevShare/100),1))) * 0.85) - (IFNULL(rf.revenue_Adsense, 0) + rf.revenue_AdExcchange + IF(rf.estimated=1, rf.revenue_Rubicon, rf.revenue_RubiconCsv)) ),2) AS ProfitAll

                       FROM users_reports_final AS rf
                       JOIN users AS u ON u.id = rf.PubID

                       WHERE DATE_FORMAT(rf.query_date, '%Y-%c')='$year-$month' AND u.active = 1

                       GROUP BY rf.query_date
                       ORDER BY rf.query_date ASC";

            }*/

               return $this->getAdapter()->fetchAll($sql);
        }

        public function getUserSiteByName($name)
        {
        	$this->_name = 'sites';
        	$this->_primary = 'SiteID';

        	$select = $this->_db->select()
        	->from('sites', array(
        			'PubID'=>'sites.PubID',
        			'name'=>'users.name',
        	))
        	->joinLeft("users", "sites.PubID=users.id", array())
        	->where("sites.SiteName='$name'")
        	;

        	return $this->getAdapter()->fetchRow($select);
        }

        public function getUserSiteByNameExist($name, $PubID)
        {
        	$this->_name = 'sites';
        	$this->_primary = 'SiteID';

        	$select = $this->_db->select()
        	->from('sites', array(
        			'PubID'=>'sites.PubID',
        			'name'=>'users.name',
        	))
        	->joinLeft("users", "sites.PubID=users.id", array())
        	->where("sites.SiteName='$name'")
                ->where("sites.PubID!='$PubID'")
        	;

        	return $this->getAdapter()->fetchRow($select);
        }

        public function uncheckAdx($id)
        {
        	$this->_name = 'users';
        	$this->_primary = 'id';

        	$null = new Zend_Db_Expr("NULL");

        	$result = array(
        			'reg_AdExchage'=>$null,
        	);

        	$where = $this->getAdapter()->quoteInto('id  = ?', $id);
        	$this->update($result, $where);

        }

        public function checkAdx($id)
        {
        	$this->_name = 'users';
        	$this->_primary = 'id';

        	$null = new Zend_Db_Expr("NULL");

        	$result = array(
        			'reg_AdExchage'=>1,
        	);

        	$where = $this->getAdapter()->quoteInto('id  = ?', $id);
        	$this->update($result, $where);

        }

        public function getNumNewInviteReguest()
        {
            $sql = $this->_db->select()
                             ->from('users', array('COUNT(id) AS count'))
                             ->where('inviteRequest = 1');

            $data = $this->_db->query($sql)->fetchAll();

            return $data[0]['count'];
        }

    public function countPsaUsers($userID)
    {
            $select = $this->_db->select()
                                ->from('madads_psa', array('count'=>'COUNT(madads_psa.SiteID)'))
                                ->join("sites", "sites.SiteID=madads_psa.SiteID", array())
                                ->where("sites.PubID = $userID AND madads_psa.viewed IS NULL")
                                ->where("IF(madads_psa.iframe, madads_psa.src != '', madads_psa.url != '')")
                                ->group('IF(madads_psa.iframe, madads_psa.src, madads_psa.url)');

              $this->_db->query($select);
       return $this->_db->fetchOne('select FOUND_ROWS()');

    }
    
    public function emulateOsTicketAuth($email = false)
    {
    	if($email)
    	{
    		$select_staff = $this->_db->select()
    								  ->from(array('s' => 'ost_staff'))
    								  ->join(array('t' => 'ost_timezone'), 's.timezone_id = t.id',array('offset'))
    								  ->where('username = ?', $email)
    								  ->limit(1);
    		$str = $select_staff->__toString();
    		$current_staff = $this->_db->fetchRow($select_staff);
    		if($current_staff)
    		{
    			session_start();
    			global $_SESSION;
    			$data_to_db = array();
    			//TZ
    			$_SESSION['TZ_OFFSET'] = $current_staff['offset'];
    			$_SESSION['TZ_DST'] = '0.0';
    			//Creating CSRF_TOKEN
    			$len = $len>8?$len:32;
    			$r = '';
    			for ($i = 0; $i <= $len; $i++)
    				$r .= chr(mt_rand(0, 255));
    			$_SESSION['csrf']['token'] = base64_encode(sha1(session_id().$r.'80CB74EDD510877'));
    			//Time
    			$_SESSION['csrf']['time'] = time();
    			//Staff token
    			$time = time();
    			$hash  = md5($time.md5('80CB74EDD510877').$current_staff['username']);
    			$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
    			$token = "$hash:$time:".MD5($ip);    						 
    			//_Staff section
    			$_SESSION['_staff']['userID'] = $current_staff['username'];
    			$_SESSION['_staff']['token'] = $token;
    			//Writing session to db
    			$data_to_db = session_encode();
    			$data_to_db = array(
    				'session_id' => session_id(),
    				'session_data' => session_encode(),
    				'session_expire' => date("Y-m-d H:i:s", time()+86400),
    				'user_id' => $current_staff['staff_id'],
    				'user_ip' => $ip,
    				'user_agent' => $_SERVER['HTTP_USER_AGENT']
    			);    			
    			try 
    			{
    				$this->_db->insert('ost_session', $data_to_db);
    			}catch(Exception $e) 
    			{
    				$this->_db->update('ost_session', $data_to_db, array("session_id = '".session_id()."'"));
    			}
    		}
    	}
    }
    
    public function logoutOsTicketAuth($email = false)
    {
    	if($email)
    	{
    		$this->_db->delete('ost_session',array("session_id = '".session_id()."'"));
    	}
    }
    
    public function checkIfSiteNameExist($site_name)
    {
    	$result = false;
    	$parsed_name = parse_url($site_name);
    	$stripped_site_name = '';
    	if(isset($parsed_name['host']))
    		$stripped_site_name = $parsed_name['host'];
    	elseif(isset($parsed_name['path']))
    		$stripped_site_name = $parsed_name['path'];
    		
    	$stripped_site_name = str_replace("www.", "", $stripped_site_name);
    	
    	$stripped_site_name = preg_replace('#^(http(s)?://)?w{3}\.(\w+\.\w+)#', '$1$3', $stripped_site_name);
    	if(strlen($stripped_site_name))
    	{
    		$select = $this->_db->select()
					    		->from('sites', array('SiteName'))
					    		->where('SiteName = ?', $stripped_site_name);
    		$result = $this->_db->fetchAll($select);
    		if(count($result))
    		{
    			$domain_name_parts = explode('.',$stripped_site_name);
    			if(count($domain_name_parts) > 1)
    			{
    				$array_index = count($domain_name_parts) - 2;
    			}
    			else 
    				$array_index = 0;    			
    			$editable_string = $domain_name_parts[$array_index];
    			$ending = substr($editable_string, strlen($editable_string) - 2 ,2);
    			$iter = substr($ending, 1,strlen($ending) - 1);
    			$iter = intval($iter);
    			$new_iter = 1;
    			while(1)
    			{
    				$domain_name_parts[$array_index] = $editable_string.'-'.$new_iter;
    				$final_site_name = implode('.', $domain_name_parts);
    				$check_new_one_select = $this->_db->select()
    				->from('sites',array('SiteName'))
    				->where('SiteName = ?', $final_site_name);
    				if(count($this->_db->fetchAll($check_new_one_select)))
    				{
    					$new_iter++;
    					continue;
    				}
    				else 
    					break;
    			}
    			$result = $final_site_name;
    		}
    	}
    	return $result;	  
    }
    
    public function setTable($name = 'users')
    {
        $this->_name = $name;
    }

    public function getAllAdmin()
    {
        $this->_name = 'users';
        
        $sql = $this->_db->select()
                    ->from($this->_name, array('id', 'email', 'role'))
                    ->where('role IN("admin")');
        
        return $this->_db->query($sql)->fetchAll();
    }
}