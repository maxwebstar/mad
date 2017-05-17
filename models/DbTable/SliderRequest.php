<?php
/**
 * Description of SizesRequest
 *
 * @author nik
 */
class Application_Model_DbTable_SliderRequest extends Zend_Db_Table_Abstract
{
    protected $_name = 'slider_request';
    protected $_primary = 'id';

    function getRequest($site_id){
        $sql = $this->_db->select()
            ->from($this->_name, ['id', 'created_at']);

        $sql->where('site_id = ?',$site_id);

        return $this->_db->query($sql)->fetchAll();
    }
}