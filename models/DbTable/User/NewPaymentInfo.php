<?php
class Application_Model_DbTable_User_NewPaymentInfo extends Zend_Db_Table_Abstract
{
    protected $_name = 'user_new_payment_info';
    protected $_primary = 'id';
    protected $_rowClass='Application_Model_DbTable_Row_User_NewPaymentInfo';

    public function getData($PubID,$ready_array = true)
    {
        $data = array(
            'new' => array(),
            'old' => array()
        );
        if(intval($PubID))
        {
            $select_new = $this->_db->select()
            ->from(array('unpi' => 'user_new_payment_info'),
                array('unpi.PubID', 'unpi.name', 'unpi.payType', 'unpi.street1', 'unpi.street2', 'unpi.city', 'unpi.state', 'unpi.zip', 'unpi.country', 'unpi.paymentAmout', 'unpi.ssn', 'unpi.ein', 'unpi.paymentBy', 'unpi.paypalmail', 'unpi.accName', 'unpi.bankName', 'unpi.accType', 'unpi.accNumber','unpi.confirmAccNumber', 'unpi.routNumber', 'unpi.confirmRoutNumber', 'unpi.bankAdress', 'unpi.bankName2', 'unpi.accName2', 'unpi.accNumber2', 'unpi.swift', 'unpi.iban' ,'unpi.bank', 'unpi.created'))
                ->join(array('u' => 'users'),'unpi.PubID = u.id',array('u.email'))
                ->where('unpi.PubID = ?', $PubID)
                ->limit(1);

            $users_model = new Application_Model_DbTable_Users();

            $data = array();
            $data['new'] =  $this->_db->fetchRow($select_new);
            $data['old'] = $users_model->getUserInfo($PubID);
            if($ready_array)
            {
                if($data['new'])
                {
                    $data['new']['payType'] = $this->prepareValues('payType', $data['new']['payType']);
                    $data['new']['paymentBy'] = $this->prepareValues('paymentBy', $data['new']['paymentBy']);
                    $data['new']['bank'] = $this->prepareValues('bank', $data['new']['bank']);
                    $data['new']['paymentAmout'] = $this->prepareValues('paymentAmout', $data['new']['paymentAmout']);
                    $data['new']['accType'] = $this->prepareValues('accType', $data['new']['accType']);
                    $data['new']['country'] = $this->getCountry($data['new']['country']);
                }
                if($data['old'])
                {
                    $data['old']['payType'] = $this->prepareValues('payType', $data['old']['payType']);
                    $data['old']['paymentBy'] = $this->prepareValues('paymentBy', $data['old']['paymentBy']);
                    $data['old']['bank'] = $this->prepareValues('bank', $data['old']['bank']);
                    $data['old']['paymentAmout'] = $this->prepareValues('paymentAmout', $data['old']['paymentAmout']);
                    $data['old']['accType'] = $this->prepareValues('accType', $data['old']['accType']);
                    $data['old']['country'] = $this->getCountry($data['old']['country']);
                }
            }
        }
        return $data;
    }

    public function getNum()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('COUNT(id) AS count'));
        $data = $this->_db->query($sql)->fetchAll();
        return $data[0]['count'];
    }

    public function getNotApprovedPayInfos()
    {
        set_time_limit(0);
        $output = array(
                'sEcho' => intval($_GET['sEcho']),
                'iTotalRecords' => 1,
                'iTotalDisplayRecords' => 1,
                'aaData' => array()
        );
        $select = $this->_db->select()
                         ->from(array('unpi' => 'user_new_payment_info'),
                                array('PubID', 'name', 'payType', 'paymentBy', 'unpi.created'))
                         ->join(array('u' => 'users'),'unpi.PubID = u.id',array('u.id', 'u.email'));
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1')
        {
            $select = $select->limit(intval($_GET['iDisplayLength']));
        }
        $data = $this->_db->query($select)->fetchAll();
        $output['iTotalRecords'] = count($data);
        $output['iTotalDisplayRecords'] = count($data);
        foreach($data as $payInfo)
        {
            $payInfo['payType'] = $this->prepareValues('payType', $payInfo['payType']);
            $payInfo['paymentBy'] = $this->prepareValues('paymentBy', $payInfo['paymentBy']);
            $item = array(
                $payInfo['PubID'],
                $payInfo['email'],
                $payInfo['payType'],
                $payInfo['paymentBy'],
                date('Y-m-d',strtotime($payInfo['created'])),
                $payInfo['PubID'],
                $payInfo['PubID'],
                $payInfo['PubID'],
            );
            $output['aaData'][] = $item;
        }
    	return $output;
    }

    public function prepareValues($type,$value)
    {
        switch($type)
        {
            case 'paymentBy':
                {
                    switch($value)
                    {
                        case 1:
                            return 'Payment By Check';
                        case 2:
                            return'Payment By PayPal';
                        case 3:
                            return 'Payment By ACH';
                        case 4:
                            return 'Payment by Wire Transfer';
                        default:
                            return 'Payment By Check';
                    }
                }
                break;
            case 'payType':
                {
                    switch($value)
                    {
                        case 1:
                            return 'Individual';
                        case 2:
                            return 'Business';
                        default:
                            return '';
                    }
                }
            case 'accType':
                {
                    switch($value)
                    {
                        case 1:
                            return 'Checking';
                        case 2:
                            return 'Savings';
                        default:
                            return '';
                    }
                }
            case 'bank':
                {
                    switch($value)
                    {
                        case 1:
                            return '';
                        case 2:
                            return 'US Bank';
                        default:
                            return '';
                    }
                }
            case 'paymentAmout':
                {
                    switch($value)
                    {
                        case 1:
                            return 'Not selected';
                        case 2:
                            return '50$';
                        case 3:
                            return '100$';
                        case 4:
                            return '250$';
                        case 5:
                            return '500$';
                        case 6:
                            return '1000$';
                        default:
                            return '';
                    }
                }
                break;
            default:
                return '';
        }
    }

    public function getCountry($countryId)
    {
        $country = new Application_Model_DbTable_Country();
        return $country->getCountry($countryId);
    }

    public function getHistory($PubID)
    {
        $history = new Application_Model_DbTable_User_NewPaymentInfoChanges();
        return $history->getData($PubID);
    }

}
?>
