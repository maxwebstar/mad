<?php


class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {      
        $layout = Zend_Layout::getMvcInstance();
        
        if(Zend_Registry::get('isMobile')===true){
            $layout->setLayoutPath(APPLICATION_PATH.'/layouts/scripts/mobile');
            $layout->setLayout('index-mobile');
        }else{
            $layout->setLayout('index');
        }
        
        $layout->nav = 'home';                
        
        $this->view->headTitle('MadAdsMedia.com - Ad Optimization for Small to Mid-Sized Websites');
        $this->view->headMeta ('Make the most of your impressions. Our optimization technology has websites seeing a 350% increase in revenue!', 'description');
        $this->view->headMeta ('ad optimization, ad optimization for small websites, ad optimization for mid-sized websites, ad optimizer', 'keywords');        
    }
    
    public function aboutAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'about';                        
    }
    
    public function cpmAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->nav = 'cpm';                                

        $this->view->headTitle('MadAdsMedia.com CPM/Revenue Calculator - How Much Can I Make?');
        $this->view->headMeta('See how much you can make using MadAds Media to monetize your website.', 'description');
        $this->view->headMeta('cpm, top cpm, cpm calculator, revenue calculator, ads', 'keywords');        
    }
    
    public function getcpmAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
                        
        $this->getResponse()->setHeader('Content-Type', 'application/json');    

        if($this->getRequest()->isPost()){

            $IMP = str_replace(",","",$this->getRequest()->getPost('IMP'));
            
            if($IMP < 10000) { 
            $PERC = 1;
            }elseif(($IMP >= 10000) && ($IMP < 50000)) { 
            $PERC = .95;
            }elseif(($IMP >= 50000) && ($IMP < 100000)) { 
            $PERC = .90;
            }elseif(($IMP >= 100000) && ($IMP < 200000)) { 
            $PERC = .85;
            }elseif(($IMP >= 200000) && ($IMP < 300000)) { 
            $PERC = .80;
            }elseif(($IMP >= 300000) && ($IMP < 400000)) { 
            $PERC = .75;
            }elseif(($IMP >= 400000) && ($IMP < 500000)) { 
            $PERC = .70;
            }elseif(($IMP >= 500000) && ($IMP < 1000000)) { 
            $PERC = .65;
            }elseif(($IMP >= 1000000) && ($IMP < 5000000)) { 
            $PERC = .60;
            }elseif(($IMP >= 5000000) && ($IMP < 10000000)) { 
            $PERC = .55;
            }elseif(($IMP >= 10000000) && ($IMP < 20000000)) { 
            $PERC = .50;
            }elseif($IMP >= 20000000) { 
            $PERC = .50;
            }else{
            //$PERC = 1.99;
            }
            
            $USA = str_replace('%','',$this->getRequest()->getPost('USA'))/100;
            //echo "USA IMP: ".$USA."<br>";
            $USACPM = "1.15" * $PERC;
            //echo "USA CPM: ".$USACPM."<br>";
            $UK = str_replace('%','',$this->getRequest()->getPost('UK'))/100;
            //echo "UK IMP: ".$UK."<br>";
            $UKCPM = ".85" * $PERC;
            //echo "UK CPM: ".$UKCPM."<br>";
            $CA = str_replace('%','',$this->getRequest()->getPost('CA'))/100;
            //echo "CA IMP: ".$CA."<br>";
            $CACPM = "1.00" * $PERC;
            //echo "CA CPM: ".$CACPM."<br>";
            $AU = str_replace('%','',$this->getRequest()->getPost('AU'))/100;
            //echo "AU IMP: ".$AU."<br>";
            $AUCPM = "1.00" * $PERC;
            //echo "AU CPM: ".$AUCPM."<br>";
            $INT = str_replace('%','',$this->getRequest()->getPost('INT'))/100;
            //echo "INT IMP: ".$INT."<br>";
            $INTCPM = ".60" * $PERC;
            //echo "INT CPM: ".$INTCPM."<br>";
            
            $USAREV = ($IMP/1000)*$USA*$USACPM;
            //echo "USA REV:".$USAREV."<br>";
            $UKREV = ($IMP/1000)*$UK*$UKCPM;
            //echo "UK REV:".$UKREV."<br>";
            $CAREV = ($IMP/1000)*$CA*$CACPM;
            //echo "CA REV:".$CAREV."<br>";
            $AUREV = ($IMP/1000)*$AU*$AUCPM;
            //echo "AU REV:".$AUREV."<br>";
            $INTREV = ($IMP/1000)*$INT*$INTCPM;
            //echo "INT REV:".$INTREV."<br>";

            
            $data = array(
                            'imp'=>number_format($IMP),
                            'cpm'=>'$'.number_format(($USACPM+$UKCPM+$CACPM+$AUCPM+$INTCPM)/5,2),
                            'daily'=>'$'.number_format($USAREV + $UKREV + $CAREV + $AUREV + $INTREV),
                            'monthly'=>'$'.number_format(($USAREV + $UKREV + $CAREV + $AUREV + $INTREV)*31)
                        );
            
        }else{
            $data = array();
        } 
        
            $json = Zend_Json::encode($data);
            echo $json;            
               
    }
    
    public function faqAction()
    {
    	 
    }
    
    public function disableNotificationAction()
    {
        
        $code = $this->getRequest()->getParam('code');
        $confirm = $this->getRequest()->getParam('confirm');
        
        if($code && $confirm){
        
            $tableUser = new Application_Model_DbTable_Users();

            $sql = $tableUser->select()
                             ->where('password = ?', $code);

            $dataUser = $tableUser->fetchRow($sql);
            
            if($dataUser){
            
                $dataUser->notification_control_user = NULL;
                $dataUser->save();
                     
                   $this->view->message = 'You have been successfully unsubscribed';
            } else $this->view->message = 'User from such data can not be found. Please try again';   
              
            
        } elseif($code){
            
            $this->view->message = 'Are you sure you want to unsubscribe from all MadAds Media email?  You can re-enable this later in the "Contact Info" section of your account.<br /><br /><br /><a href="/disable-notification/'.$code.'/1">Yes, unsubscribe me.</a>';
            
        }
        
    }
    
    public function checkMissingTagAction()
    {
        $tableTag = new Application_Model_DbTable_Tags();
        $tableSize = new Application_Model_DbTable_Sizes();
        $tableMissing = new Application_Model_DbTable_Tag_Missing();
        
        $sqlSize = $tableSize->select()->where('file_name != ?', 'pop')->order('position ASC');
        
        $dataSize = $tableSize->fetchAll($sqlSize);                
        $dataTag = $tableTag->getInfoForUpdate(); 

        $date = date('Y-m-d');

        foreach($dataTag as $iter){ 

            foreach($dataSize as $jter){ 
          
                $file = $_SERVER['DOCUMENT_ROOT']."/tags/".$iter['PubID']."/".$iter['SiteID']."/async/".$jter['file_name'].".js";
                
                if(is_file($file) == false){ 
                                        
                    $tableMissing->insert(array('PubID' => $iter['PubID'], 'SiteID' => $iter['SiteID'], 'created' => $date));
                    
                }        
            }
            
        }
        
        exit();
    }
    
    public function mobileAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        setcookie ("Madads_Mobile", 1, 0, '/');
        $this->_redirect('/');
    }
    
    public function fullAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        setcookie ("Madads_Mobile", 0, 0, '/');
        $this->_redirect('/');
    }    
    
    public function termsAction()
    {
        $this->view->headTitle('MadAdsMedia.com CPM/Revenue Calculator - How Much Can I Make?');
        $this->view->headMeta('See how much you can make using MadAds Media to monetize your website.', 'description');
        $this->view->headMeta('cpm, top cpm, cpm calculator, revenue calculator, ads', 'keywords');                
    }
    
    public function careersAction()
    {
        
    }    
}