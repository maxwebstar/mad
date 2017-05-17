<?php
class ReferralController extends Zend_Controller_Action
{
    protected $_session;
    protected $_userAuth;
    
    public function init()
    {              
           $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()) { $this->_userAuth = $auth->getIdentity(); }
    }
    
    public function indexAction()
    {
           $referral_id = (int)$this->_getParam('ref');
           $user_id = isset($this->_userAuth->id) ? $this->_userAuth->id : 0;
           
           $tableReferral = new Application_Model_DbTable_Referral();
                  
           $sql = $tableReferral->select()
                                ->where('id = ?', $referral_id);

           $dataReferral = $tableReferral->fetchRow($sql);
                      
           if(empty($_COOKIE['referral_id']) && $dataReferral && $user_id == false){
           
                $tableReferralStat = new Application_Model_DbTable_ReferralStat();
                $sql = $tableReferralStat->select()->where('refID = ?', $referral_id)->where('query_date = ?', date("Y-m-d"));
                $dataReferralStat = $tableReferralStat->fetchRow($sql);           
               
              $dataReferralStat->num_click += 1;
              //$dataReferral->save();
              $sql = "INSERT INTO referral_stat (refID, num_click, query_date) VALUES ('".$referral_id."', '".$dataReferralStat->num_click."', '".date("Y-m-d")."') ON DUPLICATE KEY UPDATE refID='".$referral_id."', num_click = '".$dataReferralStat->num_click."', query_date='".date("Y-m-d")."'";
              $tableReferralStat->getDefaultAdapter()->query($sql);

              setcookie('referral_id', $referral_id, time() + 86400, '/');
                          
           }
           
           $this->_redirect('/registration');
    }
    
}
?>
