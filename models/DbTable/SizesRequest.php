<?php
/**
 * Description of SizesRequest
 *
 * @author nik
 */
class Application_Model_DbTable_SizesRequest extends Zend_Db_Table_Abstract
{
    protected $_name = 'sizes_request';
    protected $_primary = 'id';

    function getSizeRequest($site_id){
        $sql = $this->_db->select()
            ->from($this->_name, ['size_id', 'network_requested_id', 'status']);

        $sql->where('site_id = ?',$site_id);
        $data = $this->_db->query($sql)->fetchAll();
        $dataArr = [];
        if($data){
            foreach($data as $item){
                $dataArr[$item['size_id']][$item['network_requested_id']] = $item['status'];
            }
        }
        return $dataArr;
    }

}