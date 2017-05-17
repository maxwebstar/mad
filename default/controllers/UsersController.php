<?php
/**
 *
 * @author tim
 * @copyright 2011
 *
 * class Users
 */


class UsersController extends Zend_Controller_Action
{
    protected $_dataAuth;

    public function preDispatch()
    {
        $auth = Zend_Auth::getInstance()->getIdentity();
        $this->_dataAuth = $auth;
    }

    private function _summ_arrays(array $array1, array $array2, array $estimatesDates)
    {
        $newArray = array();
        if(count($array1)>count($array2)){
            foreach($array1 as $key=>$value){
                if(!in_array($value['Date'], $estimatesDates)){
                    $newArray[$key]['Date']=isset($array2[$key]['Date']) ? $array2[$key]['Date'] : $array1[$key]['Date'];
                    $newArray[$key]['Impressions']=isset($array2[$key]['Impressions']) ? $array2[$key]['Impressions']+$array1[$key]['Impressions'] : $array1[$key]['Impressions'];
                    $newArray[$key]['Revenue']=isset($array2[$key]['Revenue']) ? $array2[$key]['Revenue']+$array1[$key]['Revenue'] : $array1[$key]['Revenue'];
                    $newArray[$key]['RevShare']=isset($array2[$key]['RevShare']) ? $array2[$key]['RevShare'] : $array1[$key]['RevShare'];
                }
            }
        }else{
            foreach($array2 as $key=>$value){
                if(!in_array($value['Date'], $estimatesDates)){
                    $newArray[$key]['Date']=isset($array1[$key]['Date']) ? $array1[$key]['Date'] : $array2[$key]['Date'];
                    $newArray[$key]['Impressions']=isset($array1[$key]['Impressions']) ? $array2[$key]['Impressions']+$array1[$key]['Impressions'] : $array2[$key]['Impressions'];
                    $newArray[$key]['Revenue']=isset($array1[$key]['Revenue']) ? $array2[$key]['Revenue']+$array1[$key]['Revenue'] : $array2[$key]['Revenue'];
                    $newArray[$key]['RevShare']=isset($array1[$key]['RevShare']) ? $array1[$key]['RevShare'] : $array2[$key]['RevShare'];
                }
            }
        }

        return $newArray;
    }

    private function _summ_arraysNoEstimated(array $array1, array $array2)
    {
        $newArray = array();
        if(count($array1)>count($array2)){
            foreach($array1 as $key=>$value){
                $newArray[$key]['Date']=isset($array2[$key]['Date']) ? $array2[$key]['Date'] : $array1[$key]['Date'];
                $newArray[$key]['Impressions']=isset($array2[$key]['Impressions']) ? $array2[$key]['Impressions']+$array1[$key]['Impressions'] : $array1[$key]['Impressions'];
                $newArray[$key]['Revenue']=isset($array2[$key]['Revenue']) ? $array2[$key]['Revenue']+$array1[$key]['Revenue'] : $array1[$key]['Revenue'];
                $newArray[$key]['RevShare']=isset($array2[$key]['RevShare']) ? $array2[$key]['RevShare'] : $array1[$key]['RevShare'];
            }
        }else{
            foreach($array2 as $key=>$value){
                $newArray[$key]['Date']=isset($array1[$key]['Date']) ? $array1[$key]['Date'] : $array2[$key]['Date'];
                $newArray[$key]['Impressions']=isset($array1[$key]['Impressions']) ? $array2[$key]['Impressions']+$array1[$key]['Impressions'] : $array2[$key]['Impressions'];
                $newArray[$key]['Revenue']=isset($array1[$key]['Revenue']) ? $array2[$key]['Revenue']+$array1[$key]['Revenue'] : $array2[$key]['Revenue'];
                $newArray[$key]['RevShare']=isset($array1[$key]['RevShare']) ? $array1[$key]['RevShare'] : $array2[$key]['RevShare'];
            }
        }

        return $newArray;
    }

    public function indexAction()
    {
        $this->_redirect('/registration/');
    }

    /**
     *
     * @var void
     *
     * registration function
     */
    public function registrationAction()
    {

        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'apply';

        // creat form and send in view
        $form = new Application_Form_Registration();
        $this->view->form = $form;

        //create session
        $session = new Zend_Session_Namespace('Default');

        //if isset message view
        if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }

        $helpersFuncs = new My_Helpers();

        //load model
        $usersModel = new Application_Model_DbTable_Users();
        $tableContact = new Application_Model_DbTable_ContactNotification();

        $this->view->zones = $usersModel->querySelect('timezone');
        $this->view->country = $usersModel->querySelect('country');
        $this->view->state = $usersModel->querySelect('state');
        $this->view->server = $usersModel->querySelect('service', 'service');
        $this->view->category = $usersModel->querySelect('category');

        // if form submit
        if($this->getRequest()->isPost()){
            //get form data
            $formData = $this->getRequest()->getPost();
            if($formData['daily'])
                $formData['daily'] = str_replace(",", "", $formData['daily']);
            //validate form

            if($this->getRequest()->getPost('country')!=237){
                $form->removeElement('ssn');
            }

            if($this->getRequest()->getPost('type')==3){
                $form->getElement('company')->removeValidator('NotEmpty');
                $form->getElement('company')->setRequired(false);
                $form->removeElement('privacy');
                $form->removeElement('daily');
            }else{
                $form->removeElement('followers');
            }

			$formData['url'] = str_replace("http://", "", $formData['url']);
			$formData['url'] = str_replace("https://", "", $formData['url']);
			$formData['url'] = str_replace("www.", "", $formData['url']);
			$formData['url'] = ucfirst($formData['url']);
			$formData['url'] = parse_url('http://'.$formData['url'], PHP_URL_HOST);
			
            if($form->isValid($formData)){ 
                //generate salt
                $salt = $helpersFuncs->genereteSalt();
                $formData['salt'] = $salt;

                //Alexa rank
                //require_once '/home/madads/library/Alexa/alexa.class.php';
                require_once LIB_PATH.'/Alexa/alexa.class.php';
                $site = str_replace("http://", '', $this->getRequest()->getPost('url'));
                $site = str_replace("http://", '', $site);
                $AlexaRank = new AlexaRank($site);
                $rank = $AlexaRank->get('rank');
                $USrank = $AlexaRank->get('USrank');

                $rank = $rank!='"rank" does not exist.' ? $rank : 'NULL';
                $USrank = $USrank!='"USrank" does not exist.' ? $USrank : 'NULL';  

                if($USrank!='NULL'){
                    $tmpRankArr = explode("(", $USrank);
                    $USrank = $tmpRankArr[0];

                    if($tmpRankArr[1]!='')
                        $country = str_replace("(", "", str_replace(")", "", $tmpRankArr[1]));
                    else
                    	$country='NULL';
                }                            

                $users_waiting = null;
                if($USrank>=1 && $USrank<=200000 && ($country=='US' || $country=='UK' || $country=='CA'))
                	$users_waiting=0;
                elseif($USrank>=1 && $USrank<=800000 && ($country=='US' || $country=='UK' || $country=='GB' || $country=='CA' || $country=='AU' || $country=='DE' || $country=='NZ' || $country=='CH'))
                    $users_waiting=0;
               	elseif($rank>=1 && $rank<=800000)
               		$users_waiting=0;            
               	elseif((!$rank || $rank==0) && $formData['daily']>=100000)
               		$users_waiting=1;
               	else
               		$users_waiting=1;


                $formData['alexaRank'] = $rank;
                
                $formData['alexaRankUS'] = $USrank;
                $formData['alexa_country'] = $country=='NULL' ? null : $country;
                $formData['users_waiting'] = $users_waiting==1 ? 1 : null;

                $formData['alexaRank_update'] = date("Y-m-d H:i:s");
                $formData['account_manager_id'] = $tableContact->getRandomID();
                $formData['referral_id'] = isset($_COOKIE['referral_id']) ? (int) $_COOKIE['referral_id'] : NULL;

                //check referral
                $site = str_replace("https://", "", $site);
                $site = str_replace("www", "", $site);
                $recrutingTable = new Application_Model_DbTable_RecruitingEmails();
                $dataRecruting = $recrutingTable->fetchRow("website LIKE '%$site%'"); 
                if($dataRecruting->created_by){
                    $referalTable = new Application_Model_DbTable_Referral();
                    $dataReferral = $referalTable->fetchRow("email='$dataRecruting->created_by'");
                    if($dataReferral->id){
                        $formData['referral_id'] = $dataReferral->id;
                    }
                }

                //save new user
                $userID = $usersModel->save($formData);

                if($userID){
                    $session->message = '<h1 class="applTitle">Publisher Application Form</h1><br>Your application is now being reviewed. You should receive an email from us within 1-2 business days.';

                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    $headers .= "From: Publisher Support <support@madadsmedia.com>\r\n";
                    $to = $formData['email'];
                    $title = 'Your Application to Join MadAdsMedia.com ('.$formData['company'].')';
                    if(!empty($formData['alexaRank']) && $formData['alexaRank']<=800000 && $formData['alexaRank']!=0){
                        $message = 'Your application is now being reviewed. You should receive an email from us within 1-2 business days.<br /><br />';
                        $message.='Thank you for applying to MadAdsMedia.com.<br /><br />';
                        $message.='Regards,<br />MadAdsMedia.com Staff';
                    }else{
                        $message = "Thanks for your interest in becoming a publisher of MadAds Media.  Our automated systems have determined your site's traffic rank may be below the minimum requirement.  As a result, your application will require additional time.  Once we've determined you've met our minimum traffic requirements, your application will be processed.<br /><br />";
                        $message.="If you're currently receiving more than 10,000 daily page views, we recommend responding to this email with a screenshot of your analytics data.<br /><br />";
                        $message.='Thank you for applying to MadAdsMedia.com.<br /><br />';
                        $message.='Regards,<br />MadAdsMedia.com Staff';
                    }
                    //mail($to, $title, $message, $headers);

                    $privacy = $formData['privacy']==1 ? "Yes" : "No";

                    switch ($formData['type']){
                        case 1:
                            $type = 'Web Site';
                            break;
                        case 2:
                            $type = 'Application';
                            break;
                        case 3:
                            $type = 'Tumblr Account';
                            break;
                    }

                    //mail admin
                    $title = 'New MadAdsMedia.com Application!';
                    $message = '<strong>Email:</strong> '.$formData['email'].'<br />';
                    $message.='<strong>Company Name:</strong> '.$formData['company'].'<br />';
                    $message.='<strong>Contact Name:</strong> '.$formData['name'].'<br />';
                    $message.='<strong>Phone Number:</strong> '.$formData['phone'].'<br />';
                    $message.='<strong>Time Zone:</strong> '.$usersModel->getResult('timezone', $formData['zone']).'<br />';
                    $message.='<strong>Country:</strong> '.$usersModel->getResult('country', $formData['country']).'<br />';
                    $message.='<strong>SSN or EIN:</strong> '.$formData['ssn'].'<br />';
                    $message.='<strong>URL:</strong> '.$formData['url'].'<br />';
                    $message.='<strong>Title:</strong> '.$formData['title'].'<br />';
                    $message.='<strong>Description:</strong> '.$formData['description'].'<br />';
                    $message.='<strong>Keywords:</strong> '.$formData['keywords'].'<br />';
                    $message.='<strong>Category:</strong> '.$usersModel->getResult('category', $formData['category']).'<br />';
                    $message.='<strong>Privacy Policy:</strong> '.$privacy.'<br />';
                    $message.='<strong>Site Type:</strong> '.$type.'<br />';
                    $message.='<strong>Daily Visits:</strong> '.$formData['daily'].'<br /><br />';
                    $message.='<strong>Followers:</strong> '.$formData['followers'].'<br /><br />';
                    $message.='<a href="http://www.madadsmedia.com/administrator/index/view/id/'.$userID.'">View</a>';
                    //mail('support@madadsmedia.com', $title, $message, $headers);

                }else{
                    $session->message = 'Error please try again';
                }

                $this->_redirect('/registration/');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }
    }

    public function reportAction()
    {
    	header("Location: /report");
    	$cacheOk = true;

        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'report';

        if($_GET['new']!=1){
            $users = new Application_Model_DbTable_Users();
            $auth = Zend_Auth::getInstance()->getIdentity();
            $userData = $users->getUserById($auth->id);

            if (!defined('SITE_ROOT_DIR')) {
               define('SITE_ROOT_DIR',$_SERVER["DOCUMENT_ROOT"]);
            }
            require_once(implode(DIRECTORY_SEPARATOR,array(APPLICATION_PATH,'models','earningsreport','earningsreport.php')));
            $reportsModel = new Application_Model_EARNINGSREPORT();

            // Get params
            $params = $this->getRequest()->getParams();

    		// Integrate default params
            $params["disable_grouping"] = isset($params["disable_grouping"]) && $params["disable_grouping"] == 1 ? 1 : 0;

    		if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"])) {
    			    $params["site"] = null;
    		}

    		if (!isset($params["start_date"]) || !is_string($params["start_date"])) {
                $params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
    		}

    		$params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));

    		if (!isset($params["end_date"]) || !is_string($params["end_date"])) {
    		    $params["end_date"] = gmdate( 'm/d/Y',time());
    		}

    		$params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));

    		if (!isset($params["ad_size"]) || strlen($params["ad_size"]) < 1) {
    			$params["ad_size"] = null;
    		}

        		$reportsModel->setDebug($params["debug"]);
        		$reportsModel->setDisableGrouping($params["disable_grouping"]);
        		$reportsModel->setOrder($params["order"]);
        		$reportsModel->setSite($params["site"]);
        		$reportsModel->setStartDate(gmdate('Y-m-d',strtotime($params["start_date"])));
        		$reportsModel->setEndDate(gmdate('Y-m-d',strtotime($params["end_date"])));
        		$reportsModel->setPublisher($reportsModel->getPublisherByEmail( $userData['email']));
                $reportsModel->setAdSize($params['ad_size']);

                // Assign View data
                $this->view->userData =  $userData;
                $this->view->identity = $auth;
                $this->view->report_ad_sizes = $reportsModel->getAdSizes();
                $this->view->report_sites = $reportsModel->getSites();
                $this->view->report_params = $params;
                list($usec, $sec) = explode(" ", microtime());
                $tstart = ((float)$usec + (float)$sec);

                set_time_limit(600);

                /*
                $finalArr = array();
        	    $finalArr = $users->getFinalReport($auth->id, $params["site"], $params["start_date"], $params["end_date"], $params["ad_size"], $params["disable_grouping"]);

                $EstimatedArr = array();
        	    $EstimatedArr = $users->getEstimatedReport($auth->id, $params["site"], $params["start_date"], $params["end_date"], $params["ad_size"], $params["disable_grouping"]);

                //print_r($dfpArr);
                //print_r($RubiconArr);
                $resultArray = array();
                $resultArray = array_merge($EstimatedArr, $finalArr);
                //print_r($resultArray);
                //$this->view->report_rows = $resultArray;
                */

                //new Report
                $NewEstimatedArr = array();
                $NewEstimatedArr = $users->getNewEstimatedReport($auth->id, $params["site"], $params["start_date"], $params["end_date"], $params["ad_size"], $params["disable_grouping"]);

                $NewFinalRubiconArr = array();
                $NewFinalRubiconArr = $users->getNewFinalRubiconReport($auth->id, $params["site"], $params["start_date"], $params["end_date"], $params["ad_size"], $params["disable_grouping"]);
                $NewFinalAdsenseArr = array();
                $NewFinalAdsenseArr = $users->getNewFinalAdsensReport($auth->id, $params["site"], $params["start_date"], $params["end_date"], $params["ad_size"], $params["disable_grouping"]);

                $NewEstimatedRubiDates = array();
                $NewEstimatedRubiDates = $users->getEstimatedRubiDates();

                $NewEstimatedAdsDates = array();
                $NewEstimatedAdsDates = $users->getEstimatedAdsDates();

                $NewEstimatedDates = array();
                $NewEstimatedDates = array_diff_assoc($NewEstimatedAdsDates, $NewEstimatedRubiDates);

                $NewFinalArr = array();
                $NewFinalArr = $this->_summ_arrays($NewFinalAdsenseArr, $NewFinalRubiconArr, $NewEstimatedDates);

                //print_r($NewFinalAdsenseArr);
                $NewArrayReport = array_merge($NewEstimatedArr, $NewFinalArr);

                function date_compare($a, $b)
                {
                    $t1 = strtotime($b['Date']);
                    $t2 = strtotime($a['Date']);
                    return $t1 - $t2;
                }

                usort($NewArrayReport, 'date_compare');

                $this->view->report_rows = $NewArrayReport;

                $this->view->estimatedDates = $NewEstimatedDates;


                //Paid Impressions
                $this->view->floor_pricing = null;

                if(count($this->view->report_sites)>0){
                    foreach($this->view->report_sites as $site){
                        if(!$params["site"] && $site['floor_pricing']==1){
                            $this->view->floor_pricing = 1;
                        }elseif($params["site"]==$site['SiteID'] && $site['floor_pricing']==1){
                            $this->view->floor_pricing = 1;
                        }
                    }
                }

                if($this->view->floor_pricing==1){
                    $NewPaidImpressionArray = array();
                    $NewPaidImpressionArray = $users->getPaidImpressionReprt($auth->id, $params["site"], $params["start_date"], $params["end_date"], $params["ad_size"], $params["disable_grouping"]);
                    $this->view->paidReport = $this->_summ_arraysNoEstimated($NewPaidImpressionArray, $NewFinalAdsenseArr);
                    //$this->view->paidReport = $users->getPaidImpressionReprt($auth->id, $params["site"], $params["start_date"], $params["end_date"], $params["ad_size"], $params["disable_grouping"]);

                    $FlorPricingArray = array();
                    $FlorPricingArray = $users->getFlorPricing($auth->id, $params["site"]);
                    $this->view->FlorPricing = $FlorPricingArray;
                    print_r($FlorPricingArray);
                }

                if($auth->role=='admin'){
                    //print_r($NewEstimatedDates);
                    //print_r( array_merge( $RubiconEstimatedArray, $AdsenseEstimatedArray ) );
                    //echo "!!!!!!".$this->view->floor_pricing;
                }

                list($usec, $sec) = explode(" ", microtime());
                $this->view->report_time = ((float)$usec + (float)$sec) - $tstart;

        		$this->view->report_error = $reportsModel->getError();
        		$this->view->debug_messages = $reportsModel->getDebugMessages();

                //print_r($this->view->report_rows);
                $this->render('new-report');
        }else{
        	// setup framework bridge

            if (!defined('SITE_ROOT_DIR')) {
               define('SITE_ROOT_DIR',$_SERVER["DOCUMENT_ROOT"]);
            }

            // Get current user data

            $users = new Application_Model_DbTable_Users();
            $auth = Zend_Auth::getInstance()->getIdentity();


            $userData = $users->getUserById($auth->id);

            //print_r($auth);
            //print_r($userData);
            //die('Under construction!');

            $frontendOptions = array(
                'lifetime'=>null,
                'automatic_serialization'=>true
            );
            $backendOptions = array(
                'cache_dir'=>APPLICATION_PATH.'/cache/'
            );

            $cache = Zend_Cache::factory(
                'Core',
                'File',
                $frontendOptions,
                $backendOptions
            );

            $cache_id = 'Report_'. intval($auth->id);


            // Load earnings report model

            require_once(implode(DIRECTORY_SEPARATOR,array(APPLICATION_PATH,'models','earningsreport','earningsreport.php')));

            $reportsModel = new Application_Model_EARNINGSREPORT();

            // Get params

            $params = $this->getRequest()->getParams();

    		// Integrate default params

            $params["debug"] = isset($params["debug"]) && $params["debug"] == 1 ? 1 : 0;
            $params["disable_grouping"] = isset($params["disable_grouping"]) && $params["disable_grouping"] == 1 ? 1 : 0;

    		if (!isset($params["order"])) {
    	        $params["order"] = array("Date"=>-1);
    		}

    		if (!is_array($params["order"])) {
    			$params["order"] = null;
    		}

    		if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"])) {
    			    $params["site"] = null;
    		}

    		if (!isset($params["start_date"]) || !is_string($params["start_date"])) {
                $params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
    		}

    		$params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));

    		if (!isset($params["end_date"]) || !is_string($params["end_date"])) {
    		    $params["end_date"] = gmdate( 'm/d/Y',time());
    		}

    		$params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));

    		if (!isset($params["ad_size"]) || strlen($params["ad_size"]) < 1) {
    			$params["ad_size"] = null;
    		}

            if($cacheOk && !$_GET && $results = $cache->load($cache_id)){
                $this->view->userData =  $results['userData'];
                $this->view->identity = $auth;
                $this->view->report_ad_sizes = $results['report_ad_sizes'];
                $this->view->report_sites = $results['report_sites'];
                $this->view->report_params = $results['report_params'];
        		//$this->view->report_rows = $results['report_rows'];
        		$this->view->report_error = $results['report_error'];
        		$this->view->debug_messages = $results['debug_messages'];
            }else{
        		// Assign report parameters

        		$reportsModel->setDebug($params["debug"]);
        		$reportsModel->setDisableGrouping($params["disable_grouping"]);
        		$reportsModel->setOrder($params["order"]);
        		$reportsModel->setSite($params["site"]);
        		$reportsModel->setStartDate(gmdate('Y-m-d',strtotime($params["start_date"])));
        		$reportsModel->setEndDate(gmdate('Y-m-d',strtotime($params["end_date"])));
        		$reportsModel->setPublisher($reportsModel->getPublisherByEmail( $userData['email']));
                $reportsModel->setAdSize($params['ad_size']);

                // Assign View data
                $this->view->userData =  $userData;
                $this->view->identity = $auth;
                $this->view->report_ad_sizes = $reportsModel->getAdSizes();
                $this->view->report_sites = $reportsModel->getSites();
                $this->view->report_params = $params;
                list($usec, $sec) = explode(" ", microtime());
                $tstart = ((float)$usec + (float)$sec);

                set_time_limit(600);
        	    //$this->view->report_rows = $reportsModel->getReportResults();

                list($usec, $sec) = explode(" ", microtime());
                $this->view->report_time = ((float)$usec + (float)$sec) - $tstart;

        		$this->view->report_error = $reportsModel->getError();
        		$this->view->debug_messages = $reportsModel->getDebugMessages();
            }

            $this->render('report-mess');
        }


    }

    public function newreportAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
    	$layout->nav = 'report';

    	$auth = Zend_Auth::getInstance()->getIdentity();
    	$userModel = new Application_Model_DbTable_Users();
    	$sitesModel = new Application_Model_DbTable_Sites();
    	$params = $this->getRequest()->getParams();

    	if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"])) {
    		$params["site"] = null;
    	}

    	if (!isset($params["start_date"]) || !is_string($params["start_date"])) {
    		$params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
    	}

    	$params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));

    	if (!isset($params["end_date"]) || !is_string($params["end_date"])) {
    		$params["end_date"] = gmdate( 'm/d/Y',time());
    	}

    	$params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));

    	if (!isset($params["ad_size"]) || strlen($params["ad_size"]) < 1) {
    		$params["ad_size"] = null;
    	}

    	$this->view->report_params = $params;
    	$this->view->ad_sizes = $userModel->querySelect('display_size');
    	$this->view->sites = $sitesModel->getApprovedSitesByUserId($auth->id);

    	$finalUserReport = array();
    	$finalUserReport = $userModel->getNewUserReport($auth->id, $params["site"], $params["start_date"], $params["end_date"], $params["ad_size"]);

    	$this->view->report = $finalUserReport;
    	$this->view->absEcpm = null;
    	$count = 0;
    	foreach ($finalUserReport as $key=>$item){
    		$count++;
    		if($count==8){
    			break;
    		}else{
    			if($item['estimated']==1){
    				unset($finalUserReport[$key]);
    			}
    		}
    	}

    	$this->view->floor_pricing = null;

    	if(count($this->view->sites)>0){
    		foreach($this->view->sites as $site){
    			if(!$params["site"] && $site['floor_pricing']==1){
    				$this->view->floor_pricing = 1;
    			}elseif($params["site"]==$site['SiteID'] && $site['floor_pricing']==1){
    				$this->view->floor_pricing = 1;
    			}
    		}
    	}


    	$ecpm = 0;

    	if(count($finalUserReport)>3){
    		$count = 0;

    		foreach ($finalUserReport as $item){
    			$count++;
    			if($count==4){
    				break;
    			}else{
					if($this->view->floor_pricing==1)
						$ecpm = $ecpm + ($item['revenue']*1000/$item['allocated_impressions']);
					else
						$ecpm = $ecpm + ($item['revenue']*1000/$item['impressions']);
    			}
    		}
    	}

    	if($ecpm>0){
    		$this->view->absEcpm = round($ecpm/3, 2);
    	}else{
			$sitePricing = $userModel->getUsersFlorPricing($auth->id, $params["site"], $params["start_date"], $params["end_date"]);
			if(count($sitePricing)>0){
				foreach($sitePricing as $item){
					$this->view->absEcpm +=$item['price'];
				}
				//$this->view->absEcpm = round(array_sum($sitePricing), 2);
				//echo "!!!!!!".$this->view->absEcpm;
			}
			//print_r($sitePricing);
		}

		$PayEcpm = $userModel->getSitesPayEcpm($auth->id, $params["site"]);
		if($PayEcpm){
			$this->view->absEcpm = $PayEcpm['ecpm'];
			$this->view->removeAsterics = 1;
		}


		//print_r($PayEcpm);
    }

    public function testreportAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
    	$layout->nav = 'report';

    	$auth = Zend_Auth::getInstance()->getIdentity();
    	$userModel = new Application_Model_DbTable_Users();
    	$sitesModel = new Application_Model_DbTable_Sites();
    	$params = $this->getRequest()->getParams();

    	$this->view->ad_sizes = $userModel->querySelect('display_size');
    	$this->view->sites = $sitesModel->getApprovedSitesByUserId($auth->id);
    	$this->view->floor_pricing = null;
    	$this->view->absEcpm = null;
        $this->view->auth = $auth;

    	$sitesArray = array(0);

    	if (!isset($params["site"]) || strlen($params["site"]) < 1 || !is_numeric($params["site"])) {
    		if($this->view->sites){
    			foreach($this->view->sites as $site){
    				$sitesArray[] = $site['SiteID'];
    				if($site['floor_pricing']==1)
    					$this->view->floor_pricing = 1;
    			}

    		}
                $allSites = true;
    	}else{
    		if($this->view->sites){
    			foreach($this->view->sites as $site){
    				if($params["site"]==$site['SiteID']){
    					$sitesArray[] = $site['SiteID'];
    					if($site['floor_pricing']==1)
    						$this->view->floor_pricing = 1;
    					break;
    				}
    			}

    		}
                $allSites = false;
    	}

    	if (!isset($params["start_date"]) || !is_string($params["start_date"])) {
    		$params["start_date"] = gmdate( 'm/d/Y',time()- (30 * 86400));
    	}

    	$params["start_date"] = gmdate('m/d/Y',strtotime($params["start_date"]));

    	if (!isset($params["end_date"]) || !is_string($params["end_date"])) {
    		$params["end_date"] = gmdate( 'm/d/Y',time());
    	}

    	$params["end_date"] = gmdate('m/d/Y',strtotime($params["end_date"]));

    	if (!isset($params["ad_size"]) || strlen($params["ad_size"]) < 1) {
    		$params["ad_size"] = null;
    	}

    	$this->view->report_params = $params;

    	$finalUserReport = array();
    	$finalUserReport = $userModel->getNewUserReportVersion_2($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"], $allSites);
    	$this->view->report = $finalUserReport;

    	//remove estimated data
    	$count = 0;
    	foreach ($finalUserReport as $key=>$item){
    		$count++;
    		if($count==8){
    			break;
    		}else{
    			if($item['estimated']==1){
    				unset($finalUserReport[$key]);
    			}
    		}
    	}
    	$this->view->previosAllocted = null;
    	$this->view->previosImpressoin = null;
    	// abs ecpm
    	$ecpm = 0;
    	if(count($finalUserReport)>3){
    		$count = 0;

    		foreach ($finalUserReport as $item){
    			if($count==0 && $item['paid_impressions'] && $item['impressions']){
    				$this->view->previosAllocted = $item['paid_impressions'];
    				$this->view->previosImpressoin = $item['impressions'];
    			}
    			$count++;
    			if($count==4){
    				break;
    			}else{
    				if($item['revenue']){
	    				if($this->view->floor_pricing==1 && $item['paid_impressions'])
	    					$ecpm = $ecpm + ($item['revenue']*1000/$item['paid_impressions']);
	    				elseif($item['impressions'])
	    					$ecpm = $ecpm + ($item['revenue']*1000/$item['paid_impressions']);
    				}
    			}
                }
    	}

    	if($ecpm>0) $this->view->absEcpm = round($ecpm/3, 2);

    	//$PayEcpm = $userModel->getSitesPayEcpm($auth->id, $params["site"]);
    	//print_r($PayEcpm);

        /* create csv file for user */

        if(count($sitesArray)>1){

        $file = md5($auth->id.date('Y-m-d')).'.csv';

        if(is_file('csv/my-report/'.$file))  unlink('csv/my-report/'.$file);

        unset($sitesArray[0]);

        $dataCsv = $sitesModel->getCsvDataByUser($sitesArray, $params["start_date"], $params["end_date"], $params["ad_size"]);

        $fileOpen = fopen('csv/my-report/'.$file, 'w');

            if($this->view->floor_pricing != 1)
                fputcsv($fileOpen, array('Date', 'Site Name', 'Impressions', 'CPM', 'Revenue'));
            else
                fputcsv($fileOpen, array('Date', 'Site Name', 'Impressions', 'Paid Impressions','CPM', 'Revenue'));

                $cpm = 0;
                $asterics = '';
                $countEstim = 0;

            foreach($dataCsv as $iter){

                if($this->view->floor_pricing != 1){

                            if($iter['impressions']){

                                    if($iter['revenue'] != 0) $cpm = $iter['revenue'] * 1000 / $iter['impressions'];

                            } else { $cpm = 0; }

                            if($iter['estimated'] == 1 && $countEstim != -1){

                                    $countEstim ++ ;
                                    $asterics = '*';
                                    if($this->view->absEcpm > 0 && $iter['impressions'] > 0 && $iter['revenue'] == 0){

                                            $cpm = $this->view->absEcpm;
                                            $iter['revenue'] = $this->view->absEcpm * $iter['impressions'] / 1000;

                                    }

                            } else { $asterics = ''; $countEstim = -1; }

                            if($countEstim == -1 && number_format($iter['revenue'],2) == 0){

					$iter['revenue'] = 0;
					$cpm = 0;

                            }

                } else {

                            if($iter['paid_impressions']){

                                    if($iter['revenue'] != 0) $cpm = $iter['revenue'] * 1000 / $iter['paid_impressions'];

                            } else { $cpm = 0; }

                            if($iter['estimated'] == 1){

                                    if($this->view->absEcpm > 0 && $iter['paid_impressions'] > 0 && $iter['revenue'] == 0){

                                                $asterics = '*';
                                                $cpm = $this->view->absEcpm;
                                                $iter['revenue'] = $this->view->absEcpm * $iter['paid_impressions'] / 1000;
                                    }

                            } else { $asterics = ''; }

                }

                   $cpm = number_format($cpm, 2, '.', '');
                   $iter['revenue'] = number_format($iter['revenue'], 2, '.', '');

                   if($this->view->floor_pricing != 1)
                        fputcsv($fileOpen, array($iter['Date'].$asterics, $iter['site_name'], $iter['impressions'].$asterics, $cpm.$asterics, $iter['revenue'].$asterics));
                   else
                        fputcsv($fileOpen, array($iter['Date'].$asterics, $iter['site_name'], $iter['impressions'].$asterics, $iter['paid_impressions'].$asterics, $cpm.$asterics, $iter['revenue'].$asterics));

            }

        fclose($fileOpen);


        $this->view->fileCsv = '/csv/my-report/'.$file;

        }
        /*
        if($auth->role=='admin')
        	$this->render('testreport');
        else
        	$this->render('message');
        */

        if($auth->role=='admin' && $params['show_demand']==1)
            $this->render('testreport-tab');
    	else
            $this->render('testreport');

    }

    public function zonesJob_unlink()
    {
    	$auth = Zend_Auth::getInstance()->getIdentity();
    	$zonesModel =& Application_Model_DbTable_Zones::getInstance();

    	if ($zonesModel->doUnlink($auth->id,$_POST['display_zone'])) {
    		return "Zone unlink complete!";
    	}
    	throw new Exception("Unlink failed!");
    }

    public function zonesJob_link()
    {
    	$auth = Zend_Auth::getInstance()->getIdentity();
    	$zonesModel =& Application_Model_DbTable_Zones::getInstance();
    	if ($zonesModel->doLink($auth->id,$_POST['target'],$_POST['display_zone'])) {
    		return "Zones linked!";
    	}
    	throw new Exception("Link failed!");
    }

    public function zonesAction()
    {

    	require_once(APPLICATION_PATH.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'DbTable'.DIRECTORY_SEPARATOR.'Zones.php');

    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
    	$layout->nav = 'zones';

    	// Get current user data

    	$zonesModel =& Application_Model_DbTable_Zones::getInstance();
    	$users = new Application_Model_DbTable_Users();
    	$auth = Zend_Auth::getInstance()->getIdentity();
    	$userData = $users->getUserById($auth->id);

    	if ($auth->role != "admin" && $auth->role!='super') {
    		throw new Exception("Access denied!");
    	}

    	// Process Job

    	$jobResult = null;
    	$jobError = null;
    	$jobId = null;

    	try {
    	    if (isset($_POST['job'])) {
    		    switch($_POST['job']) {
    			    case "unlink":
    			    	$jobId = "unlink";
    				    $jobResult = $this->zonesJob_unlink();
    				    break;
    			    case "link":
    			    	$jobId = "link";
    				    $jobResult = $this->zonesJob_link();
    				    break;
    			    default:
    				    $jobError = "Requested processing job not defined!";
    		    }
    	    }
    	} catch (Exception $ex) {
    		$jobError = $ex->getMessage();
    	}

    	$userZones = $zonesModel->getUserZones($auth->id);

    	// Get params

    	$params = $this->getRequest()->getParams();

    	// Integrate default params

    	// Assign report parameters

    	// Assign View data

    	$this->view->userData =  $userData;

    	$this->view->userZones = $userZones;

    	$this->view->identity = $auth;

    	$this->view->job = array("result"=>$jobResult,"error"=>$jobError);

    }

    public function forgotAction()
    {
        $session = new Zend_Session_Namespace('Default');
        $user = new Application_Model_DbTable_Users();
        $helpersFuncs = new My_Helpers();

        if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }

        if($this->_getParam('code')){
            $userData = $user->getUserByPassConfirm($this->_getParam('code'));

            if(!$userData){
                $this->view->message = 'Not Found';
            }else{
                $this->view->messageTitle = "Change Password";
            }

            $form = new Application_Form_NewPassword();
            $this->view->nameForm = 'password';

            if($this->getRequest()->isPost()){
                $formData = $this->getRequest()->getPost();
                if($form->isValid($formData)){
                    $salt = $helpersFuncs->genereteSalt();
                    $password = md5(md5($formData['password']).md5($salt));
                    $user->changePassword($userData['id'], $password, $salt);
                    $session->message = 'Your password has been changed.<br /><br /> You may now sign in using your new password.';
                    $this->_redirect('/forgot/');
                }else{
                    $this->view->formErrors = $form->getMessages();
                    $this->view->formValues = $form->getValues();
                }
            }

        }else{

            $form = new Application_Form_Forgot();
            $this->view->nameForm = 'email';

            if($this->getRequest()->isPost()){
                $formData = $this->getRequest()->getPost();
                $formData['email'] = $this->_helper->Xss->xss_clean($formData['email']);
                if($form->isValid($formData)){

                    $confirm = md5($helpersFuncs->genereteSalt());

                    $user->generareNewPassConfirm($formData['email'], $confirm);

                    $link = "http://".$_SERVER['HTTP_HOST']."/forgot/".$confirm;

                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    $headers .= "From: Publisher Support <support@madadsmedia.com>\r\n";
                    $to = $formData['email'];
                    $title = 'Reset Your MadAdsMedia.com Password';
                    $message = 'Dear Publisher,<br /><br />';
                    $message .= 'Reset your password by following the steps below:<br /><br />';
                    $message .= '1. Click this link or copy and paste it into your web browser: '.$link.'<br /><br />';
                    $message .= '2. Follow the on-screen instructions to reset your password. <br /><br />';
                    $message .= 'Regards,<br />MadAdsMedia.com Staff';
                    mail($to, $title, $message, $headers);

                    $session->message = 'You should receive an email containing a password reset link shortly.';
                    $this->_redirect('/forgot/');
                }else{
                	$this->view->message = 'You should receive an email containing a password reset link shortly';
                    $this->view->formErrors = $form->getMessages();
                    $this->view->formValues = $form->getValues();
                }
            }
        }
    }

    public function contactAction()
    {

        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'contact';

        $users = new Application_Model_DbTable_Users();
        $tableNewContact = new Application_Model_DbTable_User_NewContact();

        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $userData = $users->getUserById($auth->id);

        $dataNewContact = $tableNewContact->getData($auth->id);
        $dataNewContact->PubID = $auth->id;

        $this->view->userData =  $userData;

        
        $form = new Application_Form_Contact($auth->id);

        if($this->getRequest()->isPost()){

            if($userData['email']==$this->getRequest()->getPost('email')){
                $form->getElement('email')->removeValidator('Db_NoRecordExists');
            }

            $formData = $this->getRequest()->getPost();

            if($form->isValid($formData) AND $this->_helper->Csrf->check_token($formData['csrf'])){				
				$dirty_params = $form->getValues();
				$formData = array();
				foreach($dirty_params as $key => $value)
				{
					$formData[$key] = $this->_helper->Xss->xss_clean($value);
				}

                    $users->saveContacts($auth->id, $formData);

                    if(!$dataNewContact->checkIdentity($userData, $formData)){

                        $dataNewContact->appendData($formData);
                        $dataNewContact->created = date('Y-m-d');
                        $dataNewContact->save();

                    }

                    $this->_redirect('/report/');
            }else{
            		$this->view->csrf = $this->_helper->Csrf->set_token();
                    $this->view->formErrors = $form->getMessages();
                    $dirty_values = $form->getValues();
                    foreach($dirty_values as $key => $value)
                    {
                    	$this->view->formValues[$key] =  $this->_helper->Xss->xss_clean($value);
                    }
                    //$this->view->formValues = $form->getValues();
            }
        }else{
        	$this->view->csrf = $this->_helper->Csrf->set_token();
            $this->view->formValues = $userData;
        }

        $this->view->wait = $dataNewContact;
    }

    public function passwordAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'password';

        $users = new Application_Model_DbTable_Users();
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $form = new Application_Form_NewPassword();
        $helpersFuncs = new My_Helpers();

        $userData = $users->getUserById($auth->id);

        $this->view->userData =  $userData;

        if($this->getRequest()->isPost()){
            $formData = $this->getRequest()->getPost();

            $passwordUserEnter = md5(md5($formData['oldPass']).md5($userData['salt']));
            if($passwordUserEnter===$userData['password']){
                if($form->isValid($formData)){
                	if($formData['password'] != $userData['email'])
                	{
                		if(strlen($formData['password']) >= 8)
                		{
                			if(!preg_match('/^(\w)\1{2,}/',$formData['password']))
                			{
	                			$salt = $helpersFuncs->genereteSalt();
	                			$password = md5(md5($formData['password']).md5($salt));
	                			$users->changePassword($auth->id, $password, $salt);
	                			$this->_redirect('/report/');
                			}
                			else 
                			{
                				$this->view->formErrors = array('password'=>array('Characters of at least two types'));
                			}
                		}
                		else
                		{
                			$this->view->formErrors = array('password'=>array('Minimun length is 8 characters'));
                		}
                	}
                	else 
                	{
                		$this->view->formErrors = array('password'=>array('A password can not be identical to a username.'));
                	}
                }else{
                    $this->view->formErrors = $form->getMessages();
                }
            }else{
                $this->view->formErrors = array('oldPass'=>array('Old password incorrect.'));
            }
        }
    }
    
    public function paymentAction()
    {
        $users = new Application_Model_DbTable_Users();
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $userData = $users->getUserById($auth->id);
        $form = new Application_Form_Payment();

        $paymentBy = $form->getElement('paymentBy');
        if($userData['enable_wire_transfer'])
        	$paymentBy->addMultiOptions(array(1=>'Payment by Check', 2=>'Payment by PayPal', 3=>'Payment by Direct Deposit', 4=>'Payment by Wire Transfer'));
        else
        	$paymentBy->addMultiOptions(array(1=>'Payment by Check', 2=>'Payment by PayPal', 3=>'Payment by Direct Deposit'));
        	
        $this->view->userData =  $userData;

        $this->view->state = $users->querySelect('state');
        $this->view->country = $users->querySelect('country');
        if($userData->enable_wire_transfer != 0 AND $userData->paymentBy == 4)
        	$this->view->payment = $users->querySelectPaymentAmount(true);
        else
        	$this->view->payment = $users->querySelectPaymentAmount();
        
        $day = intval(date('d',time()));
        switch($day)
        {
        	case 11:
        	case 12:
        	case 13:
        	case 14:
        	case 15:
        	case 16:
        	case 17:
        		$this->view->can_change_info = false;
        		break;
        	default:
        		$this->view->can_change_info = true;
        		break;
        }
        $this->view->auth = $auth;

        $pending_model = new Application_Model_DbTable_User_NewPaymentInfo();
        $pending_data = $pending_model->getData($auth->id);
        if($pending_data['new'])
        {
         	$this->view->pending_data = $pending_data;
        }
        $this->view->changes = $pending_model->getHistory($auth->id);
        if($this->getRequest()->isPost()){
        	$token = new Zend_Session_Namespace('token');
        	$stored_token = $token->hash;
            $formData = $this->getRequest()->getPost();

            if($this->getRequest()->getPost('paymentBy')!=4){

                $form->removeElement('bankName2');
                $form->removeElement('bankAdress');
                $form->removeElement('accName2');
                $form->removeElement('accNumber2');
                $form->removeElement('accNumber2Real');
                $form->removeElement('swift');
                $form->removeElement('iban');

                if($this->getRequest()->getPost('paymentBy')!=2){
                    $form->removeElement('paypalmail');

                }

                if($this->getRequest()->getPost('paymentBy')!=3){
                    $form->removeElement('bank');
                    $form->removeElement('accName');
                    $form->removeElement('accNameReal');
                    $form->removeElement('bankName');
                    $form->removeElement('accType');
                    $form->removeElement('accNumber');
                    $form->removeElement('accNumberReal');
                    $form->removeElement('confirmAccNumber');
                    $form->removeElement('confirmAccNumberReal');
                    $form->removeElement('routNumber');
                    $form->removeElement('routNumberReal');
                    $form->removeElement('confirmRoutNumber');
                    $form->removeElement('confirmRoutNumberReal');

                }

            } else { /* if payment by = wire transfer */

                $form->removeElement('paypalmail');
                $form->removeElement('bank');
                $form->removeElement('bankName');
                $form->removeElement('accType');
                $form->removeElement('accName');
                $form->removeElement('accNameReal');
                $form->removeElement('accNumber');
                $form->removeElement('accNumberReal');
                $form->removeElement('confirmAccNumber');
                $form->removeElement('confirmAccNumberReal');
                $form->removeElement('routNumber');
                $form->removeElement('routNumberReal');
                $form->removeElement('confirmRoutNumber');
                $form->removeElement('confirmRoutNumberReal');

            }

            if($this->getRequest()->getPost('street1')){
            	$form->getElement('street2')->removeValidator('NotEmpty');
            	$form->getElement('street2')->setRequired(false);
            }elseif($this->getRequest()->getPost('street2')){
            	$form->getElement('street1')->removeValidator('NotEmpty');
            	$form->getElement('street1')->setRequired(false);
            }

			if($this->getRequest()->getPost('country')!=237){
            	$form->getElement('state')->removeValidator('NotEmpty');
            	$form->getElement('state')->setRequired(false);				
			}
				

            if($form->isValid($formData) AND $this->_helper->Csrf->check_token($formData['csrf'])){           	
            	$dirty_params = $form->getValues();
            	$formData = array();
            	foreach($dirty_params as $key => $value)
            	{
            		$formData[$key] = $this->_helper->Xss->xss_clean($value);
            	}          	            	
                if($userData["paymentAmout"] != $formData["paymentAmout"]) $formData['paymentAmout_update'] = date('Y-m-d');
                $users->setPaymentInfoPending($auth->id, $formData);
                $this->_redirect('/payment/');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $formData;
                $this->view->formValues['accName'] = $this->view->formValues['accNameReal'];
                $this->view->formValues['accNumber'] = $this->view->formValues['accNumberReal'];
                $this->view->formValues['accNumber2'] = $this->view->formValues['accNumber2Real'];
                $this->view->formValues['confirmAccNumber'] = $this->view->formValues['confirmAccNumberReal'];
                $this->view->formValues['routNumber'] = $this->view->formValues['routNumberReal'];
                $this->view->formValues['confirmRoutNumber'] = $this->view->formValues['confirmRoutNumberReal'];
                $this->view->csrf = $this->_helper->Csrf->set_token();
            }
        }else{
            $this->view->csrf = $this->_helper->Csrf->set_token();
            //$userData['accName'] = $this->_helper->Xss->leakage_protect($userData['accName']);
            //$userData['accNumber'] = $this->_helper->Xss->leakage_protect($userData['accNumber']);
            //$userData['confirmAccNumber'] = $this->_helper->Xss->leakage_protect($userData['confirmAccNumber']);
            //$userData['routNumber'] = $this->_helper->Xss->leakage_protect($userData['routNumber']);
            //$userData['confirmRoutNumber'] = $this->_helper->Xss->leakage_protect($userData['confirmRoutNumber']);
            $this->view->formValues = $userData;
            if(!$userData['paymentAmout']){
                $this->view->formValues['paymentAmout'] = 2;
            }
            if(!$userData['payType']){
                $this->view->formValues['payType'] = 1;
            }

        }

        $tablePdf = new Application_Model_DbTable_Pdf_Entity();

        $sql = $tablePdf->select()->where('PubID = ?', $auth->id);

        $this->view->pdf = $tablePdf->fetchRow($sql);

        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'payment';
        $this->render('payment-new');
    }

    public function adcodeAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'adcode';

        $id = (int)$this->_getParam('site');

        $auth = Zend_Auth::getInstance()->getIdentity();
        $sitesModel = new Application_Model_DbTable_Sites();
        $dataSites = $sitesModel->getConfirmSitesPublisher($auth->id);
        $this->view->sites = $dataSites;

        if(count($dataSites)==1){
            $id = $dataSites[0]['SiteID'];
        }

        if($id){
            $sitesTagsModel = new Application_Model_DbTable_SitesTags();
            $tagsPropModel = new Application_Model_DbTable_TagsProperties();
            $tagsModel = new Application_Model_DbTable_Tags();
            $sliderModel = new Application_Model_DbTable_SliderRequest();

            $dataTags = $sitesTagsModel->getSiteTags($id, -1);
            $dataTagsProp = $tagsPropModel->getSiteTagsProp($id);
            if($dataTags){
                $networksSizesModel = new Application_Model_DbTable_NetworksSizes();
                $sizesRequestModel = new Application_Model_DbTable_SizesRequest();

                $networksSite = $sitesTagsModel->getSiteNetworks($id);

                $primaryNetwork = null;
                $additNetworks = [];

                if(count($networksSite)>0){
                    foreach($networksSite as $network){
                        if($network['primary']==1)
                            $primaryNetwork = $network['network_id'];
                        else
                            $additNetworks[] = $network['network_id'];
                    }
                }

                $this->view->network = $primaryNetwork;

                $dataTagsForRequest = $networksSizesModel->getSizesForRequest($primaryNetwork, $additNetworks);

                $dataRequestFinal = [];
                if(count($dataTagsForRequest)>0){
                    foreach($dataTagsForRequest as $item){
                        if(!isset($dataRequestFinal[$item['size_id']])){
                            $dataRequestFinal[$item['size_id']] = $item;
                        }else{
                            if($dataRequestFinal[$item['size_id']]['network_id']!=$primaryNetwork && $item['network_id']==$primaryNetwork){
                                $dataRequestFinal[$item['size_id']] = $item;
                            }
                        }
                    }
                }

                $this->view->tagsDataForRequest = $dataRequestFinal;

                $dataSiteRequest = $sizesRequestModel->getSizeRequest($id);
                $this->view->sizesRequested = $dataSiteRequest;
            }

            $siteData = $sitesModel->getSiteByID($id);
            $this->view->tagsData = $dataTags;
            $this->view->tagsPropData = $dataTagsProp;
            $this->view->siteData = $siteData;

            $this->view->wanted_iframe_for_site = $tagsModel->getWantedIframe($id);

            $this->view->sliderRequest = $sliderModel->getRequest($id);
        }
    }
    

    public function makeAction()
    {
        set_time_limit(0);
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
    
        $users = new Application_Model_DbTable_Users();
        $this->view->users = $users->getApproveUsersWithSites();
    }

    public function setcacheAction()
    {
        set_time_limit(0);
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data = array();
        $id = $this->getRequest()->getPost('id');
        if($id){

            // Get current user data
            $users = new Application_Model_DbTable_Users();
            $userData = $users->getUserById($id);

        }

        $json = Zend_Json::encode($data);
        echo $json;
    }

    public function tumblradsAction()
    {

        $layout = Zend_Layout::getMvcInstance();
        //$layout->nav = 'apply';

        // creat form and send in view
        $form = new Application_Form_Tumblrads();
        $this->view->form = $form;

        //create session
        $session = new Zend_Session_Namespace('Default');

        //if isset message view
        if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }

        $helpersFuncs = new My_Helpers();

        //load model
        $usersModel = new Application_Model_DbTable_Users();

        $this->view->headTitle('MadAdsMedia.com - Make Money From Your Tumblr Account By Display Advertisments');
        $this->view->headMeta('Turn your Tumblr from a hobby to a career.  Display ads on your Tumblr account and make money!  Many top Tubmlr accounts are making $100\'s of dollars a day.', 'description');
        $this->view->headMeta('tumblr ads, make money from tumblr, ad networks for tumblr, make money from tumblr, adsense for tumblr', 'keywords');

        $this->view->zones = $usersModel->querySelect('timezone');
        $this->view->country = $usersModel->querySelect('country');
        $this->view->state = $usersModel->querySelect('state');
        $this->view->server = $usersModel->querySelect('service', 'service');
        $this->view->category = $usersModel->querySelect('category');

        // if form submit
        if($this->getRequest()->isPost()){
            //get form data
            $formData = $this->getRequest()->getPost();
            if($formData['daily'])
                $formData['daily'] = str_replace(",", "", $formData['daily']);
            //validate form

            if($this->getRequest()->getPost('country')!=237){
                $form->removeElement('ssn');
            }

            if($form->isValid($formData)){
                //generate salt
                $salt = $helpersFuncs->genereteSalt();
                $formData['salt'] = $salt;
                $formData['tumblrAds'] = 1;
                //save new user
                $userID = $usersModel->save($formData);

                if($userID){
                    $session->message = '<h1 class="applTitle">Publisher Application Form</h1><br>Your application is now being reviewed. You should receive an email from us within 1-2 business days.';

                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    $headers .= "From: Publisher Support <support@madadsmedia.com>\r\n";
                    $to = $formData['email'];
                    $title = 'Your Application to Join MadAdsMedia.com';
                    $message = 'Your application is now being reviewed. You should receive an email from us within 1-2 business days.<br /><br />';
                    $message.='Thank you for applying to MadAdsMedia.com.<br /><br />';
                    $message.='Regards,<br />MadAdsMedia.com Staff';
                    mail($to, $title, $message, $headers);

                    $privacy = $formData['privacy']==1 ? "Yes" : "No";

                    switch ($formData['type']){
                        case 1:
                            $type = 'Web Site';
                            break;
                        case 2:
                            $type = 'Application';
                            break;
                        case 3:
                            $type = 'Tumblr Account';
                            break;
                    }

                    //mail admin
                    $title = 'New MadAdsMedia.com Application!';
                    $message = '<strong>Email:</strong> '.$formData['email'].'<br />';
                    $message.='<strong>Company Name:</strong> '.$formData['company'].'<br />';
                    $message.='<strong>Contact Name:</strong> '.$formData['name'].'<br />';
                    $message.='<strong>Phone Number:</strong> '.$formData['phone'].'<br />';
                    $message.='<strong>Time Zone:</strong> '.$usersModel->getResult('timezone', $formData['zone']).'<br />';
                    $message.='<strong>Country:</strong> '.$usersModel->getResult('country', $formData['country']).'<br />';
                    $message.='<strong>SSN or EIN:</strong> '.$formData['ssn'].'<br />';
                    $message.='<strong>URL:</strong> '.$formData['url'].'<br />';
                    $message.='<strong>Title:</strong> '.$formData['title'].'<br />';
                    $message.='<strong>Description:</strong> '.$formData['description'].'<br />';
                    $message.='<strong>Keywords:</strong> '.$formData['keywords'].'<br />';
                    $message.='<strong>Category:</strong> '.$usersModel->getResult('category', $formData['category']).'<br />';
                    $message.='<strong>Privacy Policy:</strong> '.$privacy.'<br />';
                    $message.='<strong>Site Type:</strong> '.$type.'<br />';
                    $message.='<strong>Daily Visits:</strong> '.$formData['daily'].'<br /><br />';
                    $message.='<strong>Followers:</strong> '.$formData['followers'].'<br /><br />';
                    $message.='<a href="http://www.madadsmedia.com/administrator/index/view/id/'.$userID.'">View</a>';
                    //mail('support@madadsmedia.com', $title, $message, $headers);

                }else{
                    $session->message = 'Error please try again';
                }

                $this->_redirect('/tumblr-ads/');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }
    }

    public function newAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'newSite';

        $auth = Zend_Auth::getInstance()->getIdentity();
        

        
        $this->view->auth = $auth;

        $usersModel = new Application_Model_DbTable_Users();
        $this->view->category = $usersModel->querySelect('category');

        // creat form and send in view
        $form = new Application_Form_NewUserSite();
        $this->view->form = $form;

        //create session
        $session = new Zend_Session_Namespace('Default');

        //if isset message view
        if($session->message){
            $this->view->message = $session->message;
            unset($session->message);
        }

        if($this->getRequest()->isPost()){
            if($this->getRequest()->getPost('type')==3){
                $form->removeElement('privacy');
                $form->removeElement('daily');
            }else{
                $form->removeElement('followers');
            }
            if($form->isValid($this->getRequest()->getPost())){
                $sites = new Application_Model_DbTable_Sites();

                $dirty_params = $form->getValues();
                $params = array();
                foreach($dirty_params as $key => $value)
                {
                	$params[$key] = $this->_helper->Xss->xss_clean($value);
                }
                require_once LIB_PATH.'/Alexa/alexa.scraper.php';
                $site = str_replace("http://", '', $form->getValue('hidden_url'));
                $site = str_replace("https://", '', $site);
                $site = $this->_helper->Xss->xss_clean($site);
                $alexaData = Scrape($site);
               
                
                $sites->addNewUserSite($auth->id, $params, $alexaData);
                $session->message = '<p>Your website is now currently under approval. You will receive an email within the next 1-2 business days.</p>';

                $privacy = $formData['privacy']==1 ? "Yes" : "No";

                switch ($formData['type']){
                    case 1:
                        $type = 'Web Site';
                        break;
                    case 2:
                        $type = 'Application';
                        break;
                    case 3:
                        $type = 'Tumblr Account';
                        break;
                }

                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers .= "From: Publisher Support <support@madadsmedia.com>\r\n";

                //mail admin
                $title = 'New Website Application!';
                $message = '<strong>Email:</strong> '.$auth->email.'<br />';
                $message.='<strong>Company Name:</strong> '.$auth->company.'<br />';
                $message.='<strong>Contact Name:</strong> '.$auth->name.'<br />';
                $message.='<strong>Phone Number:</strong> '.$auth->phone.'<br />';
                $message.='<strong>Time Zone:</strong> '.$usersModel->getResult('timezone', $auth->zone).'<br />';
                $message.='<strong>Country:</strong> '.$usersModel->getResult('country', $auth->country).'<br />';

                $message.='<strong>URL:</strong> '.$form->getValue('hidden_url').'<br />';
                $message.='<strong>Title:</strong> '.$form->getValue('title').'<br />';
                $message.='<strong>Description:</strong> '.$form->getValue('description').'<br />';
                $message.='<strong>Keywords:</strong> '.$form->getValue('keywords').'<br />';
                $message.='<strong>Category:</strong> '.$usersModel->getResult('category', $form->getValue('category')).'<br />';
                $message.='<strong>Privacy Policy:</strong> '.$privacy.'<br />';
                $message.='<strong>Site Type:</strong> '.$type.'<br />';
                $message.='<strong>Daily Visits:</strong> '.$form->getValue('daily').'<br />';
                $message.='<strong>Followers:</strong> '.$form->getValue('followers').'<br /><br />';
                //mail('support@madadsmedia.com', $title, $message, $headers);

                $this->_redirect('/new-site/');
            }else
            {
            	$this->view->formErrors = $form->getMessages();
            	/*$dirty_values = $form->getValues();
            	foreach($dirty_values as $key => $value)
            	{
            		$this->view->formValues[$key] =  $this->_helper->Xss->xss_clean($value);
            	}*/
                $this->view->formValues = $form->getValues();
            }
        }
    }
    
    public function checkSiteNameAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$result = array('status' => 0, 'site_name' => false);
    	$site_name = $this->_getParam('site_name');
    	if(strlen($site_name))
    	{
    		$users_model = new Application_Model_DbTable_Users();
    		if($result['site_name'] = $users_model->checkIfSiteNameExist($site_name))
    		{
    			$result['status'] = 1;
    		}
    	}
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }

    public function paymentsAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
    	$layout->nav = 'payments';

    	$auth = Zend_Auth::getInstance()->getIdentity();
    	    	
    	$usersModel = new Application_Model_DbTable_Users();
    	$userData = $usersModel->getUserAllInfoById($auth->id);
    	if($userData['referral_system'])
    	{
    		$referral_model = new Application_Model_DbTable_Referral();
    		$this->view->total_revenue = $referral_model->getReferralsReport($auth->email);
    	}    	 
    	$this->view->payments = $usersModel->getUserPaymentDueByYear($auth->id, null);

    }

    public function paymentsBetaAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
    	$layout->nav = 'paymentsBeta';

    	$auth = Zend_Auth::getInstance()->getIdentity();
    	    	
    	$usersModel = new Application_Model_DbTable_Users();
    	$userData = $usersModel->getUserAllInfoById($auth->id);
    	if($userData['referral_system'])
    	{
    		$referral_model = new Application_Model_DbTable_Referral();
    		$this->view->total_revenue = $referral_model->getReferralsReport($auth->email);
    	}    	 
    	$this->view->payments = $usersModel->getUserPaymentDueByYearBeta($auth->id, null);

    }

    public function reportDateAction()
    {
        $layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');

        $auth = Zend_Auth::getInstance()->getIdentity();

        $date = $this->getRequest()->getParam('date');
        $ad_size = $this->getRequest()->getParam('ad_size');
        $user_id = $auth->id;

//        $date = $date ? $date : '2012-09-22';
//        $ad_size = $ad_size ? $ad_size : false;
//        $user_id = 1492;

        $date = date('Y-m-d', strtotime($date));

        $tableSite = new Application_Model_DbTable_Sites();

        $sql = $tableSite->select()->setIntegrityCheck(false)
                         ->from(array('s' => 'sites'), array('s.SiteID', 's.floor_pricing'))
                         ->where('PubID = ?', $auth->id);

        $dataSite = $tableSite->fetchAll($sql);


        $siteIDs = NULL;
        $this->view->absEcpm = null;
        $this->view->floor_pricing = NULL;
        $this->view->previosAllocted = null;
    	$this->view->previosImpressoin = null;

        foreach($dataSite as $iter){

            $siteIDs .= $iter['SiteID'].',';

            if($iter['floor_pricing'] == 1) $this->view->floor_pricing = 1;

        }   $siteIDs = substr($siteIDs, 0, strlen($siteIDs) - 1);

        $dataReport = $tableSite->getReportByDate($siteIDs, $date, $ad_size);

        //Zend_debug::dump($dataReport);

    	$ecpm = 0;

        $today = date('Y-m-d');

        $str1 = preg_split('/[-]/', $date);
        $str2 = preg_split('/[-]/', $today);

        $intDate1 = mktime(0, 0, 0, $str2[1], $str2[2], $str2[0]);
        $intDate2 = mktime(0, 0, 0, $str2[1], $str2[2] - 12 /* day */, $str2[0]);

    	if(count($dataReport) && $dataReport[0]['estimated']){

           $dataFinal = $tableSite->getFinalDataByUser($siteIDs, date('m/d/Y', $intDate2), date('m/d/Y', $intDate1), false);

                foreach($dataFinal as $key => $iter){ if($iter['estimated']) unset($dataFinal[$key]); }

                if(count($dataFinal) > 3){

                    $count = 0;

                    foreach($dataFinal as $key => $item){

                        $count++; if($count > 3) break;

                        if($count==1 && $item['paid_impressions'] && $item['impressions']){

                                $this->view->previosAllocted = $item['paid_impressions'];
                                $this->view->previosImpressoin = $item['impressions'];
                        }

                        if($item['revenue']){

                                if($this->view->floor_pricing==1 && $item['paid_impressions']) $ecpm = $ecpm + ($item['revenue']*1000/$item['paid_impressions']);
                            elseif($item['impressions'])                                       $ecpm = $ecpm + ($item['revenue']*1000/$item['paid_impressions']);
                        }

                    }
                }
        }

    	if($ecpm > 0) $this->view->absEcpm = round($ecpm/3, 2);

        $file = md5($auth->id.date('Y-m-d')).'.csv';

        if(is_file('csv/my-report/by-date/'.$file))  unlink('csv/my-report/by-date/'.$file);

        $this->view->file = $file;

        $this->view->data = $dataReport;

        $this->view->curent = array('date' => $date, 'ad_size' => $ad_size);

        $this->view->ad_sizes = array('1' => '300x250 Medium Rectangle',
                                      '2' => '728x90 Banner',
                                      '3' => '160x600 SkyScraper',
                                      '6' => 'Pop-unders');


    }

    public function minimumCpmAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
    	$layout->nav = 'minimumcpm';
        $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
        $tableRevshare = new Application_Model_DbTable_UserRevshare();

        $dataCpm = $tableCpm->createRow();
        $dataCpm->PubID = $this->_dataAuth->id;

        $sql = $tableRevshare->select()->where('PubID = ?', $this->_dataAuth->id)->order('date DESC');

        $dataRevshare = $tableRevshare->fetchRow($sql);

        $form = new Application_Form_Cpm_Minimum($dataCpm);
        $form->showStartData();

        if ($this->getRequest()->isPost()) {
                if ($form->isValid($this->_getAllParams())) {

                    $form->appendData();

                    $dataCpm->getEstim($dataRevshare->RevShare);
                    $dataCpm->getPrevFloor();
                    $dataCpm->createDynamic();
                    $dataCpm->checkPath();

                    $content['728x90'] = "document.writeln('".addslashes($dataCpm['728x90'])."');";
                    $content['300x250'] ="document.writeln('".addslashes($dataCpm['300x250'])."');";
                    $content['160x600'] ="document.writeln('".addslashes($dataCpm['160x600'])."');";

                    $path = $dataCpm->getPath();

                    file_put_contents($path.'/728x90.js', $content['728x90']);
                    file_put_contents($path.'/300x250.js', $content['300x250']);
                    file_put_contents($path.'/160x600.js', $content['160x600']);

                    $dataCpm->status = 1;
                    $dataCpm->created = date('Y-m-d');

                    /*$dataCpm->deleteApprovedData();*/

                    $cpmID = $dataCpm->save();

                    $form->getElement('SiteID')->setValue($dataCpm->SiteID);
                    $this->view->minimumCpm = array('id' => $cpmID, 'SiteName' => $dataCpm->getSiteName());
                }
        }

        $this->view->form = $form;
        $this->view->revShare = $dataRevshare->RevShare;
        


        $this->view->cancel = $this->_getParam('SiteName');

    }

    public function minimumCpmCancelAction()
    {
        $id = $this->_getParam('id');

        $tableFile = new Application_Model_DbTable_Cpm_File();
        $tableCpm = new Application_Model_DbTable_Cpm_Minimum();

        $sql = $tableCpm->select()
                        ->where('PubID = ?', $this->_dataAuth->id)
                        ->where('id = ?', $this->_getParam('id'));

        $dataCpm = $tableCpm->fetchRow($sql);
        $siteName = $dataCpm->getSiteName();

        $tableFile->delete('minimum_cpm_id = '.$dataCpm->id);
        $dataCpm->delete();

        $this->_redirect('/minimum-cpm/'.$siteName);
    }

    public function pdfAction()
    {
        $layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
        $layout->nav = 'payment';
    }

    public function ajaxMinimumCpmAction(){
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('reports');
        $layout->nav = 'minimumcpm';

        $cpmValuesModel = new Application_Model_DbTable_Cpm_Value();
        $sitesModel = new Application_Model_DbTable_Sites();

        $this->view->cpm = $cpmValuesModel->getValues();
        $this->view->site = $sitesModel->getSitesWidthTagsByPubID($this->_dataAuth->id);
        $this->view->countSite = count($this->view->site);
    }

    public function inviteRequestAction()
    {
        $tableUser = new Application_Model_DbTable_Users();

        $sql = $tableUser->select()
                         ->where('inviteRequest IS NULL')
                         ->where('id = ?', $this->_dataAuth->id);

        $dataUser = $tableUser->fetchRow($sql);
        $dataUser->inviteRequest = 1;
        $dataUser->save();

        if($_SERVER['HTTP_REFERER'] && strpos($_SERVER['HTTP_REFERER'], 'http://adcito')){

            $url = str_replace('http://madadsmedia.com', '', $_SERVER['HTTP_REFERER']);
            $url = str_replace('http://media', '', $url);

            $this->_redirect($url);

        } else { $this->_redirect('/report'); }

    }

    public function termAction()
    {
        $this->_helper->layout()->disableLayout();
    }

    public function deniedAction()
    {
        $layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
        $layout->nav = 'denied';
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $sitesModel = new Application_Model_DbTable_Sites();

        if($auth->role!='admin' && $auth->role!='super')
            $layout->NoPsaUsers = true;

        $psaModel = new Application_Model_DbTable_Psa();


        $this->view->sites = $sitesModel->getApprovedSitesByUserId($auth->id, false, 'sites.SiteName ASC');
        $this->view->PubID = $auth->id;
        $this->view->auth = $auth;
    }

    public function httpsadsAction()
    {
        $this->view->headMeta('Monetize your HTTPS/SSL website with our HTTPS/SSL secured ads.', 'description');
        $this->view->headMeta('https ads, ssl ads, https ad network, ssl ad network, https advertisements', 'keywords');
        $this->view->headTitle('MadAdsMedia.com - HTTPS/SSL Advertisements - Monetize Your Secure Website');

        $usersModel = new Application_Model_DbTable_Users();

        $this->view->zones = $usersModel->querySelect('timezone');
        $this->view->country = $usersModel->querySelect('country');
        $this->view->state = $usersModel->querySelect('state');
        $this->view->server = $usersModel->querySelect('service', 'service');
        $this->view->category = $usersModel->querySelect('category');
    }

    public function bannedAction()
    {
        $layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('reports');
        $layout->nav = 'banned';
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        $sitesModel = new Application_Model_DbTable_Sites();


        
        $this->view->sites = $sitesModel->getApprovedSitesByUserId($auth->id, false, 'sites.SiteName ASC');
        $this->view->PubID = $auth->id;
        $this->view->auth = $auth;
    }

    public function requestMobileAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id = (int)$this->_getParam('site_id');

        $tableWantBaner = new Application_Model_DbTable_Sites_WantBaner();

        $sql = $tableWantBaner->select()->where("SiteID='".$id."'");
        $dataBaner = $tableWantBaner->fetchRow($sql);
		$result = array('status' => 1);
        if(!$dataBaner->id){
            $tableWantBaner->insert(array(
                'SiteID'=>$id,
                'created'=>date("Y-m-d")
            ));
			$result = array();
            $session = new Zend_Session_Namespace('Default');
            $session->message = "Your request for this ad size has been submitted.<br>You should receive a confirmation with 48 hours of approval.";
        	$result['status'] = 0;        	
        }
        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
        //$this->_redirect('/adcode/'.$id);

    }
    
    public function requestBannerAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$params = $this->_getAllParams();
    	$sizesRequestModel = new Application_Model_DbTable_SizesRequest();

        $networks = explode(":", $params['networks']);

        $dataRequest = $sizesRequestModel->createRow();
        $dataRequest->setFromArray([
            'site_id'=>(int)$params['site_id'],
            'size_id'=>(int)$params['size_id'],
            'network_current_id'=>(int)$networks[0],
            'network_requested_id'=>(int)$networks[1],
            'created_at'=>date("Y-m-d H:i:s"),
            'status'=>1
        ]);
        $dataRequest->save();

        if($dataRequest->id)
            $result['status'] = 1;
        else
            $result = [];

    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function testMinimumCpmAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout(); 
                           
        $tableSite = new Application_Model_DbTable_Sites();
        $tableFile = new Application_Model_DbTable_Cpm_File();
        $tableMinimum = new Application_Model_DbTable_Cpm_Minimum();
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
     
        $sql = $tableFile->select()
                         ->where('file IS NOT NULL')
                         ->where('minimum_cpm_id = ?', (int) $this->_getParam('id'));

        if($dataAuth->role == 'user') $sql->where('PubID = ?', $dataAuth->id);
        
              $dataFile = $tableFile->fetchAll($sql); 
		if(count($dataFile)){
		
			$dataSite = $tableSite->getSiteInfoByID($dataFile[0]->SiteID);
			 
			$this->view->data = $dataFile;
		
		} else { $this->view->message = '<strong style="color: red">Passback tags not found</strong>'; }
        
    } 
    
    public function requestIframeAction()
    {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$params = $this->_getAllParams();
    	$tags_model = new Application_Model_DbTable_Tags();
    	$result = $tags_model->setRequestIframe($params);
    	$this->getResponse()->setBody(Zend_Json::encode(array('status'=>1)))->setHeader('content-type', 'application/json', true);
    }       

	public function requestNewBurstAction()
	{
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$params = $this->_getAllParams();
    	$tags_model = new Application_Model_DbTable_Tags();
    	$result = $tags_model->setRequestNewBurst($params);
    	$this->getResponse()->setBody(Zend_Json::encode(array('status'=>1)))->setHeader('content-type', 'application/json', true);
		
	}

    public function changeVisitorAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $result = [];
        $site_id = (int)$this->getRequest()->getPost('site_id');
        $visitor = (int)$this->getRequest()->getPost('visitor');
        $dataAuth = Zend_Auth::getInstance()->getIdentity();

        $tagsModel = new Application_Model_DbTable_SitesTags();

        $dataTags = $tagsModel->fetchRow([
            $tagsModel->getAdapter()->quoteInto('site_id = ?', $site_id),
            $tagsModel->getAdapter()->quoteInto('pub_id = ?', $dataAuth->id),
            $tagsModel->getAdapter()->quoteInto('network_id = ?', 16),
            $tagsModel->getAdapter()->quoteInto('size_id = ?', 6)
        ]);

        if($dataTags){
            $propModel = new Application_Model_DbTable_TagsProperties();
            $propModel->update([
                'value'=>$visitor
            ],[
                $propModel->getAdapter()->quoteInto('tag_id = ?', $dataTags->id),
                $propModel->getAdapter()->quoteInto('name = ?', 'popVisitor')
            ]);

            $tagsModel->update([
                'action'=>'gen'
            ],[
                $tagsModel->getAdapter()->quoteInto('id = ?', $dataTags->id)
            ]);

        }

        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }

    public function requestSliderAction(){
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $sliderModel = new Application_Model_DbTable_SliderRequest();
        $tagsModel = new Application_Model_DbTable_SitesTags();

        $site_id = (int)$this->getRequest()->getPost('site_id');

        $sliderModel->insert([
            'site_id'=>$site_id,
            'created_at'=>date('Y-m-d H:i:s')
        ]);

        $tagsModel->update([
            'action'=>'gen'
        ],[
            $tagsModel->getAdapter()->quoteInto('site_id = ?', $site_id),
            '`primary` = 1'
        ]);

        $result['status'] = 1;

        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
}

