<?php
class Application_Model_DbTable_Revshares extends Zend_Db_Table_Abstract
{
    protected $_name = 'users_revshares_size';
    protected $_primary = 'id';

    function getSaveSizes($site_id){
        $sql = $this->_db->select()
            ->from(['ur'=>$this->_name],[]);

        $sql->join(['ds'=>'display_size'], 'ds.id=ur.size_id', [
            'id'=>'ds.id',
            'size'=>'CONCAT(ds.width,"x",ds.height)',
            'size_name'=>'ds.name'
        ]);

        $sql->where('ur.site_id=?', $site_id);

        return $this->_db->query($sql)->fetchAll();
    }

    function getRevsharesValues($site_id){
        $sql = $this->_db->select()
            ->from(['ur'=>$this->_name],['size_id', 'RevShare']);

        $sql->where('ur.site_id=?', $site_id);

        $data = $this->_db->query($sql)->fetchAll();

        $revshares = [];

        if($data){
            foreach ($data as $val){
                $revshares[$val['size_id']] = $val['RevShare'];
            }
        }

        return $revshares;
    }

}