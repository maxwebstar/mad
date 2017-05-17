<?php
class Application_Model_DbTable_NonfilleReport extends Zend_Db_Table_Abstract
{
    protected $_name = 'nonfille_report';

    public function getReport($pub_id, $date_from, $date_to, $site_id=null, $ad_size=null)
    {
        $sql = $this->_db->select()
            ->from($this->_name, array(
                'size_id',
                'query_date',
                'impressions'=>'SUM(impressions)'
            ))
            ->where('user_id = ?', $pub_id)
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