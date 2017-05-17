<?php
class Application_Model_DbTable_UsersReport extends Zend_Db_Table_Abstract
{
    protected $_name = 'users_report';
    protected $_primary = 'site_id';

    public function getFirstDate($PubID, $SiteID=null)
    {
        $sql = $this->_db->select()
            ->from($this->_name, array('query_date'))
            ->where('pub_id = ?', $PubID)
            ->order('query_date ASC')
            ->limit(1);

        if($SiteID) $sql->where('site_id = ?', $SiteID);
        $data = $this->_db->query($sql)->fetch();

        return $data['query_date'] ? date("M j, Y", strtotime($data['query_date'])) : date("M j, Y");
    }

    public function getReport($pub_id, $date_from, $date_to, $site_id=null, $ad_size=null)
    {
        $sql = $this->_db->select()
            ->from($this->_name, array(
                'size_id',
                'query_date',
                'impressions'=>'SUM(impressions)',
                'paid_impressions'=>'SUM(paid_impressions)',
                'revenue_paid'=>'SUM(revenue_paid)'
            ))
            ->where('pub_id = ?', $pub_id)
            ->where('query_date >= ?', $date_from)
            ->where('query_date <= ?', $date_to)
            ->group('query_date')
            ->order('query_date DESC');

        if($site_id){
            $sql->where('site_id = ?', $site_id);
        }

        if($ad_size){
            $sql->where('size_id = ?', $ad_size);
        }

        $data = $this->_db->query($sql)->fetchAll();
        $dataArr = [];
        if($data){
            foreach($data as $item){
                $dataArr[$item['query_date']] = $item;
            }
        }

        return $dataArr;
    }

}