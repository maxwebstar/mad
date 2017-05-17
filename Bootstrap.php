<?php


class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{


    protected function _initAutoload()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('My_');

    }

    public function _initDatabase()
    {
        $this->bootstrapDb();
        Zend_Session::setOptions(array('cookie_httponly' => true));
    }

    protected function _initFront()
    {
        $front = Zend_Controller_Front::getInstance();

        $front->setControllerDirectory(array(
            'default' => APPLICATION_PATH.'/default/controllers',
            'administrator' => APPLICATION_PATH.'/administrator/controllers',
            ));

        $front->setDefaultControllerName('index');


    }
	
    function _initCache() {
		$this->bootstrap ( 'cachemanager' );
		$manager = $this->getResource ( 'cachemanager' );
		Zend_Registry::set ( 'Cache_Manager', $manager );

		$this->bootstrap ( 'db' );
		Zend_Db_Table_Abstract::setDefaultMetadataCache ( $manager->getCache ( 'database' ) );
    }

    protected function _initRoutes() {

        $front  = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        $router->addRoute(
                            'registration',
                            new Zend_Controller_Router_Route('registration',
                                                             array('controller' => 'users',
                                                                    'action' => 'registration',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'tumblrads',
                            new Zend_Controller_Router_Route('tumblr-ads',
                                                             array('controller' => 'users',
                                                                    'action' => 'tumblrads',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'login',
                            new Zend_Controller_Router_Route('login',
                                                             array('controller' => 'auth',
                                                                    'action' => 'login',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'logout',
                            new Zend_Controller_Router_Route('logout',
                                                             array('controller' => 'auth',
                                                                    'action' => 'logout',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'forgot',
                            new Zend_Controller_Router_Route('forgot/:code',
                                                             array('controller' => 'users',
                                                                    'action' => 'forgot',
                                                                    'module'=>'default',
                                                                    'code'=>''))
                        );

        $router->addRoute(
                            'oldreport',
                            new Zend_Controller_Router_Route('oldreport',
                                                             array('controller' => 'users',
                                                                    'action' => 'testreport',
                                                                    'module'=>'default'))
                        );
        /*
        $router->addRoute(
        		'newreport',
        		new Zend_Controller_Router_Route('/users/report/',
        				array('controller' => 'users',
        						'action' => 'testreport',
        						'module'=>'default'))
        );


        $router->addRoute(
        		'newreport',
        		new Zend_Controller_Router_Route('newreport',
        				array('controller' => 'users',
        						'action' => 'testreport',
        						'module'=>'default'))
        );
        */

        $router->addRoute(
        		'testreport',
        		new Zend_Controller_Router_Route('testreport',
        				array('controller' => 'report',
        						'action' => 'index',
        						'module'=>'default'))
        );
        
        $router->addRoute(
        		'viewallnews',
        		new Zend_Controller_Router_Route('viewallnews',
        				array('controller' => 'report',
        						'action' => 'viewallnews',
        						'module'=>'default'))
        );

        $router->addRoute(
        		'csv-report',
        		new Zend_Controller_Router_Route('csv-report',
        				array('controller' => 'report',
        						'action' => 'csv',
        						'module'=>'default'))
        );

        $router->addRoute(
        		'csv-report-view',
        		new Zend_Controller_Router_Route('csv-report-view',
        				array('controller' => 'report',
        						'action' => 'csvview',
        						'module'=>'default'))
        );

        $router->addRoute(
                            'newSite',
                            new Zend_Controller_Router_Route('new-site',
                                                             array('controller' => 'users',
                                                                    'action' => 'new',
                                                                    'module'=>'default'))
                        );
        
        $router->addRoute(
        		'check-site-name',
        		new Zend_Controller_Router_Route('check-site-name',
        				array('controller' => 'users',
        						'action' => 'check-site-name',
        						'module'=>'default'))
        );


        $router->addRoute(
        				'zones',
        				new Zend_Controller_Router_Route('zones',
        					array('controller' => 'users',
        						  'action' => 'zones',
                                  'module'=>'default'))
        );


        $router->addRoute(
                            'adcode',
                            new Zend_Controller_Router_Route('adcode/:site',
                                                             array('controller' => 'users',
                                                                    'action' => 'adcode',
                                                                    'module'=>'default',
                                                                    'site'=>''))
                        );


        $router->addRoute(
                            'contact',
                            new Zend_Controller_Router_Route('contact',
                                                             array('controller' => 'users',
                                                                    'action' => 'contact',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'password',
                            new Zend_Controller_Router_Route('password',
                                                             array('controller' => 'users',
                                                                    'action' => 'password',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'payment',
                            new Zend_Controller_Router_Route('payment',
                                                             array('controller' => 'users',
                                                                    'action' => 'payment',
                                                                    'module'=>'default'))
                        );
        
        $router->addRoute(
        		'news',
        		new Zend_Controller_Router_Route('administrator/news/edit/:id',
        				array('controller' => 'news',
        						'action' => 'edit',
        						'module' =>'administrator',
        						'id' => 0))
        );

        $router->addRoute(
                            'about',
                            new Zend_Controller_Router_Route('about-us',
                                                             array('controller' => 'index',
                                                                    'action' => 'about',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'cpm',
                            new Zend_Controller_Router_Route('cpm',
                                                             array('controller' => 'index',
                                                                    'action' => 'cpm',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'getcpm',
                            new Zend_Controller_Router_Route('getcpm',
                                                             array('controller' => 'index',
                                                                    'action' => 'getcpm',
                                                                    'module'=>'default'))
                        );


        $router->addRoute(
                            'contacts',
                            new Zend_Controller_Router_Route('contact-us',
                                                             array('controller' => 'contacts',
                                                                    'action' => 'index',
                                                                    'module'=>'default'))
                        );

        $router->addRoute(
                            'adminPaymentDue',
                            new Zend_Controller_Router_Route('administrator/sites/payments/:year/:month/:filter',
                                                             array('controller' =>'sites',
                                                                    'action' =>'payments',
                                                                    'module'=>'administrator',
                                                                    'year'=>date("Y"),
                                                                    'month'=>date("n"),
                                                             		'filter'=>'all'
                                                                    ))
                        );

        $router->addRoute(
        		'adminNetworkStats',
        		new Zend_Controller_Router_Route('administrator/index/network/:year/:month/:rubicon_15',
        				array('controller' =>'index',
        						'action' =>'network',
        						'module'=>'administrator',
        						'year'=>date("Y"),
        						'month'=>date("n"),
                                                        'rubicon_15' => 0
        				))
        );


        $router->addRoute(
        		'adminDailyStats',
        		new Zend_Controller_Router_Route('administrator/index/daily/:year/:month',
        				array('controller' =>'index',
        						'action' =>'daily',
        						'module'=>'administrator',
        						'year'=>date("Y"),
        						'month'=>date("n"),
                                             ))
        );


        $router->addRoute(
        		'faq',
        		new Zend_Controller_Router_Route('faq',
        				array('controller' => 'index',
        						'action' => 'faq',
        						'module'=>'default'))
        );

        $router->addRoute(
        		'payments',
        		new Zend_Controller_Router_Route('payments',
        				array('controller' => 'users',
        						'action' => 'payments',
        						'module'=>'default'))
        );

        $router->addRoute(
        		'payments-beta',
        		new Zend_Controller_Router_Route('payments-beta',
        				array('controller' => 'users',
        						'action' => 'payments-beta',
        						'module'=>'default'))
        );

        $router->addRoute(
        		'disable-notification1',
        		new Zend_Controller_Router_Route('disable-notification/:code',
        				array('controller' => 'index',
        			              'action' => 'disable-notification',
        				      'module'=>'default'))
        );

        $router->addRoute(
        		'disable-notification2',
        		new Zend_Controller_Router_Route('disable-notification/:code/:confirm',
        				array('controller' => 'index',
        			              'action' => 'disable-notification',
        				      'module'=>'default'))
        );

        $router->addRoute(
        		'adminLiveStats',
        		new Zend_Controller_Router_Route('administrator/sites/live/:date',
        				array('controller' =>'sites',
        						'action' =>'live',
        						'module'=>'administrator',
        						'date'=>date("Y-m-d")
        				))
        );
        
        $router->addRoute(
                            'minimum-cpm',
                            new Zend_Controller_Router_Route('minimum-cpm',
                                                             array('controller' => 'users',
                                                                    'action' => 'ajax-minimum-cpm',
                                                                    'module'=>'default'))
                        );


        $router->addRoute(
                            'w-9',
                            new Zend_Controller_Router_Route('w-9',
                                                             array('controller' => 'users',
                                                                    'action' => 'pdf',
                                                                    'module'=>'default'))
                        );


        $router->addRoute(
        		'adminRefferedUsers',
        		new Zend_Controller_Router_Route('administrator/referral/user/:ref_id/:from/:to',
        				array('controller' =>'referral',
        						'action' =>'user',
        						'module'=>'administrator',
        						'from'=>date("Y-m-d", mktime(0,0,0,date("n"), 1, date("Y"))),
        						'to'=>date("Y-m-d", mktime(0,0,0,date("n")+1, 0, date("Y"))),
                                'ref_id' => 0
        				))
        );

        $router->addRoute(
        		'invite-request',
        		new Zend_Controller_Router_Route('invite-request',
        				array('controller' => 'users',
                                              'action' => 'invite-request',
                                              'module'=>'default'))
        );

        $router->addRoute(
        		'term',
        		new Zend_Controller_Router_Route('term',
        				array('controller' => 'users',
                                              'action' => 'term',
                                              'module'=>'default'))
        );

        $router->addRoute(
        		'mobile',
        		new Zend_Controller_Router_Route('mobile',
        				array('controller' => 'index',
        						'action' => 'mobile',
        						'module'=>'default'))
        );

        $router->addRoute(
        		'full',
        		new Zend_Controller_Router_Route('full',
        				array('controller' => 'index',
        						'action' => 'full',
        						'module'=>'default'))
        );

        $router->addRoute(
        		'terms-of-service',
        		new Zend_Controller_Router_Route('terms-of-service',
        				array('controller' => 'index',
        						'action' => 'terms',
        						'module'=>'default'))
        );

        $router->addRoute(
        		'denied-urls',
        		new Zend_Controller_Router_Route('denied-urls',
        				array('controller' => 'users',
                                              'action' => 'denied',
                                              'module'=>'default'))
        );

        $router->addRoute(
        		'banned-urls',
        		new Zend_Controller_Router_Route('banned-urls',
        				array('controller' => 'users',
                                              'action' => 'banned',
                                              'module'=>'default'))
        );

        $router->addRoute(
        		'https-ads',
        		new Zend_Controller_Router_Route('https-ads',
        				array('controller' => 'users',
                                              'action' => 'httpsads',
                                              'module'=>'default'))
        );


        $router->addRoute(
        		'adminRecruitingStats',
        		new Zend_Controller_Router_Route('administrator/recruiting/stats/:year/:month',
        				array('controller' =>'recruiting',
        						'action' =>'stats',
        						'module'=>'administrator',
        						'year'=>date("Y"),
        						'month'=>date("n"),
                                             ))
        );

        $router->addRoute(
        		'careers',
        		new Zend_Controller_Router_Route('careers',
        				array('controller' => 'index',
                                              'action' => 'careers',
                                              'module'=>'default'))
        );
		$router->addRoute(
        		'buy',
        		new Zend_Controller_Router_Route('buy/network/:network/:status',
        				array('controller' => 'buy',
                                              'action' => 'index',
                                              'module'=>'default',
											  'network'=>'advertising.com',
        				                      'status' => 2
		))
        );
        
        $router->addRoute(
                            'update-pass',
                            new Zend_Controller_Router_Route('update-password/update/:code',
                                                             array('controller' => 'update-password',
                                                                    'action' => 'update',
                                                                    'module'=>'default',
                                                                    'code'=>''))
                        );

        $router->addRoute(
                            'setBurst',
                            new Zend_Controller_Router_Route('administrator/burst/set/:date',
                                                             array('controller' => 'burst',
                                                                    'action' => 'set',
                                                                    'module'=>'administrator',
                                                                    'code'=>''))
                        );

        $router->addRoute(
            'landing',
            new Zend_Controller_Router_Route('landing/1',
                array('controller' =>'landing',
                    'action' =>'index',
                    'module'=>'default'
                ))
        );


        $router->addRoute(
            'landing-ref',
            new Zend_Controller_Router_Route('landing/1/ref/:id',
                array('controller' =>'landing',
                    'action' =>'index',
                    'module'=>'default',
                    'id'=>null
                ))
        );

        $link =  substr($_SERVER["REQUEST_URI"], 1);

        $arrLink = preg_split('[/]', $link);

        if(in_array('ref', $arrLink)){

           $key = array_search('ref', $arrLink);
           $id = $arrLink[$key + 1];

           unset($arrLink[$key]);
           unset($arrLink[$key + 1]);

           $resultLink = null;

           foreach($arrLink as $iter){

               $resultLink .= '/' . $iter;

           }
           $resultLink = ($resultLink == NULL) ? '/index' : $resultLink;

           $dataUser = null;

              $auth = Zend_Auth::getInstance();
           if($auth->hasIdentity()) { $dataUser = $auth->getIdentity(); }

           $id = (int)$id;

           $tableReferral = new Application_Model_DbTable_Referral();
           $sql = $tableReferral->select()->where('id = ?', $id);
           $dataReferral = $tableReferral->fetchRow($sql);

           if(empty($_COOKIE['referral_id']) && $dataReferral && empty($dataUser)){

                $tableReferralStat = new Application_Model_DbTable_ReferralStat();
                $sql = $tableReferralStat->select()->where('refID = ?', $id)->where('query_date = ?', date("Y-m-d"));
                $dataReferralStat = $tableReferralStat->fetchRow($sql);

              $dataReferralStat->num_click += 1;
              //$dataReferral->save();
              $sql = "INSERT INTO referral_stat (refID, num_click, query_date) VALUES ('".$id."', '".$dataReferralStat->num_click."', '".date("Y-m-d")."') ON DUPLICATE KEY UPDATE refID='".$id."', num_click = '".$dataReferralStat->num_click."', query_date='".date("Y-m-d")."'";
              $tableReferralStat->getDefaultAdapter()->query($sql);
              setcookie('referral_id', $id, time() + 86400, '/');

           }

           $response = new Zend_Controller_Response_Http();
           $response->setRedirect($resultLink);
           $front->setResponse($response);

        }

    }

    protected function _initAcl()
    {

        $acl = new Zend_Acl();

        //add resources
        $acl->addResource('default_error');
        $acl->addResource('default_index');
        $acl->addResource('default_users');
        $acl->addResource('default_auth');
        $acl->addResource('default_contacts');
        $acl->addResource('default_referral');
        $acl->addResource('default_test');
        $acl->addResource('default_ajax');
        $acl->addResource('default_report');
        $acl->addResource('default_buy');
        $acl->addResource('default_update-password');
        $acl->addResource('default_landing');

        $acl->addResource('administrator_index');
        $acl->addResource('administrator_tags');
        $acl->addResource('administrator_sites');
        $acl->addResource('administrator_exchangesites');
        $acl->addResource('administrator_referral');
        $acl->addResource('administrator_recruiting');
        $acl->addResource('administrator_ajax');
        $acl->addResource('administrator_setting');
        $acl->addResource('administrator_system');
        $acl->addResource('administrator_cpm');
        $acl->addResource('administrator_sizes');
        $acl->addResource('administrator_adx');
        $acl->addResource('administrator_optimization');
        $acl->addResource('administrator_report');
        $acl->addResource('administrator_contact');
        $acl->addResource('administrator_exchange');
        $acl->addResource('administrator_contactnotification');
        $acl->addResource('administrator_alerts');
        $acl->addResource('administrator_checker');
        $acl->addResource('administrator_news');
        $acl->addResource('administrator_payments');
        $acl->addResource('administrator_pending-correction');
        $acl->addResource('administrator_url');
        $acl->addResource('administrator_sizes-requests');
        $acl->addResource('administrator_dashboard');
        $acl->addResource('administrator_co-site-approvals');
        $acl->addResource('administrator_google-api');
        $acl->addResource('administrator_burst');
        $acl->addResource('administrator_difference');
        $acl->addResource('administrator_iframe-requests');
        $acl->addResource('administrator_quickbooks');
        $acl->addResource('administrator_pubmatic');
        $acl->addResource('administrator_task');        
        $acl->addResource('administrator_all');        
        $acl->addResource('administrator_staff');
        $acl->addResource('administrator_sub');
        $acl->addResource('administrator_pp-accounts');
        $acl->addResource('administrator_networks');
        $acl->addResource('administrator_api-manager');
        $acl->addResource('administrator_revshares');

        //add role
        $acl->addRole('guest');
        $acl->addRole('user', 'guest');
        $acl->addRole('support', 'user');
        $acl->addRole('admin', 'support');
        $acl->addRole('super', 'admin');

        $acl->allow('guest', 'default_error', array('error'));
        $acl->allow('guest', 'default_index', array('index', 'about', 'cpm', 'getcpm', 'faq', 'disable-notification', 'check-missing-tag', 'mobile', 'full', 'terms', 'careers'));
        $acl->allow('guest', 'default_users', array('index', 'registration', 'tumblrads', 'forgot', 'make', 'setcache', 'term', 'httpsads'));
        $acl->allow('guest', 'default_auth', array('index', 'login', 'logout'));
        $acl->allow('guest', 'default_contacts', array('index'));
        $acl->allow('guest', 'default_referral', array('index'));
        $acl->allow('guest', 'default_update-password', array('index', 'update'));
        $acl->allow('guest', 'default_landing', array('index'));

        $acl->allow('user', 'default_ajax', array('index', 'check-pdf', 'check-site-cpm', 'get-payment-amount', 'check-minimum-cpm', 'save-minimum-cpm', 'cancel-minimum-cpm', 'denied', 'banned', 'test-save-minimum-cpm', 'delete-minimum-cpm', 'get-report-madx'));
        $acl->allow('user', 'default_users', array('report', 'adcode', 'contact', 'password', 'payment', 'new', 'check-site-name', 'payments', 'payments-beta', 'newreport', 'testreport', 'pdf', 'report-date', 'invite-request', 'change-visitor'));
        $acl->allow('user', 'default_users', array('zones', 'ajax-minimum-cpm', 'minimum-cpm-cancel', 'denied', 'request-mobile', 'request-banner', 'banned', 'test-minimum-cpm', 'request-iframe', 'request-new-burst', 'request-slider'));
        $acl->allow('user', 'default_report', array('index', 'viewallnews', 'indexb', 'data', 'view', 'csv', 'csvview', 'size', 'set-auto-report', 'del-auto-report'));
	    $acl->allow('user', 'default_buy', array('index', 'ajax-option'));

        $acl->allow('support', 'administrator_index', array('index', 'get-ajax-new', 'approved', 'ajax-approved', 'all', 'ajax-denied','new-waiting', 'ajax-new-waiting', 'view', 'contact', 'updateaprovd', 'reachout', 'auth'));
        $acl->allow('support', 'administrator_sites', array('new', 'ajax-new', 'approved', 'ajax-approved', 'inactive', 'ajax-inactive', 'denied', 'ajax-denied', 'view', 'viewnew', 'live', 'approved-column', 'update', 'mail-ever-live', 'contact', 'ajax-optimization-get-values', 'followup', 'ajax-hide', 'invite-denied'));
        $acl->allow('support', 'administrator_co-site-approvals', array('index', 'ajax-get', 'approve-site', 'approve-user', 'send'));
        $acl->allow('support', 'administrator_checker', array('index', 'ajax-get', 'updatechecker', 'send'));
        $acl->allow('support', 'administrator_alerts', array('index', 'add', 'get-ajax', 'close', 'edit', 'delete', 'get-ajax-assign'));
        $acl->allow('support', 'administrator_ajax', array('index'));
        $acl->allow('support', 'administrator_all', array('index', 'ajax-get', 'ajax-get-recruiting'));

        $acl->allow('admin', 'administrator_index', array('psaurls','denied','urllookup','blockedurls','unnaprovedurls','approvedservingurls','approvediframe','approvedwebsites','activity', 'view', 'save', 'updateaprovd', 'auth', 'back', 'generatenetwork', 'psaurls-new', 'get-ajax-psaurls', 'ajax-status-psaurl', 'new2', 'ajax-new2', 'new-account', 'ajax-new-account-user', 'ajax-new-account-site', 'approved-date-user', 'ajax-approved-date-user', 'approve-live', 'ajax-approve-live', 'contact', 'reachout', 'view-tags-create', 'view-prev-live-site', 'view-first-live', 'view-total-live', 'view-inactive', 'daily', 'ajax-reject', 'disable', 'un-disable'));
        $acl->allow('admin', 'administrator_tags', array('index', 'add', 'ajax-tags', 'ajax-no-tags', 'edit', 'notified', 'placement', 'block-site', 'revert-old-tag'));
        $acl->allow('admin', 'administrator_news', array('index','edit','add', 'delete' ,'ajax-get'));
        $acl->allow('admin', 'administrator_sites', array('index', 'add', 'add-ajax', 'add-note', 'delete-note', 'ajax-optimization-set-values', 'ajax-optimization-get-values', 'impress-stats', 'ajax-stats', 'view', 'viewnew-co', 'update', 'viewnew', 'userpayment', 'generate', 'comments', 'sendmail', 'live', 'csv', 'csv2', 'csv3', 'pubmatic-csv', 'csvupload', 'invite-denied', 'block', 'un-block', 'confirm-ajax', 'generate-report', 'load-url', 'ajax-url', 'ajax-url-iframe', 'ajax-get-denied-url', 'denied-url-hide', 'send', 'ajax-hide', 'followup', 'deducted', 'ajax-deducted', 'get-ajax-user', 'approved-column', 'mail-ever-live', 'contact', 'mail-contacted', 'amazon-csv', 'pulse-csv', 'pop-csv', 'sekindo-csv', 'aol-csv', 'aol-outstream-csv', 'b-real-time-csv'));
        $acl->allow('admin', 'administrator_exchangesites', array('index', 'data','view','pending','approve' ,'deny', 'update','approval'));
        $acl->allow('admin', 'administrator_recruiting', array('index','emailsnotfound','requestdsl','requestrnews','requestaws','requestrmws','requestupdate','requestdelete','activity','downloadwebsites','potentialsites', 'import', 'importws' ,'upload','send', 'data','response','templates', 'template', 'view', 'edit', 'pending', 'responded', 'neverresponded', 'closed', 'setclosed', 'stats', 'get-stats-ajax', 'ajax-get-sub', 'get-ajax-pending', 'edit-email', 'manually-notifi', 'stage-opportunities', 'set-manual', 'delete', 'get-ajax-responded', 'follow-up-manual', 'contact', 'get-ajax-closed', 'update-opportunity', 'ajax-get-stat-sub', 'closed-opportunities', 'get-ajax-closed-opportunities', 'won', 'get-ajax-won'));
        $acl->allow('admin', 'administrator_ajax', array('index','ajax-make-csv' ,'csv', 'validator-site-name', 'validator-site-name-exist', 'add-site', 'sent-for-approval', 'test', 'confirm-site', 'optimization-upload', 'site-want-api', 'generate-due', 'denied-url-hide', 'denied-site-hide', 'approve-psa-url', 'fixed-site', 'save-optimization-status', 'set-task-status'));
        $acl->allow('admin', 'administrator_system', array('index', 'activity','data','phpinfo', 'ajax-activity'));
        $acl->allow('admin', 'default_test', array('index', 'ajax', 'site', 'by-site', 'live', 'day', 'tag', 'set-lived', 'set-yesterday-live', 'cook', 'mail', 'create-pdf', 'pdf', 'cpm', 'minimum-cpm', 'tag3', 'create-xml', 'get-xml', 'tag2', 'create-xml2', 'get-xml2', 'tag4', 'tag5', 'tag6', 'tag7', 'check-pb', 'tag8', 'tag9', 'tag-default1', 'tag-default2', 'tag-default3', 'tag-default4', 'tag11', 'tag12', 'tag13', 'tag14', 'tag15', 'tag-iframe', 'check-network', 'same', 'my', 'mem-delete', 'mail-test', 'mail', 'view-mail', 'mail-show', 'ajax-mail'));
        $acl->allow('admin', 'administrator_cpm', array('index', 'ajax-get', 'approve', 'approve-burst', 'view', 'test', 'value', 'add-value', 'edit-value', 'delete-value', 'reject'));
        $acl->allow('admin', 'administrator_adx', array('index', 'change', 'ajax-get', 'edit-invite-url'));
        $acl->allow('admin', 'administrator_optimization', array('index', 'ajax-done', 'ajax-pending', 'pending', 'done', 'change', 'clear-pending', 'all', 'ajax-all'));
        $acl->allow('admin', 'administrator_report', array('index', 'get'));
        $acl->allow('admin', 'administrator_contact', array('index', 'pending-ajax' , 'ajax', 'approve', 'delete', 'view'));
        $acl->allow('admin', 'default_report', array('edit'));
        $acl->allow('admin', 'administrator_pending-correction', array('index', 'ajax-get', 'send'));
        $acl->allow('admin', 'administrator_url', array('index', 'get-ajax'));
        $acl->allow('admin', 'administrator_sizes-requests', array('index', 'get-ajax', 'enable', 'disable'));
        $acl->allow('admin', 'administrator_dashboard', array('index', 'ajax-live', 'ajax-never-live', 'get-impress-stat', 'ajax-inactive', 'ajax-week-statistic', 'site-day-statistic', 'ajax-site-day-statistic', 'ajax-top-earners'));
        $acl->allow('admin', 'administrator_co-site-approvals', array('approve-site', 'approve-user', 'send'));
        $acl->allow('admin', 'administrator_google-api', array('index', 'ajax-check-access', 'set-access', 'get-data'));
        $acl->allow('admin', 'administrator_referral', array('get-ajax', 'view', 'get-view-ajax'));
        $acl->allow('admin', 'administrator_burst', array('index', 'get-ajax', 'set-cpm', 'update-ajax', 'update-ajax-all', 'set', 'burst-requests', 'ajax-burst-requests', 'deny', 'update-status'));
        $acl->allow('admin', 'administrator_iframe-requests', array('index', 'get-ajax', 'enable', 'disable'));
        $acl->allow('admin', 'administrator_quickbooks', array('index'));
        $acl->allow('admin', 'administrator_task', array('index', 'add', 'edit', 'delete'));

        $acl->allow('super', 'administrator_sites', array('notification', 'blacklists'));
        $acl->allow('super', 'administrator_payments', array('index', 'beta', 'ajax-get-beta', 'pending', 'pending-ajax', 'pending-view' , 'pending-approve' , 'pending-delete' ,  'ajax-get', 'setcoment', 'viewcomment', 'getcoment', 'revert', 'level', 'level-add', 'level-edit', 'level-delete', 'generate-report', 'paid-beta'));
        $acl->allow('super', 'administrator_index', array('network'));
        $acl->allow('super', 'administrator_referral', array('index', 'add', 'edit', 'delete', 'user', 'signup', 'get-signup-ajax'));
        $acl->allow('super', 'administrator_setting', array('index', 'edit'));
        $acl->allow('super', 'administrator_sizes', array('index', 'add', 'edit'));
        $acl->allow('super', 'administrator_contactnotification', array('index', 'add', 'edit', 'delete', 'assign', 'remove' , 'manage-referals', 'assign-referals', 'assign-primary-referals'));
        $acl->allow('super', 'administrator_exchange', array('index', 'view', 'size'));
        $acl->allow('super', 'administrator_difference', array('index', 'impressions', 'ajax-impressions', 'ecpm', 'ajax-ecpm'));
        $acl->allow('super', 'administrator_pubmatic', array('index', 'add-site', 'add-pub-site', 'get-site-data', 'create-tag-site', 'generate-tag-site', 'update-ad-tag-placement', 'update-placement', 'get-ajax-tag', 'get-tag-placement-id', 'get-network-placements'));
        $acl->allow('super', 'administrator_staff', array('index', 'add', 'edit'));
        $acl->allow('super', 'default_report', array('beta', 'beta-ajax'));
        $acl->allow('super', 'administrator_pp-accounts', array('index', 'ajax-get', 'add', 'edit', 'del'));
        $acl->allow('super', 'administrator_networks', array('index', 'add', 'get-networks-ajax', 'edit'));
        $acl->allow('super', 'administrator_api-manager', array('index', 'set-rubicon', 'set-pubm', 'set-pop'));
        $acl->allow('super', 'administrator_revshares', array('index', 'ajax-rev', 'ajax-no-rev', 'add', 'edit'));

        $fc = Zend_Controller_Front::getInstance();

        //load the plugin to check user access rights
        $fc->registerPlugin(new Application_Plugin_AccessCheck($acl, Zend_Auth::getInstance()));
        $fc->registerPlugin(new Application_Plugin_Help());
        $fc->registerPlugin(new Application_Plugin_CountAssign());
        $fc->registerPlugin(new Application_Plugin_GenerateTags());
        $fc->registerPlugin(new Application_Plugin_UserInformation());
        $fc->registerPlugin(new Application_Plugin_HttpsRedirect());
        $fc->registerPlugin(new Application_Plugin_XFrameOptionHeader());
    }

    protected function _initMobile()
    {
        /*
        $mobile = new My_Mobile();

        if(APPLICATION_ENV=='development'){

            if($mobile->isMobile() || $mobile->isTablet()){
                if($_COOKIE['Madads_Mobile']==1)
                    Zend_Registry::set('isMobile', true);
                elseif(isset($_COOKIE['Madads_Mobile']) && $_COOKIE['Madads_Mobile']==0)
                    Zend_Registry::set('isMobile', false);
                else
                    Zend_Registry::set('isMobile', true);
            }else{
                if(isset($_COOKIE['Madads_Mobile']) && $_COOKIE['Madads_Mobile']==1)
                    Zend_Registry::set('isMobile', true);
                else
                    Zend_Registry::set('isMobile', false);
            }

        }else{
            Zend_Registry::set('isMobile', false);
        }
         *
         */
        Zend_Registry::set('isMobile', false);
    }
    
    protected function _initControllerHelpers()
    {
    	Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/default/controllers/helper', 'Helper');	
    }
}
