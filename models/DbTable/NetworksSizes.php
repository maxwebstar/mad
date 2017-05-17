<?php
class Application_Model_DbTable_NetworksSizes extends Zend_Db_Table_Abstract
{
    protected $_name = 'networks_sizes';
    protected $_primary = 'id';

    public function getNetworkSizes($id)
    {
        $sql = $this->_db->select()
            ->from($this->_name, array(
                'size_id',
                'request',
                'primary'
            ))
            ->where("network_id = ?", $id);

        return $this->_db->query($sql)->fetchAll();
    }

    function getSizesForRequest($primaryNetworkID, array $otherNetworksID){
        $sql = $this->_db->select()
            ->from($this->_name .' AS ns' , array(
                'size_id'=>'ns.size_id',
                'network_id'=>'ns.network_id'
            ));

        $sql->join('display_size AS ds', 'ds.id=ns.size_id', array(
            'size_id'=>'ds.id',
            'name'=>'ds.name',
            'width'=>'ds.width',
            'height'=>'ds.height',
            'file_name'=>'ds.file_name',
            'description'=>'ds.description'
        ));

        /*
        if(count($otherNetworksID)>0)
            $sql->where('(ns.request=1) AND ( (ns.network_id='.$primaryNetworkID.' AND (ns.primary=1 OR ns.primary=0) ) OR (ns.network_id IN (?) AND ns.primary=0) )', $otherNetworksID);
        else
            $sql->where('(ns.request=1) AND  (ns.network_id='.$primaryNetworkID.' AND (ns.primary=1 OR ns.primary=0))');
        */

        $sql->where('(ns.request=1) AND ( (ns.network_id = ? AND (ns.primary=1 OR ns.primary=0) ) OR (ns.network_id <> ? AND ns.primary=0) )', $primaryNetworkID);

        return $this->_db->query($sql)->fetchAll();
    }

}