<?php
class Application_Model_DbTable_SitesTags extends Zend_Db_Table_Abstract
{
    protected $_name = 'sites_tags';
    protected $_primary = 'id';

    function getSiteTags($site_id, $primary=1){
        $sql = $this->_db->select()
            ->from($this->_name .' AS st' , array(
                'id'=>'st.id',
                'network_id'=>'st.network_id',
                'primary'=>'st.primary'
            ));

        $sql->join('display_size AS ds', 'ds.id=st.size_id', array(
            'size_id'=>'ds.id',
            'name'=>'ds.name',
            'width'=>'ds.width',
            'height'=>'ds.height',
            'file_name'=>'ds.file_name',
            'description'=>'ds.description'
        ));

        $sql->where('st.site_id=?', $site_id);

        switch($primary){
            case 1:
                $sql->where('st.primary=1');
                break;
            case 0:
                $sql->where('st.primary=0');
                break;
        }

        $sql->group('ds.id');

        return $this->_db->query($sql)->fetchAll();
    }

    function changeAction($site_id, $action, $env='production'){
        $this->update([
            'action'=>$action,
            'env'=>$env
        ],$this->getAdapter()->quoteInto('site_id = ?', $site_id));
    }

    function getSizesForTag($site_id, $primary=1, $group=null){
        $sql = $this->_db->select()
            ->from(['st'=>$this->_name],[]);

        $sql->join(['ds'=>'display_size'], 'ds.id=st.size_id', [
            'id'=>'ds.id',
            'size'=>'CONCAT(ds.width,"x",ds.height)',
            'size_name'=>'ds.name'
        ]);

        $sql->where('st.site_id=?', $site_id);

        switch($primary){
            case 1:
                $sql->where('st.primary=1');
                break;
            case 0:
                $sql->where('st.primary=0');
                break;
        }

        if($group)
            $sql->group($group);

        return $this->_db->query($sql)->fetchAll();
    }

    function getSiteNetworks($site_id){
        $sql = $this->_db->select()
            ->from($this->_name, [
                'network_id',
                'primary'
            ]);

        $sql->where('site_id=?', $site_id);
        $sql->group('network_id');

        return $this->_db->query($sql)->fetchAll();
    }
}